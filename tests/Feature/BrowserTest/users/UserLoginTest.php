<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../../Helpers/UserLogin.php';
require_once __DIR__.'/../../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('it login a user', function () {
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    $user = User::factory()->create([
        'email' => 'adaniruddha@gmail.com',
        'password' => 'rathod1290',
    ]);
    $user->assignRole(UserRole::AUTHOR);

    visit('/login')
        ->fill('#form\\.email', 'adaniruddha@gmail.com')
        ->fill('#form\\.password', 'rathod1290')
        ->click('button[type="submit"][wire\\:target="authenticate"]')
        ->assertRoute('filament.app.pages.home');
});

test('it logout a user.', function () {
    UserLogin();

    visit('/home')
        ->click('button[aria-label="User menu"]')
        ->click('button.fi-dropdown-list-item[type="submit"]')
        ->assertPathIs('/login');
});

test('after login user cant access login page.', function () {
    UserLogin();

    visit('/login')
        ->assertRoute('filament.app.pages.home');
});

test('login fails with an unauthorized role', function () {
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
    $admin = User::factory()->create([
        'email' => 'notauthor@gmail.com',
        'password' => 'password123',
    ]);
    $admin->assignRole(UserRole::ADMIN);

    visit('/login')
        ->fill('#form\\.email', 'notauthor@gmail.com')
        ->fill('#form\\.password', 'password123')
        ->click('button[type="submit"][wire\\:target="authenticate"]')
        ->assertSee('These credentials do not match our records.');
});
