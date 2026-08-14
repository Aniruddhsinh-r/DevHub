<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Livewire\Livewire;
use App\Enums\UserRole;
use Spatie\Permission\Models\Role;

require_once __DIR__ . '/../Helpers/UserLogin.php';
require_once __DIR__ . '/../Helpers/AdminLogin.php';

beforeEach(function () {
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
});

uses(RefreshDatabase::class);

test('user can login with correct credentials', function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
    Role::firstOrCreate(['name' => UserRole::AUTHOR]);

    $user = User::factory()->create([
        'password' => 'password123',
    ]);
    $user->assignRole(UserRole::AUTHOR);

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'password123',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    $this->assertAuthenticatedAs($user);
});

test('Logged in user cannot visit login form', function () {
    UserLogin();

    $this->get(route('filament.app.auth.login'))
        ->assertRedirect();
});

test('Logged in user cannot visit register form', function () {
    $user = UserLogin();

    $this->get(route('filament.app.auth.register'))
        ->assertRedirect();
});

test('guest can view the login page', function () {
    $this->get(route('filament.app.auth.login'))
        ->assertSuccessful();
});

test('login fails with wrong password', function () {
    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors();

    $this->assertGuest();
});

test('login fails with unregister email', function () {
    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'nobody@example.com',
            'password' => 'password123',
        ])
        ->call('authenticate')
        ->assertHasFormErrors();

    $this->assertGuest();
});

test('admin cannot log into the app panel', function () {
    $admin = User::factory()->create([
        'password' => 'password123',
    ]);

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $admin->email,
            'password' => 'password123',
        ])
        ->call('authenticate')
        ->assertHasFormErrors();

    $this->assertGuest();
});

test('user can logout', function () {
    UserLogin();

    $this->assertAuthenticated();
    $this->post(route('filament.app.auth.logout'));

    $this->assertGuest();
});
