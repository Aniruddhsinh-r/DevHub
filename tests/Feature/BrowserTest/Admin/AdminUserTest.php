<?php

use App\Models\User;
use App\Models\Article;
use App\Models\Comment;
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
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);
    Article::factory()->create(['user_id' => $user->id]);
    Comment::factory()->create(['user_id' => $user->id]);
    AdminLogin();

    visit('/admin/users')
        ->assertSee($user->name)
        ->click('Delete')
        ->click('button[wire\:target="callMountedAction"]')
        ->assertDontSee($user->name);

    $this->assertSoftDeleted('articles', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('likes', ['user_id' => $user->id,]);
    $this->assertSoftDeleted('comments', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('views', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('bookmarks', ['user_id' => $user->id,]);
});

test('guest cant access user detail page', function () {
    visit('/admin/users')
    ->assertPathIs('/admin/login');
});

test('Author cant access or search on admin user detail page', function () {
    $user = User::factory()->create();
    UserLogin();

    visit('/admin/users?search='.$user->name)
    ->assertSee('403')
    ->assertSee('Forbidden');
});

test('Admin can restore user', function () {
    $user = User::factory()->create(['deleted_at'=>	"2026-07-09 12:25:56"]);
    AdminLogin();

    visit('/admin/users?filters[trashed][value]=0')
        ->assertSee($user->name)
        ->press('Restore')
        ->press('button[wire\:target="callMountedAction"]')
        ->assertDontSee($user->name);

    $this->assertDatabaseHas('users', ['id' => $user->id,'deleted_at' => null]);
});

test('Admin can permanently delete user', function () {
    $user = User::factory()->create(['deleted_at'=>	"2026-07-09 12:25:56"]);
    $user->assignRole(UserRole::AUTHOR);
    AdminLogin();

    visit('/admin/users?filters[trashed][value]=0')
        ->assertSee($user->name)
        ->click('button[wire\:click*="forceDelete"]')
        ->click('button[wire\:target="callMountedAction"]')
        ->assertDontSee($user->name);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('Admin can cancel permanently delete user request', function () {
    $user = User::factory()->create(['deleted_at'=>	"2026-07-09 12:25:56"]);
    $user->assignRole(UserRole::AUTHOR);
    AdminLogin();

    visit('/admin/users?filters[trashed][value]=0')
        ->assertSee($user->name)
        ->click('button[wire\:click*="forceDelete"]')
        ->click('Cancel');

    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

test('Admin can view author profile pages', function () {
    $user = User::factory()->create();
    AdminLogin();

    visit('/admin/users')
        ->assertSee($user->name)
        ->click('View')
        ->assertPathIs('/admin/users/'.$user->uuid)
    ;
});

test('Admin can visite author profile edit page', function () {
    $user = User::factory()->create();
    AdminLogin();

    visit('/admin/users')
        ->assertSee($user->name)
        ->click('Edit')
        ->assertPathIs('/admin/users/'.$user->uuid.'/edit');
});
