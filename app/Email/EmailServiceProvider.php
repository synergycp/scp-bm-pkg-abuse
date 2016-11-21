<?php

namespace Packages\Abuse\App\Email;

use Illuminate\Console\Scheduling\Schedule;
use App\Support\ScheduleServiceProvider as ServiceProvider;

/**
 * Global setup of Abuse Report Emails.
 */
class EmailServiceProvider
extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        Commands\SyncAbuseEmailsCommand::class,
    ];

    public function boot()
    {
        $this->commands($this->commands);

        parent::boot();
    }

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('abuse:sync-email')->everyFiveMinutes();
    }
}
