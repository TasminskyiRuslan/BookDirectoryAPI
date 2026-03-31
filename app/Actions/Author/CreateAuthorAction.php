<?php

namespace App\Actions\Author;

use App\Data\Author\Requests\CreateAuthorData;
use App\Models\Author;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateAuthorAction
{
    /**
     * Create a new author.
     *
     * @param CreateAuthorData $authorData
     * @return Author
     * @throws Throwable
     */
    public function handle(CreateAuthorData $authorData): Author
    {
        return DB::transaction(function () use ($authorData) {
            return Author::create($authorData->all());
        });
    }
}
