<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Filament\Pages\Auth\Register;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate([
        'name' => UserRole::AUTHOR,
        'guard_name' => 'web',
    ]);
});
// beforeEach(function () {
//     Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
//     Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
//     Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
// });

test('user registration test', function () {
    Livewire::test(Register::class)
        ->fillForm([
            'name' => 'khabibji',
            'email' => 'khabib@example.com',
            'password' => 'khabibji',
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
        ])
        ->call('register')
        ->assertHasFormErrors(['email' => 'unique']);
});

test('registered user is automatically assigned the author role', function () {
    Livewire::test(Register::class)
        ->fillForm(['name' => 'Role Check', 'email' => 'rolecheck@example.com', 'password' => 'password123'])
        ->call('register')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'rolecheck@example.com')->first();
    expect($user->hasRole(UserRole::AUTHOR))->toBeTrue();
});

test('registration fails when name exceeds max length', function () {
    Livewire::test(Register::class)
        ->fillForm(['name' => 'sk', 'email' => 'name@gmail.com', 'password' => 'password'])
        ->call('register')
        ->assertHasFormErrors(['name' => 'min']);

    $this->assertDatabaseMissing('users', ['email' => 'toolongname@example.com']);
});
