<?php

use App\Enums\UserRole;
use App\Models\User;

if (! function_exists('roleUser')) {
    function roleUser(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}