<?php

namespace App\Observers\Book;

use App\Models\Book;
use Illuminate\Support\Facades\Cache;

class BookObserver
{
    /**
     * Flush the cache when a new book is created.
     *
     * @param Book $book
     * @return void
     */
    public function created(Book $book): void
    {
        Cache::tags(['book'])->flush();
    }

    /**
     * Flush the cache when a book is updated.
     *
     * @param Book $book
     * @return void
     */
    public function updated(Book $book): void
    {
        Cache::tags(['book', 'author'])->flush();
    }

    /**
     * Flush the cache when a book is deleted.
     *
     * @param Book $book
     * @return void
     */
    public function deleted(Book $book): void
    {
        Cache::tags(['book', 'author'])->flush();
    }
}
