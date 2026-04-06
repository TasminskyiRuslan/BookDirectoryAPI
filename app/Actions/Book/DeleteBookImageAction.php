<?php

namespace App\Actions\Book;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;

class DeleteBookImageAction
{
    /**
     * Remove the specified book image.
     *
     * @param Book $book
     * @return void
     */
    public function handle(Book $book): void
    {
        if ($book->image_path) {
            Storage::disk('books')->delete($book->image_path);
            $book->removeImage()->save();
        }
    }
}
