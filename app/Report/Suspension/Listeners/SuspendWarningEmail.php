<?php

namespace Packages\Abuse\App\Report\Suspension\Listeners;

use Packages\Abuse\App\Report\Suspension\Events;
use Packages\Abuse\App\Report\Report;
use App\Mail;
use Carbon\Carbon;

/**
 * Send out an Email when Server suspended.
 */
class SuspendWarningEmail extends Mail\EmailListener
{
    /**
     * Handle the event.
     *
     * @param Events\ServerSuspend $event
     */
    public function handle(Events\ServerSuspendWarning $event)
    {
        $report = $event->report;
        $maxReportDate = $event->maxReportDate;
        $this->send($report, $maxReportDate);
    }

    /**
     * @param Report $report
     */
    protected function send(Report $report, $maxReportDate)
    {
        $days = $report->created_at->diffInDays($maxReportDate);

        $client = $report->server->access->client;
        $context = [
            'client' => $client->expose('name'),
            'server' => $report->server->expose('name'),
            'report' => [
                'id' => $report->id,
            ],
            'days' => $days
        ];

        $this
            ->create('abuse_report_suspended.tpl')
            ->setData($context)
            ->toUser($client)
            ->send()
        ;
    }
}
