<?php

namespace Packages\Abuse\App\Report\Suspension\Listeners;

use Packages\Abuse\App\Report\Suspension\Events;
use App\Server\Server;
use App\Mail;

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
        $server = $event->server;
        $maxReportDate = $event->maxReportDate;
        $createdDate = $event->createdDate;
        $this->send($server, $createdDate, $maxReportDate);
    }

    /**
     * @param Server $server, Report created_at, pkg_abuse_auto_suspension
     */
    protected function send(Server $server, $createdDate, $maxReportDate)
    {
        $days = $createdDate->diffInDays($maxReportDate);

        $client = $server->access->client;
        $context = [
            'client' => $client->expose('name'),
            'server' => $server->expose('name'),
            'report' => [
                'date' => $createdDate->toDateString()
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
