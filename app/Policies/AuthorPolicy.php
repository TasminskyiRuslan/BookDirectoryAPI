<?php

namespace App\Policies;

use App\Enums\UserPermission;
use App\Models\Author;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AuthorPolicy
{
    /**
     * Determine whether the user can view the list of authors.
     *
     * @param User|null $user
     * @return bool
     */
    public function viewAny(?User $user): bool
    {
        return $user?->hasPermissionTo(UserPermission::AUTHOR_INDEX->value) ?? true;
    }

    /**
     * Determine whether the user can view the specific author's details.
     *
     * @param User $user
     * @param Author $author
     * @return bool
     */
    public function view(User $user, Author $author): bool
    {
        return $user->hasPermissionTo(UserPermission::AUTHOR_SHOW->value);
    }

    /**
     * Determine whether the user can create authors.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(UserPermission::AUTHOR_STORE->value);
    }

    /**
     * Determine whether the user can update the author.
     *
     * @param User $user
     * @param Author $author
     * @return bool
     */
    public function update(User $user, Author $author): bool
    {
        return $user->hasPermissionTo(UserPermission::AUTHOR_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the author.
     *
     * @param User $user
     * @param Author $author
     * @return bool
     */
    public function delete(User $user, Author $author): bool
    {
        return $user->hasPermissionTo(UserPermission::AUTHOR_DESTROY->value);
    }
}
