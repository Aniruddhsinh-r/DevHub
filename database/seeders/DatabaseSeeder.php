<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
        CategorySeeder::class,
        RolePermissionSeeder::class,
        UserSeeder::class,
        AdminSeeder::class,
        ArticleSeeder::class,
        CommentSeeder::class,
        LikeSeeder::class,]);
    }
}
