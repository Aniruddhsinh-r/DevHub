<?php

namespace Database\Seeders;

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

        $author = Role::firstOrCreate(['name' => 'author']);
        $admin = Role::firstOrCreate(['name' => 'admin']);

        $author->givePermissionTo([
            'article.create',
            'article.edit',
            'article.delete',
            'article.publish',
        ]);

        $admin->givePermissionTo([
            'category.create',
            'category.delete',
            'user.manage',
        ]);
    }
}
