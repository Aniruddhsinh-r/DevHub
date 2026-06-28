<?php

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

if (!function_exists('UserLogin')) {
    function UserLogin(array $permissions = ['article.create','article.edit','article.delete','article.publish'])
    {
        $authorRole = Role::firstOrCreate(['name' => UserRole::AUTHOR]);
        foreach ($permissions as $permissionName) {
            $permission = Permission::create(['name' => $permissionName]);
            $authorRole->givePermissionTo($permission);
        }
        $user = User::factory()->create([
            'email' => 'adanirudda@gmail.com',
            'password' => 'rathod1290'
        ]);
        $user->assignRole(UserRole::AUTHOR);

        test()->actingAs($user);

        return $user;
    }
}
