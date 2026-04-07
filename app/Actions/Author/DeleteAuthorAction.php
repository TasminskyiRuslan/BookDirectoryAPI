<?php

namespace App\Actions\Author;

use App\Models\Author;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteAuthorAction
{
    /**
     * @param DeleteAuthorImageAction $deleteAuthorImageAction
     */
    public function __construct(
        protected DeleteAuthorImageAction $deleteAuthorImageAction,
    )
    {
    }

    /**
     * Remove the specified author and its image.
     *
     * @param Author $author
     * @return void
     * @throws Throwable
     */
    public function handle(Author $author): void
    {
        DB::transaction(function () use ($author) {
            $this->deleteAuthorImageAction->handle($author);
            $author->delete();
        });
    }
}
