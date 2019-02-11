<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\ChatBlock;
use App\Models\ChatBlockSection;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations, DatabaseTransactions;
    protected $faker;
    protected $user;
    protected $token;
    protected $project;
    protected $block;
    protected $section;

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->artisan('passport:install');
        $this->user = factory(User::class)->create();

        $token = json_decode($this->post(
            route('api.login'),
            [
                'email' => $this->user->email,
                'password' => '123321'
            ]
        )->getContent());

        $this->token = $token->token;

        $this->project = factory(Project::class)->create(['user_id' => $this->user->id]);
        $projectUser = factory(ProjectUser::class)->create([
            'project_id' => $this->project->id,
            'user_id' => $this->project->user_id,
            'user_type' => 1
        ]);

        $this->block = factory(ChatBlock::class)->create([
            'title' => 'Landing',
            'project_id' => $this->project->id,
        ]);

        $this->section = factory(ChatBlockSection::class)->create([
            'block_id' => $this->block->id
        ]);

    }
    /**
     * Reset the migrations
     */
    public function tearDown()
    {
        $this->artisan('migrate:reset');
        parent::tearDown();
    }
}
