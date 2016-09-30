<?php

namespace Packages\Abuse\App\Report\Listeners;

use Packages\Abuse\App\Report\Events\ReportCreated;
use Packages\Abuse\App\Report\Events\ReportClientReassigned;

class ReportCreatedCheck
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ReportCreated  $event
     * @return void
     */
    public function handle(ReportCreated $event)
    {
        $report = $event->report;

        if ($report->client_id) {
            event(new ReportClientReassigned($report));
        }
    }
}
