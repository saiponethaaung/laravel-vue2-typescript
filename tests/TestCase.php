<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Faker\Factory as Faker;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations, DatabaseTransactions;
    protected $faker;
    protected $user;
    protected $token;

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
