<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use App\Enums\UserRole;
require_once __DIR__ . '/../../Helpers/UserLogin.php';
require_once __DIR__ . '/../../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate([
        'name' => UserRole::AUTHOR,
        'guard_name' => 'web'
    ]);
});

test('Register a user.', function () {
    $email = 'roman'.time().'@gmail.com';

    visit(route('register.create'))
        ->fill('name', 'Romanreigns')
        ->fill('email', $email)
        ->fill('password', 'Roman123')
        ->click('Create account')
        ->assertRoute('filament.app.pages.home');

    $this->assertDatabaseHas('users', [
        'email' => $email,
    ]);
});

test('after login user cant access login page.', function () {
    UserLogin();

    visit('/register')
    ->assertRoute('filament.app.pages.home');
});

test('after login admin cant access login page.', function () {
    AdminLogin();

    visit('/login')
    ->assertPathIs('/admin');
});
