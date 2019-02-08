<?php

use Faker\Generator as Faker;

$factory->define(App\Models\ProjectPage::class, function (Faker $faker) {
    return [
        'project_id' => factory(App\Models\Project::class)->create()->id,
        'page_id' => 123321,
        'publish' => 0,
        'token' => $faker->text(50)
    ];
});
