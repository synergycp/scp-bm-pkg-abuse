<?php

namespace Packages\Abuse\App\Report;

use App\Support\ClassMap;
use App\Support\ScheduleServiceProvider as ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

/**
 * Provide the Report Feature to the Application.
 */
class ReportServiceProvider
    extends ServiceProvider
{
    /**
     * @var array
     */
    protected $providers = [
        ReportEventProvider::class,
        ReportRoutesProvider::class,
    ];

    /**
     * @var array
     */
    protected $commands = [
        Commands\DeleteOldAbuseReportsCommand::class,
    ];

    public function register()
    {
        collection($this->providers)->each(function ($provider) {
            $this->app->register($provider);
        });
    }

    /**
     * Boot the Report Service Feature.
     *
     * @param ClassMap $classMap
     */
    public function boot(ClassMap $classMap = null)
    {
        $classMap->map(
            'pkg.abuse.report',
            Report::class
        );

        parent::boot();
    }

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('abuse:report:expire')->daily();
    }
}
