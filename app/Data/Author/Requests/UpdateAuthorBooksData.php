<?php

namespace App\Data\Author\Requests;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UpdateAuthorBooksData extends Data
{
    /**
     * @param array $bookIds
     */
    public function __construct(
        #[Required]
        #[ArrayType('integer')]
        #[Min(1)]
        #[Exists(table: 'books', column: 'id')]
        #[MapName('book_ids')]
        public array $bookIds
    ) {}
}
