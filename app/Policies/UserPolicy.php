<?php

namespace App\Policies;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the list of users.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(UserPermission::USER_INDEX->value);
    }

    /**
     * Determine whether the user can view a specific user's details.
     *
     * @param User $user
     * @param User $target
     * @return bool
     */
    public function view(User $user, User $target): bool
    {
        return $user->hasPermissionTo(UserPermission::USER_SHOW->value);
    }

    /**
     * Determine whether the user can delete the target user.
     *
     * @param User $user
     * @param User $target
     * @return bool
     */
    public function delete(User $user, User $target): bool
    {
        return $user->id !== $target->id && $user->hasPermissionTo(UserPermission::USER_DESTROY->value) && !$target->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value]);
    }

    /**
     * Determine whether the user can update role the target user.
     *
     * @param User $user
     * @param User $target
     * @return bool
     */
    public function updateRole(User $user, User $target): bool
    {
        return $user->id !== $target->id && $user->hasPermissionTo(UserPermission::USER_ROLE_UPDATE->value) && !$target->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value]);
    }
}
