<?php

namespace App\Actions\Author;

use App\Data\Author\Requests\UpdateAuthorImageData;
use App\Models\Author;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UpdateAuthorImageAction
{
    /**
     * Update the specified author image.
     *
     * @param UpdateAuthorImageData $authorImageData
     * @param Author $author
     * @return Author
     * @throws Throwable
     */
    public function handle(UpdateAuthorImageData $authorImageData, Author $author): Author
    {
        return DB::transaction(function () use ($authorImageData, $author) {
            try {
                $oldPath = $author->image_path;
                $newPath = $authorImageData->image->store('/', 'authors');
                $author->update(['image_path' => $newPath]);
                if ($oldPath) {
                    Storage::disk('authors')->delete($oldPath);
                }
                return $author;
            } catch (Exception $e) {
                if (isset($newPath)) {
                    Storage::disk('authors')->delete($newPath);
                }
                throw $e;
            }
        });
    }
}
