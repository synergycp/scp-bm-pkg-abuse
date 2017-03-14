<?php

namespace Packages\Abuse\App\Suspension;

use Packages\Abuse\App\Report;
use Carbon\Carbon;

class ReportList
{
    private $suspension;

    public function __construct(Suspension $suspension)
    {
        $this->suspension = $suspension;
    }

    /**
     *
     */
    public function get()
    {
        $settings = app('Settings');
        $autoSuspension = $settings->pkg_abuse_auto_suspension;
        $suspensionLastDate = Carbon::now()->subDays($autoSuspension)->toDateString();

        $olderAbuseReport = function($reports) {
            return collect($reports)->where('created_at', collect($reports)->min('created_at'))->first();
        };

        $vipClientFilter = function(Report\Report $report) {
            if ($report->server) {
                if ($report->server->access) {
                    return $report->server->access->client->billing_ignore_auto_suspend ? false : true;
                }
                return false;
            }
            return false;
        };

        $suspension = function(Report\Report $report) use ($suspensionLastDate) {
            if ($report->created_at->toDateString() <= $suspensionLastDate) {
                // suspend & send suspended message
                $this->suspension->suspendServer($report);
            } else {
                // send suspend warning message
                $this->suspension->suspendWarning($report);
            }
        };

        Report\Report::with('server')
            ->pendingClient()
            ->get()
            ->groupBy('server_id')
            ->map($olderAbuseReport)
            ->filter($vipClientFilter)
            ->each($suspension)
        ;
    }
}
