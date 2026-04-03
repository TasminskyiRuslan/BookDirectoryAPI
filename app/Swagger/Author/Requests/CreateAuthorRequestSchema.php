<?php

namespace App\Swagger\Author\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateAuthorRequest',
    title: 'Create Author Request',
    description: 'Request payload for creation a new author.',
    required: ['last_name', 'first_name'],
    properties: [
        new OA\Property(
            property: 'last_name',
            description: 'Lastname of the author.',
            type: 'string',
            example: 'Shevchenko',
            maxLength: 255,
            minLength: 2
        ),
        new OA\Property(
            property: 'first_name',
            description: 'Firstname of the author.',
            type: 'string',
            example: 'Taras',
            maxLength: 255,
            minLength: 2
        ),
        new OA\Property(
            property: 'patronymic',
            description: 'Patronymic of the author.',
            type: 'string',
            example: 'Grigorievich',
            nullable: true,
            maxLength: 255,
            minLength: 2
        ),
        new OA\Property(
            property: 'slug',
            description: 'Slug of the author.',
            type: 'string',
            pattern: '^[a-z0-9-]+$',
            example: 'shevchenko-taras-grigorievich',
            nullable: true,
            maxLength: 255,
        ),
        new OA\Property(
            property: 'birth_date',
            description: 'Birth date of the author.',
            type: 'string',
            format: 'date',
            example: '1814-03-09',
            nullable: true
        ),
        new OA\Property(
            property: 'death_date',
            description: 'Death date of the author.',
            type: 'string',
            format: 'date',
            example: '1861-03-10',
            nullable: true
        ),
        new OA\Property(
            property: 'biography',
            description: 'Biography of the author.',
            type: 'string',
            example: 'Ukrainian poet, artist, and thinker. Born into a family of serfs, he gained his freedom through a ransom. His work became the foundation of modern Ukrainian literature.',
            nullable: true
        )
    ],
    type: 'object'
)]
class CreateAuthorRequestSchema
{

}
