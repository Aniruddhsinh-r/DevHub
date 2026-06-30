<?php

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
require_once __DIR__.'/../../Helpers/AdminLogin.php';
require_once __DIR__.'/../../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
});

test('Admin fetch user details', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);
    AdminLogin();

    visit('/admin/users')
    ->assertSee($user->name);
});

test('admin search and delete user', function () {
    $user =  User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);
    AdminLogin();

    visit('/admin/users?search='.$user->name)
    ->assertSee($user->name)
    ->press('Remove');

    $this->assertDatabaseMissing('articles', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('likes', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('comments', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('views', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('bookmarks', ['user_id' => $user->id,]);
});

test('guest cant access user detail page', function () {
    visit('/admin/users')
    ->assertRoute('login');
});

test('Author cant access or search on user detail page', function () {
    $user = User::factory()->create();
    UserLogin();

    visit('/admin/users?search='.$user->name)
    ->assertSee('403')
    ->assertSee('Forbidden');
});
