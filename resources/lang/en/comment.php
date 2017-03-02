<?php

$created = 'Abuse Report comment saved.';

return [
    'created' => [
        \App\Admin\Admin::class => $created,
        \App\Client\Client::class => $created,
        \App\Api\Integration\Integration::class => $created,
    ],
];
