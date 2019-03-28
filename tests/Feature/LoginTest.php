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
                'otp' => '321123'
            ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson($err);
    }
    
    public function testUserLoginOtpRequired()
    {
        $err = [
            'status' => false,
            'code' => 422,
            'mesg' => 'The otp field is required.'
        ];

        $featureTest = $this->post(route('api.login'), [
                'email' => 'saipone@pixeldirects.com',
                'otp' => ''
            ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson($err);
    }
    
    public function testUserLoginFailed()
    {
        $err = [
            'status' => false,
            'code' => 422,
            'mesg' => 'Invalid email or otp!'
        ];

        $featureTest = $this->post(route('api.login'), [
                'email' => 'saipone@pixeldirects.com',
                'otp' => '321123'
            ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson($err);
    }
    
    public function testUserLoginWrongOtp()
    {
        User::create([
            'name' => 'Sai Pone Tha Aung',
            'email' => 'saipone@pixeldirects.com',
            'auth_code' => '6P2YJQR32MJC4NZT',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'password' => bcrypt('123')
        ]);

        $err = [
            'status' => false,
            'code' => 422,
            'mesg' => 'Invalid otp code!'
        ];

        $featureTest = $this->post(route('api.login'), [
                'email' => 'saipone@pixeldirects.com',
                'otp' => '123'
            ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson($err);
    }
    
    public function testUserLogin()
    {
        $user = User::create([
            'name' => 'Sai Pone Tha Aung',
            'email' => 'saipone@pixeldirects.com',
            'auth_code' => '6P2YJQR32MJC4NZT',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'password' => bcrypt('123')
        ]);

        $featureTest = $this->post(route('api.login'), [
                'email' => 'saipone@pixeldirects.com',
                'otp' => $this->g2fa->getCurrentOtp($user->auth_code)
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
