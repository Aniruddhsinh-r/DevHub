<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([UserRole::ADMIN,UserRole::SUPERADMIN,]);
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

    public function view(User $user, Category $category): bool
    {
        return $user->hasAnyRole([UserRole::ADMIN,UserRole::SUPERADMIN,]);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can('category.create') && $user->hasAnyRole([UserRole::ADMIN,UserRole::SUPERADMIN,]);
    }
}