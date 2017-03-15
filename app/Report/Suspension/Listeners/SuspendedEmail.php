<?php

namespace Packages\Abuse\App\Report\Suspension\Listeners;

use Packages\Abuse\App\Report\Suspension\Events;
use Packages\Abuse\App\Report\Report;
use App\Mail;

/**
 * Send out an Email when Server suspended.
 */
class SuspendedEmail extends Mail\EmailListener
{
    /**
     * Handle the event.
     *
     * @param Events\ServerSuspend $event
     */
    public function handle(Events\ServerSuspend $event)
    {
        $report = $event->report;
        $this->send($report);
    }

    /**
     * @param Report $report
     */
    protected function send(Report $report)
    {
        dd($report->server);
        $client = $report->server->access->client;
        $context = [
            'client' => $client->expose('name'),
            'server' => $report->server->expose('name'),
            'report' => [
                'id' => $report->id,
                'date' => $report->created_at->toDateString()
            ]
        ];

        $this
            ->create('abuse_report_suspended.tpl')
            ->setData($context)
            ->toUser($client)
            ->send()
        ;
    }
}
