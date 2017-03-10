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
     * @param Mail\Mailer $mail
     */
    public function __construct(
        Mail\Mailer $mail
    ) {
        parent::__construct($mail);

    }

    /**
     * Handle the event.
     *
     * @param Events\ServerSuspend $event
     */
    public function handle(Events\ServerSuspendWarning $event)
    {
        $report = $event->report;
        $this->send($report);
    }

    /**
     * @param Report $report
     */
    protected function send(Report $report)
    {
        $settings = app('Settings');
        $autoSuspension = $settings->auto_suspension;
        $suspensionLastDate = Carbon::now()->subDays($autoSuspension);

        $days = $report->created_at->diffInDays($suspensionLastDate);

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
