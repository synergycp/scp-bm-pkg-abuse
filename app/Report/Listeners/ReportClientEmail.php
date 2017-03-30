<?php

namespace Packages\Abuse\App\Report\Listeners;

use App\Auth\Sso;
use App\Client;
use App\Mail;
use Illuminate\Database\Eloquent\Builder;
use Packages\Abuse\App\Report\Events\ReportClientReassigned;
use Packages\Abuse\App\Report\Report;
use Packages\Abuse\App\Report\ReportTransformer;

class ReportClientEmail
    extends Mail\EmailListener
{
    /**
     * @var ReportTransformer
     */
    protected $transform;

    /**
     * @var Sso\SsoUrlService
     */
    protected $sso;

    /**
     * @var Client\ClientRepository
     */
    protected $clients;

    /**
     * ReportClientEmail constructor.
     *
     * @param Mail\Mailer             $mail
     * @param Sso\SsoUrlService       $sso
     * @param ReportTransformer       $transform
     * @param Client\ClientRepository $clients
     */
    public function __construct(
        Mail\Mailer $mail,
        Sso\SsoUrlService $sso,
        ReportTransformer $transform,
        Client\ClientRepository $clients
    ) {
        parent::__construct($mail);

        $this->sso = $sso;
        $this->clients = $clients;
        $this->transform = $transform;
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

            $this->clients($report)
                ->each($sendEmail)
            ;
        }

    }

    /**
     * Query for Clients that have access to the Report.
     *
     * @param Report $report
     *
     * @return Builder
     */
    public function clients(Report $report)
    {
        return $this->clients
            ->query()
            ->select('clients.*')
            ->groupBy('clients.id')
            ->leftJoin('client_supers as super', 'super.grantee_id', '=', 'clients.id')
            ->leftJoin('client_server as access', 'access.client_id', '=', 'clients.id')
            ->leftJoin('client_server as access_super', 'access.client_id', '=', 'super.granter_id')
            ->where(function (Builder $query) use ($report) {
                if (!$report->server_id && !$report->client_id) {
                    return $query->where(\DB::raw('1'), 2);
                }

                if ($report->client_id) {
                    // The user is or has been granted super access to the Client that the Report is assigned to.
                    $query
                        ->orWhere('clients.id', $report->client_id)
                        ->orWhere('super.granter_id', $report->client_id)
                    ;
                }

                if ($report->server_id) {
                    // Or, the user is or is super of one of the Clients that the Server is assigned to.
                    $query
                        ->orWhere($this->matchingAccess('access', $report))
                        ->orWhere($this->matchingAccess('access_super', $report))
                    ;
                }
            })
            ;
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

    /**
     * @param  Report $report
     *
     * @return array|null
     */
    private function server(Report $report)
    {
        $server = $report->server;

        return $server ? $server->expose('id', 'nickname', 'name') : null;
    }

    /**
     * @param  Report $report
     *
     * @return array
     */
    private function report(Report $report)
    {
        return $report->expose('id') + [
            'date' => (string) $report->created_at,
        ];
    }
}
