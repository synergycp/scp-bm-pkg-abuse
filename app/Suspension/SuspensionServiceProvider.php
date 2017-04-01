<?php

namespace Packages\Abuse\App\Suspension;

use App\Support\ServiceProvider;

/**
 * Global setup of Abuse Report Suspension.
 */
class SuspensionServiceProvider
    extends ServiceProvider
{
    protected $providers = [
        Commands\CommandServiceProvider::class,

        SuspensionEventProvider::class,
    ];
}
