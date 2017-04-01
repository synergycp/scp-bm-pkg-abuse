<?php

namespace Packages\Abuse\App\Report\Listeners;

use App\Auth\Sso;
use App\Client;
use App\Mail;
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
     * @param Sso\SsoUrlService                       $sso
     * @param Client\Server\ClientServerAccessService $access
     */
    public function boot(
        Sso\SsoUrlService $sso,
        Client\Server\ClientServerAccessService $access
    ) {
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

        if ($report->resolved_at) {
            return;
        }

        foreach ($this->clients($report) as $client) {
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
}
