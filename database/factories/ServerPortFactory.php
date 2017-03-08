<?php

$factory->define(App\Server\Port\Port::class, function (Faker\Generator $faker) {
    return [
        'mac' => $faker->macAddress,
    ];
});
