<?php

namespace Packages\Abuse\App\Report;

use App\Api;

/**
 * Routing for Abuse Report API Requests.
 */
class ReportController extends Api\Controller
{
    use Api\Traits\ShowResource;
    use Api\Traits\ListResource;
    use Api\Traits\DeleteResource;
    use Api\Traits\UpdateResource;

    /**
     * @var ReportService
     */
    protected $report;

    /**
     * @var ReportFilterService
     */
    protected $filter;

    /**
     * @var ReportUpdateService
     */
    protected $update;

    /**
     * @var ReportRepository
     */
    protected $items;

    /**
     * @var ReportTransformer
     */
    protected $transform;

    /**
     * @param ReportService       $report
     * @param ReportRepository    $items
     * @param ReportTransformer   $transform
     * @param ReportUpdateService $update
     * @param ReportFilterService $filter
     */
    public function boot(
        ReportService $report,
        ReportRepository $items,
        ReportTransformer $transform,
        ReportUpdateService $update,
        ReportFilterService $filter
    ) {
        $this->items = $items;
        $this->report = $report;
        $this->update = $update;
        $this->filter = $filter;
        $this->transform = $transform;
    }

    /**
     * Filter the repository.
     */
    public function filter()
    {
        $this->items->filter([$this->filter, 'viewable']);
    }
}
