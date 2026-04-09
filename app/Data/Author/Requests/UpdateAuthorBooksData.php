<?php

namespace App\Data\Author\Requests;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdateAuthorBooksData extends Data
{
    /**
     * @param array $bookIds
     */
    public function __construct(
        #[MapName('book_ids')]
        public array $bookIds
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'book_ids' => ['required', 'array', 'min:1'],
            'book_ids.*' => ['required', 'integer', 'exists:books,id'],
        ];
    }
}
