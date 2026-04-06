<?php

namespace App\Actions\Book;

use App\Data\Book\Requests\UpdateBookData;
use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateBookAction
{
    /**
     * Update the specified book.
     *
     * @param UpdateBookData $bookData
     * @param Book $book
     * @return Book
     * @throws Throwable
     */
    public function handle(UpdateBookData $bookData, Book $book): Book
    {
        return DB::transaction(function () use ($bookData, $book) {
            $book->update($bookData->all());
            return $book;
        });
    }
}
