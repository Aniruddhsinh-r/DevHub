<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../../Helpers/UserLogin.php';
require_once __DIR__ . '/../../Helpers/AdminLogin.php';

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
        ->assertRoute('filament.app.pages.home');
});

test('it logout a user.', function () {
    UserLogin();

    visit('/home')
    ->click('[data-test="Authbutton"]')
    ->click('[data-test="logout"]')
    ->assertRoute('filament.app.pages.home');
});

test('after login user cant access login page.', function () {
    UserLogin();

    visit('/login')
    ->assertRoute('filament.app.pages.home');
});

// test('after login admin cant access login page.', function () {
//     AdminLogin();

//     visit('/login')
//     ->assertRoute('admin.dashboard');
// });
