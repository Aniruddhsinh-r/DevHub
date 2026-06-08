<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

function adminLogin()
{
    User::factory()->create([
        'email' => 'harshrajsinh@gmail.com',
        'password' => 'IAmHarsh',
        'role' => 'admin'
    ]);

    visit('/login')
    ->fill('email', 'harshrajsinh@gmail.com')
    ->fill('password', 'IAmHarsh')
    ->press('@login-btn')
    ->assertRoute('admin.dashboard');
}
