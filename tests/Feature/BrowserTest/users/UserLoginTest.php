<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('it login a user', function () {
    User::factory()->create([
        'email' => 'adanirudda@gmail.com',
        'password' => 'rathod1290'
    ]);

    visit('/login')
        ->fill('email', 'adanirudda@gmail.com')
        ->fill('password', 'rathod1290')
        ->press('@login-btn')
        ->assertRoute('home');
});

test('it logout a user.', function () {
    userLogin();

    visit('/home')
    ->click('[data-test="Authbutton"]')
    ->click('[data-test="logout"]')
    ->assertRoute('home');
});
