<?php

namespace Packages\Abuse\App\Suspension;

use Packages\Abuse\App\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Server\ServerRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportList
{
    private $suspension;

    public function __construct(Suspension $suspension, ServerRepository $server)
    {
        $this->suspension = $suspension;
        $this->server = $server;
    }

    /**
     *
     */
    public function get()
    {
        $suspensionLastDate = $this->suspension->maxReportDate()->toDateString();

        $vipClientFilter = function($report) {

            try {
                $server = $this->server->findOrFail($report->server_id)->load('access.client');

                if (!$access = $server->access) {
                    return false;
                }

                if ($access->is_suspended) {
                    return false;
                }

                return !$access->client->billing_ignore_auto_suspend;

            } catch (NotFoundHttpException $exception) {
                return false;
            }

        };

        $suspension = function($report) use ($suspensionLastDate) {

            $server = $this->server->findOrFail($report->server_id)->load('access.client');

            if ($report->created_at->toDateString() <= $suspensionLastDate) {
                // suspend & send suspended message
                $this->suspension->suspendServer($server, $report->server->created_at);
                return;
            }
            // send suspend warning message
            $this->suspension->suspendWarning($server, $report->server->created_at);
        };

        $reportModel = new Report\Report();
        Report\Report::whereNotNull('server_id')
            ->pendingClient()
            ->where('created_at', function($query) use ($reportModel) {
                $query
                    ->select( DB::raw('min(created_at)') )
                    ->from(DB::raw($reportModel->getTable() . ' ar2'))
                    ->whereRaw($reportModel->getTable() . '.server_id = ar2.server_id')
                ;
            })
            ->groupBy('server_id')
            ->select('server_id', 'created_at')
            ->get()
            ->filter($vipClientFilter)
            ->each($suspension)
        ;
    }
}
