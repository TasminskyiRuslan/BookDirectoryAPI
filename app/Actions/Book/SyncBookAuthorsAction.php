<?php

namespace App\Actions\Book;

use App\Data\Book\Requests\UpdateBookAuthorsData;
use App\Events\Author\AuthorBookRelationsSyncedEvent;
use App\Models\Book;

class SyncBookAuthorsAction
{
    /**
     * Sync the specified book authors.
     *
     * @param UpdateBookAuthorsData $bookAuthorsData
     * @param Book $book
     * @return Book
     */
    public function handle(UpdateBookAuthorsData $bookAuthorsData, Book $book): Book
    {
        $book->authors()->sync($bookAuthorsData->authorIds);
        event(new AuthorBookRelationsSyncedEvent());
        return $book;
    }
}
