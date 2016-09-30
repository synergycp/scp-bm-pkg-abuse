<?php

namespace Packages\Abuse\App\Report\Events;

use App\Services\Log\Log;

class ReportStatusChanged extends ReportLoggableEvent
{
    public function log(Log $log)
    {
        $desc = sprintf(
            'Abuse report %s',
            $this->report->isResolved() ? 'resolved' : 'unresolved'
        );
        $targets = array_filter([
            $this->report,
            $this->report->server,
        ]);

        $log->setDesc($desc)
            ->setTargets($targets)
            ;
    }
}
