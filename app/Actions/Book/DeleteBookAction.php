<?php

namespace App\Actions\Book;

use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteBookAction
{
    /**
     * @param DeleteBookImageAction $deleteBookImageAction
     */
    public function __construct(
        protected DeleteBookImageAction $deleteBookImageAction,
    )
    {
    }

    /**
     * Remove the specified book and its image.
     *
     * @param Book $book
     * @return void
     * @throws Throwable
     */
    public function handle(Book $book): void
    {
        DB::transaction(function () use ($book) {
            $this->deleteBookImageAction->handle($book);
            $book->delete();
        });
    }
}
