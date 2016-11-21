<?php

namespace Packages\Abuse\App\Report\Listeners;

use Packages\Abuse\App\Report\Events\ReportClientReassigned;
use Packages\Abuse\App\Report\Report;
use Packages\Abuse\App\Report\ReportTransformer;
use App\Auth\Sso;
use App\Mail;

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
     * @param Mail\Mailer       $mail
     * @param Sso\SsoUrlService $sso
     * @param ReportTransformer $transform
     */
    public function __construct(
        Mail\Mailer $mail,
        Sso\SsoUrlService $sso,
        ReportTransformer $transform
    ) {
        parent::__construct($mail);

        $this->sso = $sso;
        $this->transform = $transform;
    }

    /**
     * Handle the event.
     *
     * @param  ReportClientReassigned  $event
     *
     * @return void
     */
    public function handle(ReportClientReassigned $event)
    {
        // Make sure there is a client to send the report to.
        if (!$client = $event->report->client) {
            return;
        }

        $context = [
            'client' => $client->expose('name'),
            'server' => $this->server($event->report),
            'report' => $this->report($event->report),
            'urls' => [
                'view' => $this->sso->view($event->report, $client),
            ],
        ];

        $this
            ->create('abuse_report.tpl')
            ->setData($context)
            ->toUser($client)
            ->send()
            ;
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
            'date' => $this->transform->dateForViewer($report->created_at),
        ];
    }
}
