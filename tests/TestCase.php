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
  
        $this->block = ChatBlock::where('title', 'Landing')->where('project_id', $this->project->id)->first();

        $this->section = ChatBlockSection::where('block_id', $this->block->id)->first();

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
