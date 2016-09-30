<?php

namespace Packages\Abuse\App\Report;

use App\Services\Database\ModelRepository;

/**
 * Database logic for Abuse Reports.
 */
class ReportRepository
extends ModelRepository
{
    /**
     * @var ReportFilterService
     */
    protected $filter;

    /**
     * @param Report              $item
     * @param ReportFilterService $filter
     */
    public function boot(
        Report $item,
        ReportFilterService $filter
    ) {
        $this->setItem($item);
        $this->filter = $filter;
    }

    /**
     * @param ReportListRequest $request
     */
    public function request(ReportListRequest $request)
    {
        $query = $this->query();

        return $this->filter->request($request, $query);
    }

    /**
     * @return Report|null
     */
    public function latest()
    {
        return $this
            ->query()
            ->orderBy('reported_at', 'desc')
            ->first()
            ;
    }
}
