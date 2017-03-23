<?php

namespace Packages\Abuse\App\Suspension;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Server\ServerRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Packages\Abuse\App\Report\ReportRepository;

use Illuminate\Support\Facades\Log;

class SuspensionSync
{
    private $suspension;

    private $reportRepository;

    public function __construct(Suspension $suspension, ServerRepository $server, ReportRepository $reportRepository)
    {
        $this->suspension = $suspension;
        $this->server = $server;
        $this->reportRepository = $reportRepository;
    }

    /**
     *
     */
    public function sync()
    {
        $suspensionLastDate = $this->suspension->maxReportDate();
        $reports = $this->reportRepository->whereNotNull('server_id')
            ->select('server_id', DB::raw('min(created_at) as created_at'))
            ->pendingClient()
            ->groupBy('server_id')
            ->get()
        ;
        $serverIds = $reports
            ->pluck('server_id')
            ->all()
            ;
        $servers = $this
            ->server
            ->find($serverIds)
            ->load('access.client')
            ->keyBy('id')
            ;
        $vipClientFilter = function($report) use ($servers) {

            if (!isset($servers[$report->server_id])) {
                return false;
            }

            $server = $servers[$report->server_id];

            if (!$access = $server->access) {
                return false;
            }

            if ($access->is_suspended) {
                return false;
            }

            return !$access->client->billing_ignore_auto_suspend;

        };
        $suspension = function($report) use ($suspensionLastDate, $servers) {

            $server = $servers[$report->server_id];

            if ($suspensionLastDate->gt($report->created_at)) {
                // suspend & send suspended message
                $this->suspension->suspendServer($server, $report->created_at);
                return;
            }
            // send suspend warning message
            $this->suspension->suspendWarning($server, $report->created_at);
        };

        $reports
            ->filter($vipClientFilter)
            ->each($suspension)
        ;
    }
}