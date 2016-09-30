<?php

namespace Packages\Abuse\App\Report\Events;

use App\Services\Log\Log;
use App\Services\Log\LoggableEvent;

abstract class ReportLoggableEvent extends ReportEvent implements LoggableEvent
{
    abstract public function log(Log $log);
}
