<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Enums\UserRole;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'haribhai@gmail.com'],
            ['uuid' => Str::uuid(),
            'name' => 'Hari bhai',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole(UserRole::ADMIN);
        $user2 = User::updateOrCreate(
            ['email' => 'adanirudda@gmail.com'],
            ['uuid' => Str::uuid(),
            'name' => 'Aniruddhsinh rathod',
            'password' => Hash::make('password'),
        ]);
        $user2->assignRole(UserRole::ADMIN);
    }
}
