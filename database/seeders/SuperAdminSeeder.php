<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Enums\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
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
            'password' => Hash::make('Password'),
        ]);
        $user->assignRole(UserRole::SUPERADMIN);
    }
}
