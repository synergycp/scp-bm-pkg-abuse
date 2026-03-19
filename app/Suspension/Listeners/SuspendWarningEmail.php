<?php

namespace Packages\Abuse\App\Suspension\Listeners;

use App\Client\Server\ClientServerAccessService;
use App\Mail;
use App\Server\Server;
use App\Url\UrlService;
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

    /**
     * @var ClientServerAccessService
     */
    private $access;

    /**
     * @var UrlService
     */
    private $url;

    /**
     * SuspendWarningEmail constructor.
     *
     * @param Suspension                $suspension
     * @param ClientServerAccessService $access
     * @param UrlService                $url
     */
    public function boot(
        Suspension $suspension,
        ClientServerAccessService $access,
        UrlService $url
    ) {
        $this->access = $access;
        $this->suspension = $suspension;
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
        $date = $this->suspension->maxReportDate();
        $days = round(abs($createdDate->diffInDays($date)) + 1, 2);
        $context = [
            'server' => $server->expose('srv_id', 'name'),
            'report' => [
                'date' => $createdDate->toDateString(),
            ],
            'days' => $days,
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
