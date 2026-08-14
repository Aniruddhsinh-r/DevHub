<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            CategorySeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            AdminSeeder::class,
            ArticleSeeder::class,
            CommentSeeder::class,
            LikeSeeder::class,
            SuperAdminSeeder::class]);
    }
}
