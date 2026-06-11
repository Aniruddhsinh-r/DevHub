<?php

namespace App\Policies;

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
        return $user->can('category.create');
    }

    public function delete(User $user): bool {
        return $user->can('category.delete');
    }

    public function remove(User $user): bool {
        return $user->can('user.manage') && $user->hasRole('admin');
    }
}
