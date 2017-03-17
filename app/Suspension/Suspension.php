<?php

namespace Packages\Abuse\App\Suspension;

use Packages\Abuse\App\Report\Suspension\Events;
use App\Client\Server\ClientServerAccessService;
use Carbon\Carbon;
use App\Server\Server;

class Suspension {

    private $access;

    public function __construct(ClientServerAccessService $access)
    {
        $this->access = $access;
    }

    public function suspendServer(Server $server, Carbon $createdAt)
    {
        $this->access->suspend($server->access);
        event(new Events\ServerSuspend($server, $createdAt));
    }

    public function suspendWarning(Server $server, Carbon $createdAt)
    {
        event(new Events\ServerSuspendWarning($server, $createdAt));
    }

    public function maxReportDate()
    {
        $settings = app('Settings');
        return Carbon::now()->subDays($settings->pkg_abuse_auto_suspension);
    }
}