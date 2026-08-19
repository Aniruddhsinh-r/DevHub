<?php

use App\Enums\UserRole;
use App\Models\User;

if (! function_exists('authorWithPermissions')) {
    function authorWithPermissions(array $permissions = []): User
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::AUTHOR);
        $user->givePermissionTo($permissions);

        return $user;
    }
}
