<?php

namespace Packages\Abuse\App\Suspension;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;

/**
 * Setup Abuse Suspension Event Listeners.
 */
class SuspensionEventProvider extends EventServiceProvider
{
    protected $listen = [
        Events\ServerSuspend::class => [
            Listeners\SuspendedEmail::class,
        ],
        Events\ServerSuspendWarning::class => [
            Listeners\SuspendWarningEmail::class,
        ],
    ];
}
