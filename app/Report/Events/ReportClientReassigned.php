<?php

namespace Packages\Abuse\App\Report\Events;

use App\Log\Log;
use Carbon\Carbon;

class ReportClientReassigned extends ReportLoggableEvent
{
    public function log(Log $log)
    {
        if (Carbon::now()->diffInSeconds($this->report->created_at) < 3) {
            return false;
        }

        $log->setDesc('Abuse report client changed')
            ->setTarget($this->report)
            ->save();
    }
}
