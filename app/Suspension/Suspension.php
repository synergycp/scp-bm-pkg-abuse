<?php

namespace Packages\Abuse\App\Suspension;

use Packages\Abuse\App\Report;
use App\Client\Server\ClientServerAccessService;
use Carbon\Carbon;

class Suspension {

    private $access;

    public function __construct(ClientServerAccessService $access)
    {
        $this->access = $access;
    }

    public function suspendServer(Report\Report $report)
    {
        $this->access->suspend($report->server->access);
        event(new Report\Suspension\Events\ServerSuspend($report));
    }

    public function suspendWarning(Report\Report $report)
    {
        event(new Report\Suspension\Events\ServerSuspendWarning($report, $this->maxReportDate()));
    }

    public function maxReportDate()
    {
        $settings = app('Settings');
        return Carbon::now()->subDays($settings->pkg_abuse_auto_suspension);
    }
}