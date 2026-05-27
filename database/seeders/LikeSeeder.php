<?php

namespace Database\Seeders;

use App\Models\likes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        likes::factory()->count(14)->create();
    }
}
