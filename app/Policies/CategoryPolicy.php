<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('category.create');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermissionTo('category.delete');
    }

    public function remove(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return false;
        }
        if ($target->hasRole(UserRole::SUPERADMIN)) {
            return false;
        }

        return $user->hasPermissionTo('category.delete') && $user->hasRole([UserRole::ADMIN, UserRole::SUPERADMIN]);
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasAnyRole([UserRole::ADMIN, UserRole::SUPERADMIN]);
    }

    public function update(User $user, Category $category): bool
    {
        if ($user->hasRole(UserRole::SUPERADMIN)) {
            return true;
        }

        return $user->can('category.edit') && $user->id == $category->user_id;
    }
}
