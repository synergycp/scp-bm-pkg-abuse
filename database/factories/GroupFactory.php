<?php

use App\Group;

$factory->define(Group\Group::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
    ];
});
