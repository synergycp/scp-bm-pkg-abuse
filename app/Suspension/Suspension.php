<?php

namespace Packages\Abuse\App\Suspension;

use Packages\Abuse\App\Suspension\Events;
use App\Client\Server\ClientServerAccessService;
use Carbon\Carbon;
use App\Server\Server;
use Illuminate\Events\Dispatcher;

class Suspension
{
    /**
     * @var ClientServerAccessService
     */
    private $access;
    
    /**
     * @var Dispatcher
     */
    private $event;

    public function __construct(ClientServerAccessService $access, Dispatcher $event)
    {
        $this->access = $access;
        $this->event = $event;
    }

    public function suspendServer(Server $server, Carbon $createdAt)
    {
        $this->access->suspend($server->access);
        $this->event->fire(
            new Events\ServerSuspend($server, $createdAt)
        );
    }

    public function suspendWarning(Server $server, Carbon $createdAt)
    {
        $this->event->fire(
            new Events\ServerSuspendWarning($server, $createdAt)
        );
    }

    public function maxReportDate()
    {
        $settings = app('Settings');
        $now = Carbon::now();
        
        return isset($settings->pkg_abuse_auto_suspension)
             ? $now->subDays($settings->pkg_abuse_auto_suspension)
             : $now->subMonth()
             ;
    }
}
