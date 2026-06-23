<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
require_once __DIR__ . '/../../Helpers/userLogin.php';
require_once __DIR__ . '/../../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate([
        'name' => 'author',
        'guard_name' => 'web'
    ]);
});

test('Register a user.', function () {
    $email = 'roman'.time().'@gmail.com';

    visit(route('register.create'))
        ->fill('name', 'Romanreigns+')
        ->fill('email', $email)
        ->fill('password', 'Roman123')
        ->click('Register')
        ->assertRoute('home');

    $this->assertDatabaseHas('users', [
        'email' => $email,
    ]);
});

test('after login user cant access login page.', function () {
    userLogin();

    visit('/register')
    ->assertRoute('home');
});

test('after login admin cant access login page.', function () {
    adminLogin();

    visit('/login')
    ->assertRoute('admin.dashboard');
});
