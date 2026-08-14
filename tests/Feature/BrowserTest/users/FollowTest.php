<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../../Helpers/UserLogin.php';
require_once __DIR__.'/../../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
});

test('Author follow other authors', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);
    UserLogin();

    visit(route('filament.app.resources.users.view', ['record' => $user]))
        ->click('Follow')
        ->assertSee('Unfollow');

    $this->assertDatabaseHas('follows', [
        'follower_id' => auth()->id(),
        'followed_id' => $user->id,
    ]);
});

test('Author can follow other but not twice', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);
    UserLogin();

    visit(route('filament.app.resources.users.view', ['record' => $user]))
        ->click('Follow')
        ->assertSee('Unfollow');

    visit(route('filament.app.resources.users.view', ['record' => $user]))
        ->click('Unfollow')
        ->assertSee('Follow');

    $this->assertDatabaseMissing('follows', [
        'follower_id' => auth()->id(),
        'followed_id' => $user->id,
    ]);
});

test('guest cant access profile page', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);

    visit(route('filament.app.resources.users.view', ['record' => $user]))
        ->assertUrlIs(route('filament.app.auth.login'));
});

test('guest cant follow other author', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);

    visit(route('filament.app.resources.users.view', ['record' => $user]))
        ->assertDontSee('Follow')
        ->assertUrlIs(route('filament.app.auth.login'));

    $this->assertDatabaseMissing('follows', ['followed_id' => $user->id]);
});

test('admin cant access follow functionality on profile page', function () {
    AdminLogin();
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);

    visit(route('filament.app.resources.users.view', ['record' => $user]))
        ->assertSee('403');

    $this->assertDatabaseMissing('follows', ['followed_id' => $user->id]);
});
