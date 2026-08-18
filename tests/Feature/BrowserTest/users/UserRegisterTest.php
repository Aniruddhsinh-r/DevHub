<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate([
        'name' => UserRole::AUTHOR,
        'guard_name' => 'web',
    ]);
});

test('Register a user', function () {
    $email = 'roman'.time().'@gmail.com';

    visit(route('filament.app.auth.register'))
        ->fill('#form\\.name', 'Romanreigns')
        ->fill('#form\\.email', $email)
        ->fill('#form\\.password', 'Roman123')
        ->click('button[type="submit"][wire\\:target="register"]')
        ->assertRoute('filament.app.pages.home');

    $this->assertDatabaseHas('users', [
        'email' => $email,
    ]);
});

test('after login user cant access register page', function () {
    UserLogin();

    visit('/register')
        ->assertRoute('filament.app.pages.home');
});

test('after login user cant access login page', function () {
    UserLogin();

    visit('/login')
        ->assertRoute('filament.app.pages.home');
});

test('registering with an already-taken email shows a validation error', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    visit(route('filament.app.auth.register'))
        ->fill('#form\\.name', 'Someone New')
        ->fill('#form\\.email', 'taken@example.com')
        ->fill('#form\\.password', 'password123')
        ->click('button[type="submit"][wire\\:target="register"]')
        ->assertSee('has already been taken');

    $this->assertDatabaseCount('users', 1);
});