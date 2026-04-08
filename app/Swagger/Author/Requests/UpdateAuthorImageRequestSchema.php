<?php

namespace App\Swagger\Author\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateAuthorImageRequest',
    title: 'Update Author Image Request',
    description: 'Request payload for updating an author image.',
    required: ['image', '_method'],
    properties: [
        new OA\Property(
            property: 'image',
            description: 'Image of the author.',
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
class UpdateAuthorImageRequestSchema
{

}
