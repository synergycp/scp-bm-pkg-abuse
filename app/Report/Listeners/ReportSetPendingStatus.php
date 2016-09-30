<?php

namespace Packages\Abuse\App\Report\Listeners;

use Packages\Abuse\App\Report\Events\ReportClientReassigned;

class ReportSetPendingStatus
{
    public function handle(ReportClientReassigned $event)
    {
        $report = $event->report;
        $setPend = $report->client_id
            ? 'setPendingClient'
            : 'setPendingAdmin';

        $report->$setPend()->save();
    }
}
