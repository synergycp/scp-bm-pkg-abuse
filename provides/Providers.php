<?php

namespace Packages\Abuse\App;

/**
 * Define Service Providers in order of when they are loaded.
 */

return [
    Contact\ContactServiceProvider::class,
    Email\EmailServiceProvider::class,
    Report\ReportServiceProvider::class,
    Module\ModuleServiceProvider::class,
    Suspension\SuspensionServiceProvider::class,
];
