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
            ['email' => 'harshrajsinh@gmail.com'],
            ['uuid' => Str::uuid(),
            'name' => 'Harshrajsinh',
            'password' => Hash::make('IAmHarsh'),
        ]);
        $user->assignRole(UserRole::ADMIN);
    }
}
