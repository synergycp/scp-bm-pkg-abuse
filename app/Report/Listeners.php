<?php

namespace Packages\Abuse\App\Report;

use App\Services\Log\EventLogger;

return [
    \App\Services\Client\Server\Events\ClientServerCreated::class => [
        Listeners\ClientServerDeleteReports::class,
    ],
    \App\Services\Client\Server\Events\ClientServerDeleted::class => [
        Listeners\ClientServerDeleteReports::class,
    ],
    \App\Services\Entity\Events\EntityDeleted::class => [
        Listeners\EntityDeleteReports::class,
    ],

    Events\ReportCreated::class => [
        EventLogger::class,
        Listeners\ReportCreatedCheck::class,
    ],
    Events\ReportStatusChanged::class => [
        EventLogger::class,
    ],
    Events\ReportClientReassigned::class => [
        EventLogger::class,
        Listeners\ReportSetPendingStatus::class,
        Listeners\ReportClientEmail::class,
    ],
    Comment\Events\CommentCreated::class => [
        EventLogger::class,
        Comment\Listeners\CommentUpdateParent::class,
        Comment\Listeners\CommentEmail::class,
    ],
];
