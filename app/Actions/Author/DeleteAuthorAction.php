<?php

namespace App\Actions\Author;

use App\Models\Author;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteAuthorAction
{
    /**
     * Remove the specified author.
     *
     * @param Author $author
     * @return void
     * @throws Throwable
     */
    public function handle(Author $author): void
    {
        DB::transaction(function () use ($author) {
            $author->delete();
        });
    }
}
