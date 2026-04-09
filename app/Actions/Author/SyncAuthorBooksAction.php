<?php

namespace App\Actions\Author;

use App\Data\Author\Requests\UpdateAuthorBooksData;
use App\Events\Author\AuthorBooksSyncedEvent;
use App\Models\Author;

class SyncAuthorBooksAction
{
    /**
     * Sync the specified author books.
     *
     * @param UpdateAuthorBooksData $authorBooksData
     * @param Author $author
     * @return Author
     */
    public function handle(UpdateAuthorBooksData $authorBooksData, Author $author): Author
    {
        $author->books()->sync($authorBooksData->bookIds);
        event(new AuthorBooksSyncedEvent($author));
        return $author;
    }
}
