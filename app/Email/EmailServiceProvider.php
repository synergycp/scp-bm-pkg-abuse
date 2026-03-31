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

    protected function schedule(Schedule $schedule)
    {
        $minutes = 5;
        try {
            $minutes = (int) (app('Settings')->{'pkg.abuse.sync_frequency'} ?? 5);
        } catch (\Throwable $e) {
            // Fall back to default if setting is unavailable.
        }

        if ($minutes < 1) {
            $minutes = 5;
        }

        $schedule->command('abuse:sync-email')->cron("*/{$minutes} * * * *");
    }
}
