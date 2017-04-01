<?php

namespace Packages\Abuse\App\Report;

use App\Client\Server\Events as AccessEvents;
use App\Entity\Events as EntityEvents;
use App\Log\EventLogger;
use App\Support\EventServiceProvider;

/**
 * Setup Abuse Report Event Listeners.
 */
class ReportEventProvider
extends EventServiceProvider
{
    protected $listen = [
        AccessEvents\ClientServerCreated::class => [
            Listeners\ClientServerDeleteReports::class,
        ],
        AccessEvents\ClientServerDeleted::class => [
            Listeners\ClientServerDeleteReports::class,
        ],

        EntityEvents\EntityDeleted::class => [
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
}
