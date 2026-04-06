<?php

namespace App\Swagger\Book\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateBookRequest',
    title: 'Update Book Request',
    description: 'Request payload for updating a new book.',
    required: [],
    properties: [
        new OA\Property(
            property: 'title',
            description: 'Title of the book.',
            type: 'string',
            example: 'The Tragical History of Hamlet, Prince of Denmark',
            maxLength: 255
        ),
        new OA\Property(
            property: 'slug',
            description: 'Slug of the book.',
            type: 'string',
            pattern: '^[a-z0-9-]+$',
            example: 'the-tragical-history-of-hamlet-prince-of-denmark',
            maxLength: 255,
        ),
        new OA\Property(
            property: 'description',
            description: 'Description of the book.',
            type: 'string',
            example: 'The story follows Prince Hamlet of Denmark, who returns home to find his father (the King) dead and his uncle, Claudius, newly crowned and married to Hamlet\'s mother, Gertrude. After the Ghost of his father appears and reveals that Claudius murdered him, Hamlet is consumed by a quest for revenge.',
            nullable: true,
            maxLength: 5000,
        ),
        new OA\Property(
            property: 'publication_date',
            description: 'Publication date of the book.',
            type: 'string',
            format: 'date',
            example: '1603-01-01',
            nullable: true
        )
    ],
    type: 'object'
)]
class UpdateBookRequestSchema
{

}
