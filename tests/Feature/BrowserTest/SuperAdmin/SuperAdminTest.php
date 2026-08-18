<?php

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../../Helpers/SuperAdminLogin.php';
uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
});

test('SuperAdmin can permanently delete user', function () {
    $user = User::factory()->create(['deleted_at' => '2026-07-09 12:25:56']);
    $user->assignRole(UserRole::AUTHOR);
    SuperAdminLogin();

    visit('/admin/users?filters[trashed][value]=0')
        ->assertSee($user->name)
        ->click('button[wire\:click*="forceDelete"]')
        ->click('button[wire\:target="callMountedAction"]')
        ->assertDontSee($user->name);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('SuperAdmin can cancel permanently delete user request', function () {
    $user = User::factory()->create(['deleted_at' => '2026-07-09 12:25:56']);
    $user->assignRole(UserRole::AUTHOR);
    SuperAdminLogin();

    visit('/admin/users?filters[trashed][value]=0')
        ->assertSee($user->name)
        ->click('button[wire\:click*="forceDelete"]')
        ->click('Cancel');

    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

test('delete category', function () {
    SuperAdminLogin();

    $category = Category::factory()->create();

    visit('/admin/categories')
        ->assertSee($category->name)
        ->click('Delete')
        ->click('button[wire\:target="callMountedAction"]')
        ->assertDontSee($category->name);

    $this->assertDatabaseMissing('categories', [
        'name' => $category->name,
    ]);
});
