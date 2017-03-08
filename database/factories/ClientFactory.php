<?php

use App\Client;

$factory->define(Client\Client::class, function (Faker\Generator $faker) {
    return [
        'first' => $faker->firstName,
        'last' => $faker->lastName,
        'email' => $faker->companyEmail,
    ];
});
