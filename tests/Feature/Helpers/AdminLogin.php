<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function AdminLogin(array $permissions = ['user.manage','category.create','category.delete'])
{
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    foreach ($permissions as $permissionName) {
        $permission = Permission::create(['name' => $permissionName]);
        $adminRole->givePermissionTo($permission);
    }
    $admin = User::factory()->create([
        'email' => 'harshrajsinh@gmail.com',
        'password' => 'IAmHarsh',
    ]);
    $admin->assignRole('admin');

    test()->actingAs($admin);

    return $admin;
}
