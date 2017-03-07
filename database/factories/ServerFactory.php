<?php

$factory->define(App\Server\Server::class, function (Faker\Generator $faker) {
    return [
        'srv_id' => $faker->name,
    ];
});
