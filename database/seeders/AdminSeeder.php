<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate([
            ['email' => 'harshrajsinh@gmail.com'],
            ['name' => 'Harshrajsinh',
            'role' => 'admin',
            'password' => Hash::make('IAmHarsh')],
        ]);
    }
}
