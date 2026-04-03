<?php

namespace App\Policies;

use App\Enums\UserPermission;
use App\Models\Book;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookPolicy
{
    /**
     * Determine whether the user can view the list of books.
     */
    public function viewAny(?User $user): bool
    {
        return $user?->hasPermissionTo(UserPermission::BOOK_INDEX) ?? true;
    }

    /**
     * Determine whether the user can view the specific book's details.
     */
    public function view(?User $user, Book $book): bool
    {
        return $user?->hasPermissionTo(UserPermission::BOOK_SHOW) ?? true;
    }

    /**
     * Determine whether the user can create books.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(UserPermission::BOOK_STORE->value);
    }

    /**
     * Determine whether the user can update the book.
     */
    public function update(User $user, Book $book): bool
    {
        return $user->hasPermissionTo(UserPermission::BOOK_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the book.
     */
    public function delete(User $user, Book $book): bool
    {
        return $user->hasPermissionTo(UserPermission::BOOK_DESTROY->value);
    }
}
