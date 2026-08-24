<?php

use App\Enums\UserRole;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

if (! function_exists('apiGivePermissions')) {
    function apiGivePermissions(Role $role, array $permissions): void
    {
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            $role->givePermissionTo($permission);
        }
    }
}

if (! function_exists('apiActingAsAuthor')) {
    /**
     * Create an Author user, assign given permissions, and authenticate
     * the test client as that user via Sanctum.
     */
    function apiActingAsAuthor(array $permissions = [
        'article.create',
        'article.edit',
        'article.delete',
        'article.publish',
        'article.bookmark',
        'article.like',
        'article.comment',
    ], array $attributes = []): User
    {
        Permission::firstOrCreate(['name' => 'category.delete']);
        Permission::firstOrCreate(['name' => 'category.create']);
        Permission::firstOrCreate(['name' => 'category.list']);
        Permission::firstOrCreate(['name' => 'user.forceDelete']);
        Permission::firstOrCreate(['name' => 'article.forceDelete']);
        Permission::firstOrCreate(['name' => 'article.comment']);
        Permission::firstOrCreate(['name' => 'article.bookmark']);
        Permission::firstOrCreate(['name' => 'user.manage']);
        Permission::firstOrCreate(['name' => 'user.delete']);
        Permission::firstOrCreate(['name' => 'user.update']);
        $role = Role::firstOrCreate(['name' => UserRole::AUTHOR->value, 'guard_name' => 'web']);
        apiGivePermissions($role, $permissions);

        $user = User::factory()->create($attributes);
        $user->assignRole(UserRole::AUTHOR);

        Sanctum::actingAs($user, ['*']);

        return $user;
    }
}

if (! function_exists('apiActingAsAdmin')) {
    function apiActingAsAdmin(array $permissions = [
        'article.create',
        'article.edit',
        'article.delete',
        'article.restore',
        'category.create',
        'category.edit',
        'category.list',
        'category.delete',
        'article.publish',
        'user.manage',
        'user.delete',
        'user.restore',
        'user.update',
        'user.forceDelete',
        'article.forceDelete',
    ], array $attributes = []): User
    {
        Permission::firstOrCreate(['name' => 'category.delete']);
        Permission::firstOrCreate(['name' => 'user.forceDelete']);
        $role = Role::firstOrCreate(['name' => UserRole::ADMIN->value, 'guard_name' => 'web']);
        apiGivePermissions($role, $permissions);

        $user = User::factory()->create($attributes);
        $user->assignRole(UserRole::ADMIN);

        Sanctum::actingAs($user, ['*']);

        return $user;
    }
}

if (! function_exists('apiActingAsSuperAdmin')) {
    function apiActingAsSuperAdmin(array $permissions = [
        'category.delete',
        'user.forceDelete',
        'article.forceDelete',
        'article.create',
        'article.edit',
        'article.delete',
        'article.restore',
        'category.create',
        'category.edit',
        'category.list',
        'article.publish',
        'user.manage',
        'user.delete',
        'user.restore',
        'user.update',
    ], array $attributes = []): User
    {
        $role = Role::firstOrCreate(['name' => UserRole::SUPERADMIN->value, 'guard_name' => 'web']);
        apiGivePermissions($role, $permissions);

        $user = User::factory()->create($attributes);
        $user->assignRole(UserRole::SUPERADMIN);

        Sanctum::actingAs($user, ['*']);

        return $user;
    }
}

if (! function_exists('apiPlainUser')) {
    /**
     * A user with no role/permissions at all (for unauthorized-action checks).
     */
    function apiPlainUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        Sanctum::actingAs($user, ['*']);

        return $user;
    }
}