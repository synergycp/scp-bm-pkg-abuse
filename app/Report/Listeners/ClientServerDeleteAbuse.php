<?php

namespace Packages\Abuse\App\Report\Listeners;

use App\Models\Repositories\AbuseReportRepository;
use App\Services\Abuse\ReportService;
use App\Services\Client\Server\Events;

/**
 * Resolve any related abuse reports for a Client's Server access.
 */
class ClientServerDeleteAbuse
{
    /**
     * @var ReportService
     */
    protected $report;

    /**
     * @var AbuseReportRepository
     */
    protected $reports;

    /**
     * @param ReportService         $report
     * @param AbuseReportRepository $reports
     */
    public function __construct(
        ReportService $report,
        AbuseReportRepository $reports
    ) {
        $this->report = $report;
        $this->reports = $reports;
    }

    /**
     * Handle the event.
     *
     * @param Events\ClientServerLoggableEvent $event
     */
    public function handle(Events\ClientServerLoggableEvent $event)
    {
        $access = $event->target;

        if ($access->parent_id) {
            return;
        }

        $this->reports->query()
            ->open()
            ->where('server_id', $access->server_id)
            ->each([$this->report, 'resolve'])
            ;
    }
}
