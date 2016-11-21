<?php

namespace Packages\Abuse\App\Report\Events;

use Packages\Abuse\App\Report\Report;
use App\Support\Event;
use App\Support\Database\SerializesModels;

abstract class ReportEvent extends Event
{
    use SerializesModels;

    /**
     * @var Report
     */
    public $report;

    /**
     * Create a new event instance.
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
