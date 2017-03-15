<?php

namespace Packages\Abuse\App\Report\Suspension\Events;

use Packages\Abuse\App\Report\Events\ReportEvent;

class ServerSuspendWarning extends ReportEvent
{
    /**
     * Max report date
     */
    public $maxReportDate;

    /**
     * Create a new event instance.
     */
    public function __construct($report, $maxReportDate)
    {
        $this->maxReportDate = $maxReportDate;
        return parent::__construct($report);
    }
}