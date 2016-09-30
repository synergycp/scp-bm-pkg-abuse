<?php

namespace Packages\Abuse\App\Report\Events;
use App\Log\Log;

class ReportCreated extends ReportLoggableEvent
{
    public function log(Log $log)
    {
        if (!$this->report->server_id) {
            return false;
        }

        $targets = array_filter([
            $this->report,
            $this->report->server,
        ]);

        $log->setDesc('Abuse reported')
            ->setTargets($targets)
            ;
    }
}
