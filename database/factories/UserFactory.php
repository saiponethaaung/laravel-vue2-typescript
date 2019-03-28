<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'image' => '',
        'auth_code' => '6P2YJQR32MJC4NZT',
        'activated_at' => gmdate('Y-m-d H:i:s'),
        'password' => bcrypt('123321'), // secret
        'remember_token' => str_random(10),
    ];
});
