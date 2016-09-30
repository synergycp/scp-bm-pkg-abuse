<?php

namespace Packages\Abuse\App\Report\Listeners;

use Packages\Abuse\App\Report\ReportService;
use Packages\Abuse\App\Report\ReportRepository;
use App\Services\Client\Server\Events;

/**
 * Resolve any related abuse reports for a Client's Server access.
 */
class ClientServerDeleteReports
{
    /**
     * @var ReportService
     */
    protected $report;

    /**
     * @var ReportRepository
     */
    protected $reports;

    /**
     * @param ReportService    $report
     * @param ReportRepository $reports
     */
    public function __construct(
        ReportService $report,
        ReportRepository $reports
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

        $this->reports
            ->query()
            ->open()
            ->where('server_id', $access->server_id)
            ->each([$this->report, 'resolve'])
            ;
    }
}
