<?php

namespace App\Swagger\Book\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateBookAuthorsRequest',
    title: 'Update Book Authors Request',
    description: 'Request payload for updating a book authors.',
    required: ['author_ids'],
    properties: [
        new OA\Property(
            property: 'author_ids',
            description: 'Authors of the book.',
            type: 'array',
            items: new OA\Items(type: 'integer'),
            example: [1, 2, 3]
        )
    ],
    type: 'object'
)]
class UpdateBookAuthorsRequestSchema
{

}
