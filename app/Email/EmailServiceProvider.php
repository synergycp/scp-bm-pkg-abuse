<?php

namespace Packages\Abuse\App\Email;

use App\Setting\Setting;
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
        parent::boot();

        // Prevent null values on the archive_folder setting (Laravel's
        // ConvertEmptyStringsToNull middleware converts '' to null).
        Setting::saving(function (Setting $setting) {
            if ($setting->name === 'pkg.abuse.auth.archive_folder' && is_null($setting->value)) {
                $setting->value = '';
            }
        });
    }

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('abuse:sync-email')->everyFiveMinutes();
    }
}
