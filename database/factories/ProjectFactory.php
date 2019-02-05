<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Project::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'timezone' => '',
        'user_id' => factory(App\Models\User::class)->create()->id
    ];
});
