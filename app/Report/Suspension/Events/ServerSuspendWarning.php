<?php

namespace Packages\Abuse\App\Report\Suspension\Events;

use App\Server\Server;

class ServerSuspendWarning extends SuspensionEvent
{
    /**
     * Max report date
     */
    public $maxReportDate;


    /**
     * Create a new event instance.
     */
    public function __construct(Server $server, $createdDate, $maxReportDate)
    {
        $this->maxReportDate = $maxReportDate;
        return parent::__construct($server, $createdDate);
    }
}