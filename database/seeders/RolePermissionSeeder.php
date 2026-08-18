<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'article.create']);
        Permission::firstOrCreate(['name' => 'article.edit']);
        Permission::firstOrCreate(['name' => 'article.delete']);
        Permission::firstOrCreate(['name' => 'article.forceDelete']);
        Permission::firstOrCreate(['name' => 'article.publish']);
        Permission::firstOrCreate(['name' => 'article.restore']);

        Permission::firstOrCreate(['name' => 'category.create']);
        Permission::firstOrCreate(['name' => 'category.edit']);
        Permission::firstOrCreate(['name' => 'category.delete']);

        Permission::firstOrCreate(['name' => 'article.bookmark']);
        Permission::firstOrCreate(['name' => 'article.like']);
        Permission::firstOrCreate(['name' => 'article.comment']);

        Permission::firstOrCreate(['name' => 'user.update']);
        Permission::firstOrCreate(['name' => 'user.delete']);
        Permission::firstOrCreate(['name' => 'user.forceDelete']);
        Permission::firstOrCreate(['name' => 'user.manage']);
        Permission::firstOrCreate(['name' => 'user.restore']);

        // Permission::firstOrCreate(['name' => 'invitation.create']);
        // Permission::firstOrCreate(['name' => 'invitation.resend']);
        // Permission::firstOrCreate(['name' => 'invitation.delete']);

        $author = Role::firstOrCreate(['name' => UserRole::AUTHOR]);
        $admin = Role::firstOrCreate(['name' => UserRole::ADMIN]);
        $superAdmin = Role::firstOrCreate(['name' => UserRole::SUPERADMIN]);

        $author->givePermissionTo([
            'article.create',
            'article.edit',
            'article.delete',
            'article.publish',
            'article.bookmark',
            'article.like',
            'article.comment',
        ]);

        $admin->givePermissionTo([
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
            'user.update',
        ]);

        $superAdmin->givePermissionTo([
            $admin->permissions,
            'category.delete',
            'user.forceDelete',
            'article.forceDelete',
        ]);
    }
}
