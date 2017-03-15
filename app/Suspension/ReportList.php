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
        $suspensionLastDate = $this->suspension->maxReportDate()->toDateString();

        $olderAbuseReport = function($reports) {
            return collect($reports)->where('created_at', collect($reports)->min('created_at'))->first();
        };

        $vipClientFilter = function(Report\Report $report) {
            if (!$server = $report->server) {
                return false;
            }

            if (!$access = $server->access) {
                return false;
            }

            if ($access->is_suspended) {
                return false;
            }

            return !$access->client->billing_ignore_auto_suspend;
        };

        $suspension = function(Report\Report $report) use ($suspensionLastDate) {
            if ($report->created_at->toDateString() <= $suspensionLastDate) {
                // suspend & send suspended message
                $this->suspension->suspendServer($report);
                return;
            }
            // send suspend warning message
            $this->suspension->suspendWarning($report);
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
