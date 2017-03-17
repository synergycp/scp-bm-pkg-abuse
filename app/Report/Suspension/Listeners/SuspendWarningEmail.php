<?php

namespace Packages\Abuse\App\Report\Suspension\Listeners;

use Packages\Abuse\App\Report\Suspension\Events;
use App\Server\Server;
use App\Mail;
use Carbon\Carbon;

/**
 * Send out an Email when Server suspended.
 */
class SuspendWarningEmail
    extends Mail\EmailListener
{
    /**
     * Handle the event.
     *
     * @param Events\ServerSuspend $event
     */
    public function handle(Events\ServerSuspendWarning $event)
    {
        $server = $event->server;
        $createdDate = $event->createdDate;
        $this->send($server, $createdDate);
    }

    /**
     * @param Server $server, Report created_at, pkg_abuse_auto_suspension
     */
    protected function send(Server $server, Carbon $createdDate)
    {
        $settings = app('Settings');
        $date = Carbon::now()->subDays($settings->pkg_abuse_auto_suspension);
        $days = $createdDate->diffInDays($date);

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
