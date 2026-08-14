<?php

use Spatie\Permission\Models\Role;
use Livewire\Livewire;
use App\Enums\UserRole;
use App\Models\User;
use Filament\Auth\Pages\Register;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate([
        'name' => UserRole::AUTHOR,
        'guard_name' => 'web'
    ]);
});

test('user registration test', function () {
    Livewire::test(Register::class)
        ->fillForm([
            'name' => 'khabibji',
            'email' => 'khabib@example.com',
            'password' => 'khabibji',
            'passwordConfirmation' => 'khabibji',
        ])
        ->call('register')
        ->assertHasNoFormErrors();

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'name' => 'khabibji',
        'email' => 'khabib@example.com',
    ]);
});

test('guest can view the registration page', function () {
    $this->get(route('filament.app.auth.register'))
        ->assertSuccessful();
});

test('authenticated user not access registration page', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);

    $this->actingAs($user)
        ->get(route('filament.app.auth.register'))
        ->assertRedirect();
});

test('registration fails when email already taken', function () {
    $existing = User::factory()->create(['email' => 'taken@example.com']);

    Livewire::test(Register::class)
        ->fillForm([
            'name' => 'Someone Else',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'passwordConfirmation' => 'password123',
        ])
        ->call('register')
        ->assertHasFormErrors(['email' => 'unique']);
});

test('registration fails when passwords do not match', function () {
    Livewire::test(Register::class)
        ->fillForm([
            'name' => 'Someone',
            'email' => 'someone@example.com',
            'password' => 'password123',
            'passwordConfirmation' => 'password456',
        ])
        ->call('register')
        ->assertHasFormErrors(['password']);

    $this->assertDatabaseMissing('users', [
        'email' => 'someone@example.com',
    ]);
});

