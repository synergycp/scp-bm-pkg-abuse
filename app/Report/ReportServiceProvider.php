<?php

namespace Packages\Abuse\App\Report;

use App\Support\ClassMap;
use Illuminate\Support\ServiceProvider;

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
    public function boot(ClassMap $classMap)
    {
        $classMap->map(
            'pkg.abuse.report',
            Report::class
        );
    }
}
