<?php

namespace Packages\Abuse\App\Suspension\Listeners;

use App\Client\Server\ClientServerAccessService;
use App\Mail;
use App\Server\Server;
use App\Url\UrlService;
use Carbon\Carbon;
use Packages\Abuse\App\Suspension\Events;

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
     * @var UrlService
     */
    private $url;

    /**
     * SuspendedEmail constructor.
     *
     * @param ClientServerAccessService $access
     * @param UrlService                $url
     */
    public function boot(
        ClientServerAccessService $access,
        UrlService $url
    ) {
        $this->access = $access;
        $this->url = $url;
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
            'server' => $server->expose('srv_id', 'name'),
            'report' => [
                'date' => $createdDate->toDateString(),
            ],
        ];

        foreach ($this->access->clients($server) as $client) {
            $context['client'] = $client->expose('name');
            $context['urls'] = [
                'view' => $this->url->base(get_class($client)) . '/pkg/abuse/report?tab=0',
            ];

            $this
                ->create($this->template)
                ->setData($context)
                ->toUser($client)
                ->send()
            ;
        }
    }
}
