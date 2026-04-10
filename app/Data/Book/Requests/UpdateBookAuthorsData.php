<?php

namespace App\Data\Book\Requests;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdateBookAuthorsData extends Data
{
    /**
     * @param array $authorIds
     */
    public function __construct(
        #[MapName('author_ids')]
        public array $authorIds
    ) {}

    /**
     * Return the validation rules.
     *
     * @param ValidationContext $context
     * @return array[]
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'author_ids' => ['required', 'array', 'min:1'],
            'author_ids.*' => ['required', 'integer', 'exists:authors,id'],
        ];
    }
}
