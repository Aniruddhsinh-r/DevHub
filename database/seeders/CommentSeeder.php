<?php

namespace Database\Seeders;

use App\Models\comments;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        comments::factory()->count(20)->create();
    }
}
