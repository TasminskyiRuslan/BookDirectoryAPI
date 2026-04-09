<?php

namespace App\Swagger\Author\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateAuthorBooksRequest',
    title: 'Update Author Books Request',
    description: 'Request payload for updating an author books.',
    required: ['book_ids'],
    properties: [
        new OA\Property(
            property: 'book_ids',
            description: 'Books of the author.',
            type: 'array',
            items: new OA\Items(type: 'integer'),
            example: [1, 2, 3]
        )
    ],
    type: 'object'
)]
class UpdateAuthorBooksRequestSchema
{

}
