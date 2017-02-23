<?php

use App\Entity;

$factory->define(Entity\Entity::class, function (Faker\Generator $faker) {
    return [
        'ip' => $faker->ipv4,
        'gateway' => $faker->ipv4,
        'subnet_mask' => $faker->ipv4,
        'billing_id' => 'ip-'.$faker->numberBetween(24, 32),
        'vlan' => $faker->numberBetween(1, 500),
    ];
});
