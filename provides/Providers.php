<?php

namespace Packages\Abuse\App;

/**
 * Define Service Providers in order of when they are loaded.
 */

return [

    Email\EmailServiceProvider::class,

    Report\ReportEventServiceProvider::class,

];
