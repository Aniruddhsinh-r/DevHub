<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

if (! function_exists('AdminLogin')) {
    function AdminLogin(array $permissions = ['user.manage', 'category.create', 'category.delete'])
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        foreach ($permissions as $permissionName) {
            $permission = Permission::create(['name' => $permissionName]);
            $adminRole->givePermissionTo($permission);
        }
        $admin = User::factory()->create([
            'email' => 'harshrajsinh@gmail.com',
            'password' => 'IAmHarshrajsinh',
        ]);
        $admin->assignRole(UserRole::ADMIN);

        test()->actingAs($admin);

        return $admin;
    }
}
