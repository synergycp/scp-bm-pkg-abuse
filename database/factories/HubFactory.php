<?php

use App\Hub;

$factory->define(Hub\Hub::class, function (Faker\Generator $faker) {
    return [
        'switch_id' => $faker->name,
        'ip' => $faker->ipv4,
        'port' => $faker->numerify('####'),
        'type' => Hub\Hub::TYPE_JUNIPER,
    ];
});
