<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([UserRole::ADMIN, UserRole::SUPERADMIN, UserRole::AUTHOR]);
    }

    public function view(User $user, User $target): bool
    {
        if ($user->hasRole(UserRole::SUPERADMIN)) {
            return true;
        }

        if ($user->hasRole(UserRole::ADMIN)) {
            return ! $target->hasRole(UserRole::SUPERADMIN);
        }

        if ($user->hasRole(UserRole::AUTHOR)) {
            return $target->hasRole(UserRole::AUTHOR);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([UserRole::ADMIN, UserRole::SUPERADMIN]);
    }

    public function follow(User $user, User $target): bool
    {
        return $user->hasRole(UserRole::AUTHOR) && $target->hasRole(UserRole::AUTHOR) && ! $user->is($target);
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

    public function update(User $user, User $target): bool
    {
        if ($user->hasRole(UserRole::SUPERADMIN)) {
            return $user->is($target) || ! $target->hasRole(UserRole::SUPERADMIN);
        }

        if ($user->hasRole(UserRole::ADMIN)) {
            return ! $target->hasRole(UserRole::SUPERADMIN);
        }

        if ($user->hasRole(UserRole::AUTHOR)) {
            return $user->is($target);
        }

        return false;
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return false;
        }

        if ($target->hasRole(UserRole::SUPERADMIN)) {
            return false;
        }

        if ($user->hasRole(UserRole::SUPERADMIN)) {
            return ! $target->hasRole(UserRole::SUPERADMIN);
        }

        if ($user->hasRole(UserRole::ADMIN)) {
            return ! $target->hasRole(UserRole::SUPERADMIN);
        }

        return false;
    }

    public function restore(User $user, User $target): bool
    {
        return $this->delete($user, $target);
    }

    public function forceDelete(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return false;
        }

        if ($target->hasRole(UserRole::SUPERADMIN)) {
            return false;
        }

        return $user->hasRole([UserRole::ADMIN, UserRole::SUPERADMIN]);
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasAnyRole([UserRole::ADMIN, UserRole::SUPERADMIN]);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasRole(UserRole::SUPERADMIN);
    }
}
