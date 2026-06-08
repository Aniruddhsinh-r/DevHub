<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

function userLogin()
{
    User::factory()->create([
        'email' => 'adanirudda@gmail.com',
        'password' => 'rathod1290'
    ]);

    visit('/login')
    ->fill('email', 'adanirudda@gmail.com')
    ->fill('password', 'rathod1290')
    ->press('@login-btn')
    ->assertRoute('home');
}
