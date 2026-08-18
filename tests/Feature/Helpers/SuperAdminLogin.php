<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

if (! function_exists('SuperAdminLogin')) {
    function SuperAdminLogin(array $permissions = ['category.delete',
            'user.forceDelete',
            'article.forceDelete',
            'article.create',
            'article.edit',
            'article.delete',
            'article.restore',
            'category.create',
            'category.edit',
            'article.publish',
            'user.manage',
            'user.delete',
            'user.restore',
            'user.update',])
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin']);
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName,'guard_name' => 'web',]);
            $superAdminRole->givePermissionTo($permission);
        }
        $superAdmin = User::factory()->create([
            'email' => 'harshrajsinh@gmail.com',
            'password' => 'IAmHarshrajsinh',
        ]);
        $superAdmin->assignRole(UserRole::SUPERADMIN);

        test()->actingAs($superAdmin);
        return $superAdmin;
    }
}
