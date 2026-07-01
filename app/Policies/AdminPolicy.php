<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class AdminPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user): bool {
        return $user->can('category.create') && $user->hasRole([UserRole::ADMIN, UserRole::SUPERADMIN]);
    }

    public function delete(User $user): bool {
        return $user->can('category.delete') && $user->hasRole([UserRole::ADMIN, UserRole::SUPERADMIN]);
    }

    public function remove(User $user, User $target): bool {
        if ($user->is($target)) {
            return false;
        }
        if ($target->hasRole(UserRole::SUPERADMIN)) {
            return false;
        }
        return $user->can('user.manage') && $user->hasRole([UserRole::ADMIN, UserRole::SUPERADMIN]);
    }

    public function roleChange(User $user): bool {
        return $user->can('user.manage') && $user->hasRole([UserRole::ADMIN, UserRole::SUPERADMIN]); 
    }
}
