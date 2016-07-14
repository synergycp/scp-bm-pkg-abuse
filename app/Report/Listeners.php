<?php

namespace Packages\Abuse\App\Report;

return [
    \App\Services\Client\Server\Events\ClientServerCreated::class => [
        Listeners\ClientServerDeleteAbuse::class,
    ],
    \App\Services\Client\Server\Events\ClientServerDeleted::class => [
        Listeners\ClientServerDeleteAbuse::class,
    ],
];
