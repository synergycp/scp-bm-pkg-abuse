<?php

namespace Packages\Abuse\App\Report\Listeners;

use App\Auth\Sso;
use App\Client;
use App\Mail;
use Illuminate\Database\Eloquent\Builder;
use Packages\Abuse\App\Report\Events\ReportClientReassigned;
use Packages\Abuse\App\Report\Report;

class ReportClientEmail
    extends Mail\EmailListener
{
    /**
     * @var Sso\SsoUrlService
     */
    protected $sso;

    /**
     * @var Client\Server\ClientServerAccessService
     */
    private $access;

    /**
     * ReportClientEmail constructor.
     *
     * @param Mail\Mailer                             $mail
     * @param Sso\SsoUrlService                       $sso
     * @param Client\Server\ClientServerAccessService $access
     */
    public function __construct(
        Mail\Mailer $mail,
        Sso\SsoUrlService $sso,
        Client\Server\ClientServerAccessService $access
    ) {
        parent::__construct($mail);

        $this->sso = $sso;
        $this->access = $access;
    }

    /**
     * Handle the event.
     *
     * @param  ReportClientReassigned $event
     *
     * @return void
     */
    public function handle(ReportClientReassigned $event)
    {
        $report = $event->report;

        if (!$report->resolved_at) {
            $sendEmail = function (Client\Client $client) use ($report) {
                // TODO: way for Client to opt out of these emails.
                $context = [
                    'client' => $client->expose('name'),
                    'server' => $this->server($report),
                    'report' => $this->report($report),
                    'urls' => [
                        'view' => $this->sso->view($report, $client),
                    ],
                ];

                $this
                    ->create('abuse_report.tpl')
                    ->setData($context)
                    ->toUser($client)
                    ->send()
                ;
            };

            $this
                ->clients($report)
                ->each($sendEmail)
            ;
        }
    }

    /**
     * @param  Report $report
     *
     * @return array|null
     */
    private function server(Report $report)
    {
        if (!$server = $report->server) {
            return null;
        }

        return $server->expose('id', 'nickname', 'name');
    }

    /**
     * @param  Report $report
     *
     * @return array
     */
    private function report(Report $report)
    {
        return $report->expose('id') + [
                'date' => (string)$report->created_at,
            ];
    }

    /**
     * Get Clients that have access to the Report.
     *
     * @param Report $report
     *
     * @return Client\Client[]
     */
    public function clients(Report $report)
    {
        if (!$server = $report->server) {
            return [$report->client];
        }

        return $this->access->clients($report->server);
    }

    /**
     * @param string $table
     * @param Report $report
     *
     * @return \Closure
     */
    protected function matchingAccess($table, Report $report)
    {
        return function (Builder $query) use ($table, $report) {
            $query->where("$table.server_id", $report->server_id);

            if ($report->resolved_at) {
                $query->where("$table.created_at", '<', $report->resolved_at);
            }
        };
    }
}
