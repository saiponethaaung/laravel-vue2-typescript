<?php

use Faker\Generator as Faker;

$factory->define(App\Models\ChatBlockSection::class, function (Faker $faker) {
    return [
        'title' => $faker->name
    ];
});
