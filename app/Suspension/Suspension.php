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

    public function suspendServer(Server $server, $created_at)
    {
        $this->access->suspend($server->access);
        event(new Events\ServerSuspend($server, $created_at));
    }

    public function suspendWarning(Server $server, $created_at)
    {
        event(new Events\ServerSuspendWarning($server, $created_at, $this->maxReportDate()));
    }

    public function maxReportDate()
    {
        $settings = app('Settings');
        return Carbon::now()->subDays($settings->pkg_abuse_auto_suspension);
    }
}