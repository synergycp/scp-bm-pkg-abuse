<?php

namespace Packages\Abuse\App\Report\Suspension\Listeners;

use Packages\Abuse\App\Report\Suspension\Events;
use App\Server\Server;
use App\Mail;
use Carbon\Carbon;

/**
 * Send out an Email when Server suspended.
 */
class SuspendedEmail
    extends Mail\EmailListener
{
    /**
     * Handle the event.
     *
     * @param Events\ServerSuspend $event
     */
    public function handle(Events\ServerSuspend $event)
    {
        $server = $event->server;
        $createdDate = $event->createdDate;
        $this->send($server, $createdDate);
    }

    /**
     * @param Report $report
     */
    protected function send(Server $server, Carbon $createdDate)
    {
        $client = $server->access->client;
        $context = [
            'client' => $client->expose('name'),
            'server' => $server->expose('name'),
            'report' => [
                'date' => $createdDate->toDateString()
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
