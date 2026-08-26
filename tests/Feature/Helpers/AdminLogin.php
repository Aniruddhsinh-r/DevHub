<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

if (! function_exists('AdminLogin')) {
    function AdminLogin(array $permissions = ['article.create',
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
        'user.update', ])
    {
        Permission::firstOrCreate(['name' => 'category.delete']);
        Permission::firstOrCreate(['name' => 'article.like']);
        Permission::firstOrCreate(['name' => 'user.forceDelete']);
        Permission::firstOrCreate(['name' => 'article.forceDelete']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            $adminRole->givePermissionTo($permission);
        }
        $admin = User::factory()->create([
            'email' => 'yashbhai@gmail.com',
            'password' => 'IAmYashBhai',
        ]);
        $admin->assignRole(UserRole::ADMIN);

        test()->actingAs($admin);

        return $admin;
    }
}
