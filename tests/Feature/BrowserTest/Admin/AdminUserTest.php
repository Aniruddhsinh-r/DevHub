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

test('admin search and soft delete user', function () {
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

test('Admin can restore user', function () {
    $user = User::factory()->create(['deleted_at'=>	"2026-07-09 12:25:56"]);
    AdminLogin();

    visit('/admin/recover-users')
    ->assertSee($user->name)
    ->click('Restore');

    $this->assertDatabaseHas('users', ['id' => $user->id,'deleted_at' => null]);
});

test('Admin can permanently delete user', function () {
    $user = User::factory()->create(['deleted_at'=>	"2026-07-09 12:25:56"]);
    $user->assignRole(UserRole::AUTHOR);
    AdminLogin();

    $page = visit('/admin/recover-users')
    ->assertSee($user->name)
    ->press('Delete Forever');
    $page->click('[dusk="DeleteBTN"]')
    ->assertDontSee($user->name);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('Admin can cancel permanently delete user request', function () {
    $user = User::factory()->create(['deleted_at'=>	"2026-07-09 12:25:56"]);
    $user->assignRole(UserRole::AUTHOR);
    AdminLogin();

    $page = visit('/admin/recover-users')
    ->assertSee($user->name)
    ->press('Delete Forever');
    $page->click('Cancel')
    ->assertSee($user->name);

    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

test('Guest cant access or search on user detail page', function () {
    visit('/admin/recover-users')
    ->assertRoute('login');
});

test('Admin can view author profile pages', function () {
    $user = User::factory()->create(); 
    AdminLogin();

    visit('/admin/users')
        ->assertSee($user->name)
        ->click('View')
        ->assertRoute('admin.show.user',['user' => $user->uuid])
    ;
});

test('Admin can visite author profile edit page', function () {
    $user = User::factory()->create(); 
    AdminLogin();

    visit('/admin/users')
        ->assertSee($user->name)
        ->click('Edit')
        ->assertRoute('admin.edit.user',['user' => $user->uuid]);
});