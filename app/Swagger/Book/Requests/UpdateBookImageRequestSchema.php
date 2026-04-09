<?php

namespace App\Swagger\Book\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateBookImageRequest',
    title: 'Update Book Image Request',
    description: 'Request payload for updating a book image.',
    required: ['image', '_method'],
    properties: [
        new OA\Property(
            property: 'image',
            description: 'Image of the book.',
            type: 'string',
            format: 'binary'
        ),
        new OA\Property(
            property: '_method',
            description: 'Method spoofing to treat POST as PUT.',
            type: 'string',
            example: 'PUT',
            enum: ['PUT'],
        )
    ],
    type: 'object'
)]
class UpdateBookImageRequestSchema
{

}
