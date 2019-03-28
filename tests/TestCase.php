<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Faker\Factory as Faker;

use Agent;

use App\Models\User;
use App\Models\UserSession;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\ChatBlock;
use App\Models\ChatBlockSection;
use PragmaRX\Google2FA\Google2FA;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations, DatabaseTransactions;
    protected $faker;
    protected $user;
    protected $token;
    protected $project;
    protected $block;
    protected $section;
    protected $g2fa;
    protected $agent;
    protected $identifier;

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->artisan('passport:install');

        $this->agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.109 Safari/537.36';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        
        $this->g2fa = new Google2FA();

        $identifier = '';
        do {
            $identifier = str_random(247).date("Ymd");
        } while (UserSession::where('identifier', $identifier)->count()!==0);

        $this->identifier = $identifier;

        $this->user = factory(User::class)->create();

        $this->token = $this->user->createToken('phpunit')->accessToken;

        $this->project = factory(Project::class)->create(['user_id' => $this->user->id]);
  
        $this->block = ChatBlock::where('title', 'Landing')->where('project_id', $this->project->id)->first();

        $this->section = ChatBlockSection::where('block_id', $this->block->id)->first();

        UserSession::create([
            'parent_id' => $this->user->id,
            'is_valid' => 1,
            'identifier' => $identifier,
            'browser' => 'Chrome',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'os' => 'Windows',
            'last_login' => date("Y-m-d H:i:s")
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
