<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate([
            'uuid' => Str::uuid(),
            'name' => 'Harshrajsinh',
            'email' => 'harshrajsinh@gmail.com',
            'role' => 'admin',
            'password' => Hash::make('IAmHarsh'),
        ]);
        $user->assignRole('admin');
    }
}
