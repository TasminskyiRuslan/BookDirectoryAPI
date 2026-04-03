<?php

namespace App\Observers\Author;

use App\Models\Author;
use Illuminate\Support\Facades\Cache;

class AuthorObserver
{
    /**
     * Flush the cache when a new author is created.
     *
     * @param Author $author
     * @return void
     */
    public function created(Author $author): void
    {
        Cache::tags(['author'])->flush();
    }

    /**
     * Flush the cache when an author is updated.
     *
     * @param Author $author
     * @return void
     */
    public function updated(Author $author): void
    {
        Cache::tags(['author'])->flush();
    }

    /**
     * Flush the cache when an author is deleted.
     *
     * @param Author $author
     * @return void
     */
    public function deleted(Author $author): void
    {
        Cache::tags(['author'])->flush();
    }
}
