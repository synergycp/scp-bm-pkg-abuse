<?php

namespace Packages\Abuse\App\Suspension;

use Illuminate\Console\Scheduling\Schedule;
use App\Support\ScheduleServiceProvider as ServiceProvider;

/**
 * Global setup of Abuse Report Suspension.
 */
class SuspensionServiceProvider
    extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        Commands\SuspensionServerCommand::class,
    ];

    public function boot()
    {
        $this->commands($this->commands);

        parent::boot();
    }

    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->command('abuse:suspension')
            ->daily()
            ;
    }
}
