<?php

use App\Hub\Port;

$factory->define(Port\Port::class, function (Faker\Generator $faker) {
    return [
        'name' => 'ge-0/0/'.rand(1, 50),
    ];
});
