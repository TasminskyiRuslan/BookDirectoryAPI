<?php

namespace App\Actions\Author;

use App\Data\Author\Requests\UpdateAuthorData;
use App\Models\Author;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateAuthorAction
{
    /**
     * Updates the specified author.
     *
     * @param UpdateAuthorData $authorData
     * @param Author $author
     * @return Author
     * @throws Throwable
     */
    public function handle(UpdateAuthorData $authorData, Author $author): Author
    {
        return DB::transaction(function () use ($authorData, $author) {
            $author->update($authorData->toArray());
            return $author;
        });
    }
}
