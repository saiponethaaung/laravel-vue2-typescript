<?php

use Faker\Generator as Faker;

$factory->define(App\Models\ChatBlock::class, function (Faker $faker) {
    return [
        'title' => $faker->name,
        'is_lock' => 0,
        'type' => 1
    ];
});
