<?php

namespace Packages\Abuse\App\Suspension\Listeners;

use App\Mail;
use App\Server\Server;
use Carbon\Carbon;
use Packages\Abuse\App\Suspension\Events;
use Packages\Abuse\App\Suspension\Suspension;

/**
 * Send out an Email when Server suspended.
 */
class SuspendWarningEmail
    extends Mail\EmailListener
{
    /**
     * @var string
     */
    private $template = 'pkg/abuse/abuse_report_suspend_warning.tpl';

    /**
     * @var Suspension
     */
    private $suspension;

    public function boot(Suspension $suspension)
    {
        $this->suspension = $suspension;
    }

    /**
     * Handle the event.
     *
     * @param Events\SuspensionEvent $event
     */
    public function handle(Events\SuspensionEvent $event)
    {
        $server = $event->server;
        $createdDate = $event->createdDate;
        $this->send($server, $createdDate);
    }

    /**
     * @param Server $server
     * @param Carbon $createdDate
     */
    protected function send(Server $server, Carbon $createdDate)
    {
        $date = $this->suspension->maxReportDate();
        $days = $createdDate->diffInDays($date);

        $client = $server->access->client;
        $context = [
            'client' => $client->expose('name'),
            'server' => $server->expose('name'),
            'report' => [
                'date' => $createdDate->toDateString(),
            ],
            'days' => $days,
        ];

        $this
            ->create($this->template)
            ->setData($context)
            ->toUser($client)
            ->send()
        ;
    }
}
