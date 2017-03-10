<?php

namespace Packages\Abuse\App\Suspension;

use Packages\Abuse\App\Report;
use App\Client\Server\ClientServerAccessService;

class Suspension {

    private $access;

    public function suspendServer(Report\Report $report)
    {
        $this->access = app(ClientServerAccessService::class);
        $this->access->suspend($report->server->access);

        event(new Report\Suspension\Events\ServerSuspend($report));

    }

    public function suspendWarning(Report\Report $report)
    {
        event(new Report\Suspension\Events\ServerSuspendWarning($report));
    }
}