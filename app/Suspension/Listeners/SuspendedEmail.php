<?php

namespace Packages\Abuse\App\Suspension\Listeners;

use Packages\Abuse\App\Suspension\Events;
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
     * @var string
     */
    private $template = 'pkg/abuse/abuse_report_suspended.tpl';

    /**
     * @var ClientServerAccessService
     */
    private $access;

    /**
     * SuspendedEmail constructor.
     *
     * @param ClientServerAccessService $access
     */
    public function boot(
        ClientServerAccessService $access
    ) {
        $this->access = $access;
    }

    /**
     * Handle the event.
     *
     * @param Events\SuspensionEvent $event
     */
    public function handle(Events\SuspensionEvent $event)
    {
        $this->send(
            $event->server,
            $event->createdDate
        );
    }

    /**
     * @param Server $server
     * @param Carbon $createdDate
     */
    protected function send(Server $server, Carbon $createdDate)
    {
        $context = [
            'server' => $server->expose('name'),
            'report' => [
                'date' => $createdDate->toDateString(),
            ],
        ];

        foreach ($this->access->clients($server) as $client) {
            $context['client'] = $client->expose('name');

            $this
                ->create($this->template)
                ->setData($context)
                ->toUser($client)
                ->send()
            ;
        }
    }
}
