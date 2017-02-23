<?php

use Packages\Abuse\App\Report\Report;

$factory->define(Report::class, function (Faker\Generator $faker) {
    return [
        'subject' => $faker->name,
        'addr' => $faker->macAddress,
        'body' => $faker->name,
        'entity_id' => rand(1, 20),
        'pending_type' => rand(0, 1),
        'msg_num' => rand(1, 20),
        'from' => $faker->name,
    ];
});
