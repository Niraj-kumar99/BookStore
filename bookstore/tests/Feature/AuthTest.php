<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_SuccessfulRegistration()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',])
        ->json('POST', '/api/auth/registeruser', [
            "usertype" => "user",
            "firstname" => "sourav",
            "lastname" => "kumar",
            "phone_no" => "1546973165",
            "email" => "sourav@gmail.com",
            "password" => "sourav@123",
            "confirm_password" => "sourav@123"
        ]);
        $response->assertStatus(201)->assertJson(['message' => 'User successfully registered']);
    }

    public function test_If_Usere_Already_Registered()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',])
            ->json('POST', '/api/auth/registeruser',[
            "usertype" => "user",
            "firstname" => "sourav",
            "lastname" => "kumar",
            "phone_no" => "1546973165",
            "email" => "sourav@gmail.com",
            "password" => "sourav@123",
            "confirm_password" => "sourav@123"
        ]);
        $response->assertStatus(200)->assertJson(['message' => 'Mail already taken......']);

    }

    //Login
    public function test_SuccessfulLogin()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/auth/loginuser', 
        [
            "email" => "kumarnkj35@gmail.com",
            "password" => "kumar3516",
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'User successfully login']);
    }

    public function test_SuccessfulLogout()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbnVzZXIiLCJpYXQiOjE2MzY0MzAyNTgsImV4cCI6MTYzNjQzMzg1OCwibmJmIjoxNjM2NDMwMjU4LCJqdGkiOiIxT09iRFBDbUNtRXlFdktCIiwic3ViIjoyLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.klVPabM7GEJ-cohXSr5IuomlEFL-CWSA3WxCFNXncjA'
        ])->json('POST', '/api/auth/logout');
        $response->assertStatus(200)->assertJson(['message'=> 'User successfully signed out']);
    }
}
