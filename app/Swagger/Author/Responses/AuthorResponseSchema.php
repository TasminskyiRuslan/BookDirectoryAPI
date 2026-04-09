<?php

namespace App\Swagger\Author\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthorResponse',
    title: 'Author Response',
    description: 'Data of a specific author.',
    required: ['id', 'last_name', 'first_name', 'slug'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'Unique identifier of the author.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'last_name',
            description: 'Lastname of the author.',
            type: 'string',
            example: 'Shevchenko'
        ),
        new OA\Property(
            property: 'first_name',
            description: 'Firstname of the author.',
            type: 'string',
            example: 'Taras'
        ),
        new OA\Property(
            property: 'patronymic',
            description: 'Patronymic of the author.',
            type: 'string',
            example: 'Grigorievich',
            nullable: true
        ),
        new OA\Property(
            property: 'slug',
            description: 'Slug of the author.',
            type: 'string',
            example: 'shevchenko-taras-grigorievich',
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
        ),
        new OA\Property(
            property: 'image_url',
            description: 'Image url of the author.',
            type: 'string',
            format: 'uri',
            example: 'http://loclhost:8080/storage/authors/author1.png',
            nullable: true
        ),
        new OA\Property(
            property: 'books_count',
            description: 'Total number of books.',
            type: 'integer',
            example: 1,
            nullable: true
        ),
        new OA\Property(
            property: 'books',
            description: 'List of books.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/BookResponse'),
            nullable: true
        )
    ],
    type: 'object'
)]
class AuthorResponseSchema
{
}
