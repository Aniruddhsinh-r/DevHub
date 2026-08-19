<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Policies\CategoryPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../Feature/Helpers/RoleUser.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new CategoryPolicy;

    foreach (['category.create', 'category.edit', 'category.delete'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::SUPERADMIN, 'guard_name' => 'web']);
});

test('a user without category.create permission cannot create a category', function () {
    $author = roleUser(UserRole::AUTHOR);

    expect($this->policy->create($author))->toBeFalse();
});

test('a user with category.create permission can create a category', function () {
    $admin = roleUser(UserRole::ADMIN);
    $admin->givePermissionTo('category.create');

    expect($this->policy->create($admin))->toBeTrue();
});

test('only users with category.delete permission can delete a category', function () {
    $admin = roleUser(UserRole::ADMIN);

    expect($this->policy->delete($admin))->toBeFalse();

    $admin->givePermissionTo('category.delete');

    expect($this->policy->delete($admin))->toBeTrue();
});

test('a superadmin can update any category', function () {
    $superAdmin = roleUser(UserRole::SUPERADMIN);
    $category = Category::factory()->create();

    expect($this->policy->update($superAdmin, $category))->toBeTrue();
});

test('an admin without category.edit permission cannot update a category', function () {
    $admin = roleUser(UserRole::ADMIN);
    $category = Category::factory()->create(['user_id' => $admin->id]);

    expect($this->policy->update($admin, $category))->toBeFalse();
});

test('a user cannot remove themself via the category policy either', function () {
    $admin = roleUser(UserRole::ADMIN);
    $admin->givePermissionTo('category.delete');

    expect($this->policy->remove($admin, $admin))->toBeFalse();
});

test('a superadmin cannot be removed even by another admin with permission', function () {
    $admin = roleUser(UserRole::ADMIN);
    $admin->givePermissionTo('category.delete');
    $superAdmin = roleUser(UserRole::SUPERADMIN);

    expect($this->policy->remove($admin, $superAdmin))->toBeFalse();
});

test('an author cannot remove another author even with the permission, because role check requires admin', function () {
    $author = roleUser(UserRole::AUTHOR);
    $author->givePermissionTo('category.delete');
    $otherAuthor = roleUser(UserRole::AUTHOR);

    expect($this->policy->remove($author, $otherAuthor))->toBeFalse();
});
