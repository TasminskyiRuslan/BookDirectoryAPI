<?php

namespace App\Actions\Book;

use App\Data\Book\Requests\CreateBookData;
use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateBookAction
{
    /**
     * Create a new book.
     *
     * @param CreateBookData $bookData
     * @return Book
     * @throws Throwable
     */
    public function handle(CreateBookData $bookData): Book
    {
        return DB::transaction(function () use ($bookData) {
            return Book::create($bookData->all());
        });
    }
}
