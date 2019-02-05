<?php

use Faker\Generator as Faker;

$factory->define(App\Models\ProjectUser::class, function (Faker $faker) {
    $user = factory(App\Models\User::class)->create();
    return [
        'project_id' => factory(App\Models\Project::class)->create(['user_id' => $user->id])->id,
        'user_id' => $user->id,
        'user_type' => 0
    ];
});
