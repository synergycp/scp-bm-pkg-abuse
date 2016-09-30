<?php

namespace Packages\Abuse\App\Report\Listeners;

use Packages\Abuse\App\Report\ReportRepository;
use App\Services\Entity\Events\EntityDeleted;

/**
 * When an Entity is deleted, update the associated Reports.
 */
class EntityDeleteReports
{
    /**
     * @var ReportRepository
     */
    protected $reports;

    /**
     * @param ReportRepository $reports
     */
    public function __construct(
        ReportRepository $reports
    ) {
        $this->reports = $reports;
    }

    /**
     * Handle the event.
     *
     * @param EntityDeleted $event
     */
    public function handle(EntityDeleted $event)
    {
        $this->reports
            ->query()
            ->where('entity_id', $event->target->id)
            ->update([
                'entity_id' => null,
            ])
            ;
    }
}
