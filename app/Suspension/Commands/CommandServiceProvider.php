<?php

namespace Packages\Abuse\App\Suspension\Commands;

use Illuminate\Console\Scheduling\Schedule;
use App\Support\ScheduleServiceProvider as ServiceProvider;

/**
 * Global setup of Abuse Report Suspension.
 */
class CommandServiceProvider
extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        SuspensionServerCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->command('pkg:abuse:suspension')
            ->daily()
            ;
    }
}
