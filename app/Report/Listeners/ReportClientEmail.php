<?php

namespace Packages\Abuse\App\Report\Listeners;

use Packages\Abuse\App\Report\Events\ReportClientReassigned;
use Packages\Abuse\App\Report\Report;
use Packages\Abuse\App\Report\ReportTransformer;
use App\Services\Mail\EmailListener;

class ReportClientEmail extends EmailListener
{
    /**
     * @var ReportTransformer
     */
    protected $transform;

    public function boot(
        ReportTransformer $transform
    ) {
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
        ];

        $this->create('abuse_report.tpl')
            ->setData($context)
            ->toUser($client)
            ->send();
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
