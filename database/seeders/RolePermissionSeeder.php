<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'article.create']);
        Permission::firstOrCreate(['name' => 'article.edit']);
        Permission::firstOrCreate(['name' => 'article.delete']);
        Permission::firstOrCreate(['name' => 'article.publish']);

        Permission::firstOrCreate(['name' => 'category.create']);
        Permission::firstOrCreate(['name' => 'category.delete']);

        Permission::firstOrCreate(['name' => 'user.manage']);

        $author = Role::firstOrCreate(['name' => UserRole::AUTHOR]);
        $admin = Role::firstOrCreate(['name' => UserRole::ADMIN]);
        $superAdmin = Role::firstOrCreate(['name' => UserRole::SUPERADMIN]);

        $author->givePermissionTo([
            'article.create',
            'article.edit',
            'article.delete',
            'article.publish',
        ]);

        $admin->givePermissionTo([
            'article.create',
            'article.edit',
            'article.delete',
            'category.create',
            'category.delete',
            'user.manage',
        ]);

        $superAdmin->givePermissionTo($admin->permissions);
    }
}
