<?php

namespace Packages\Abuse\App\Suspension;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Server\ServerRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Packages\Abuse\App\Report\ReportRepository;

class SuspensionSync
{
    /**
     * @var ServerRepository
     */
    private $server;

    /**
     * @var Suspension
     */
    private $suspension;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * SuspensionSync constructor.
     *
     * @param Suspension       $suspension
     * @param ServerRepository $server
     * @param ReportRepository $reportRepository
     */
    public function __construct(
        Suspension $suspension,
        ServerRepository $server,
        ReportRepository $reportRepository
    ) {
        $this->server = $server;
        $this->suspension = $suspension;
        $this->reportRepository = $reportRepository;
    }

    /**
     *
     */
    public function sync()
    {
        $suspensionLastDate = $this->suspension->maxReportDate();

        if ($suspensionLastDate === null) {
            return;
        }

        $query = $this->reportRepository
            ->query()
            ->whereNotNull('server_id')
            ->select('server_id', DB::raw('min(pending_at) as pending_at'))
            ->pendingClient()
            ->groupBy('server_id')
        ;
        $reports = $query->get();
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
        $vipClientFilter = function ($report) use ($servers) {
            // Server deleted
            if (!$server = array_get($servers, $report->server_id)) {
                return false;
            }

            // If the server is not assigned to a client, there's no access to suspend.
            if (!$access = $server->access) {
                return false;
            }

            // No reason to suspend already suspended servers.
            if ($access->is_suspended) {
                return false;
            }

            // VIP clients do not get auto suspended.
            if ($access->client->billing_ignore_auto_suspend) {
                return false;
            }

            // Other clients do get auto suspended.
            return true;
        };
        $suspension = function ($report) use ($suspensionLastDate, $servers) {
            $server = $servers[$report->server_id];

            if ($suspensionLastDate->gt($report->pending_at)) {
                // suspend & send suspended message
                $this->suspension->suspendServer($server, $report->pending_at);
                return;
            }

            // send suspend warning message
            $this->suspension->suspendWarning($server, $report->pending_at);
        };

        $reports
            ->filter($vipClientFilter)
            ->each($suspension)
        ;
    }
}
