<?php

use App\Models\User;
use App\Enums\UserRole;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../../Helpers/UserLogin.php';
require_once __DIR__ . '/../../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
});
test('Author follow other authers.', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);
    UserLogin();

    visit(route('profile.show', $user))
    ->click('@follow-button')
    ->assertSee('Follow successfully.');

    $this->assertDatabaseHas('follows', [
        'follower_id' => auth()->id(),
        'followed_id' => $user->id,
    ]);
});

test('Author can follow other but not twice.', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);
    UserLogin();

    visit(route('profile.show', $user))
    ->click('@follow-button')
    ->assertSee('Follow successfully.');

    visit(route('profile.show', $user))
    ->click('@follow-button')
    ->assertSee('Unfollow successfully.');

    $this->assertDatabaseMissing('follows', [
        'follower_id' => auth()->id(),
        'followed_id' => $user->id,
    ]);
});

test('guest cant access profile page.', function () {
    $user = User::factory()->create();

    visit(route('profile.show', $user))
    ->assertRoute('login');
});

test('guest cant follow other author.', function () {
    $user = User::factory()->create();

    visit(route('profile.show', $user))
    ->assertDontSee('@follow-button')
    ->assertRoute('login');
});

test('admin cant access follow functionality profile page.', function () {
    AdminLogin();
    $user = User::factory()->create();

    visit(route('profile.show', $user))
    ->assertDontSee('@follow-button');
});
