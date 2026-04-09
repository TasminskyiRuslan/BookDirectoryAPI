<?php

namespace App\Actions\Book;

use App\Data\Book\Requests\UpdateBookImageData;
use App\Models\Book;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UpdateBookImageAction
{
    /**
     * Update the specified book image.
     *
     * @param UpdateBookImageData $bookImageData
     * @param Book $book
     * @return Book
     * @throws Throwable
     */
    public function handle(UpdateBookImageData $bookImageData, Book $book): Book
    {
        return DB::transaction(function () use ($bookImageData, $book) {
            try {
                $oldPath = $book->image_path;
                $newPath = $bookImageData->image->store('/', 'books');
                $book->update(['image_path' => $newPath]);
                if ($oldPath) {
                    Storage::disk('books')->delete($oldPath);
                }
                return $book;
            } catch (Exception $e) {
                if (isset($newPath)) {
                    Storage::disk('books')->delete($newPath);
                }
                throw $e;
            }
        });
    }
}
