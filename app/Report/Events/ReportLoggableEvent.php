<?php

namespace Packages\Abuse\App\Report\Events;

use App\Log\Log;
use App\Log\LoggableEvent;

abstract class ReportLoggableEvent extends ReportEvent implements LoggableEvent
{
    abstract public function log(Log $log);
}
