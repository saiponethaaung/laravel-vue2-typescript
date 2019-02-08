<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;

class LoginTest extends TestCase
{
    public function testUserLoginEmailRequired()
    {
        $err = [
            'status' => false,
            'code' => 422,
            'mesg' => 'The email field is required.'
        ];

        $featureTest = $this->post(route('api.login'), [
            'email' => '',
            'password' => '321123'
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson($err);
    }
    
    public function testUserLoginPasswordRequired()
    {
        $err = [
            'status' => false,
            'code' => 422,
            'mesg' => 'The password field is required.'
        ];

        $featureTest = $this->post(route('api.login'), [
            'email' => 'saipone@pixeldirects.com',
            'password' => ''
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson($err);
    }
    
    public function testUserLoginFailed()
    {
        $err = [
            'status' => false,
            'code' => 422,
            'mesg' => 'Invalid email or username'
        ];

        $featureTest = $this->post(route('api.login'), [
            'email' => 'saipone@pixeldirects.com',
            'password' => '321123'
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson($err);
    }
    
    public function testUserLoginWrongPassword()
    {
        User::create([
            'name' => 'Sai Pone Tha Aung',
            'email' => 'saipone@pixeldirects.com',
            'password' => bcrypt('321123')
        ]);

        $err = [
            'status' => false,
            'code' => 422,
            'mesg' => 'Invalid email or username'
        ];

        $featureTest = $this->post(route('api.login'), [
            'email' => 'saipone@pixeldirects.com',
            'password' => '123'
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson($err);
    }
    
    public function testUserLogin()
    {
        User::create([
            'name' => 'Sai Pone Tha Aung',
            'email' => 'saipone@pixeldirects.com',
            'password' => bcrypt('321123')
        ]);

        $featureTest = $this->post(route('api.login'), [
            'email' => 'saipone@pixeldirects.com',
            'password' => '321123'
        ], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'code',
                'mesg',
                'token'
            ])
            ->assertJson([
                'status' => true,
                'code' => 200,
                'mesg' => 'Login Success'
            ]);
    }
}
