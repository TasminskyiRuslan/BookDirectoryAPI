<?php

namespace App\Actions\Author;

use App\Models\Author;
use Illuminate\Support\Facades\Storage;

class DeleteAuthorImageAction
{
    /**
     * Remove the specified author image.
     *
     * @param Author $author
     * @return void
     */
    public function handle(Author $author): void
    {
        if ($author->image_path) {
            Storage::disk('authors')->delete($author->image_path);
            $author->removeImage()->save();
        }
    }
}
