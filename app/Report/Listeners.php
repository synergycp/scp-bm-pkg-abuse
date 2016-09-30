<?php

namespace Packages\Abuse\App\Report;

use App\Log\EventLogger;

return [
    \App\Client\Server\Events\ClientServerCreated::class => [
        Listeners\ClientServerDeleteReports::class,
    ],
    \App\Client\Server\Events\ClientServerDeleted::class => [
        Listeners\ClientServerDeleteReports::class,
    ],
    \App\Entity\Events\EntityDeleted::class => [
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
