<?php

namespace App\Swagger\Book\Responses;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BookResponse',
    title: 'Book Response',
    description: 'Data of a specific book.',
    required: ['id', 'title', 'slug'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'Unique identifier of the book.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'title',
            description: 'Title of the book.',
            type: 'string',
            example: 'The Tragical History of Hamlet, Prince of Denmark'
        ),
        new OA\Property(
            property: 'slug',
            description: 'Slug of the book.',
            type: 'string',
            example: 'the-tragical-history-of-hamlet-prince-of-denmark',
        ),
        new OA\Property(
            property: 'description',
            description: 'Description of the book.',
            type: 'string',
            example: 'The story follows Prince Hamlet of Denmark, who returns home to find his father (the King) dead and his uncle, Claudius, newly crowned and married to Hamlet\'s mother, Gertrude. After the Ghost of his father appears and reveals that Claudius murdered him, Hamlet is consumed by a quest for revenge.',
            nullable: true
        ),
        new OA\Property(
            property: 'image_url',
            description: 'Image url of the book.',
            type: 'string',
            format: 'url',
            example: 'http://loclhost:8080/storage/books/book1.png',
            nullable: true
        ),
        new OA\Property(
            property: 'publication_date',
            description: 'Publication date of the book.',
            type: 'string',
            format: 'date',
            example: '1603-01-01',
            nullable: true
        ),
        new OA\Property(
            property: 'authors',
            description: 'Authors of the book.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/AuthorResponse'),
            nullable: true
        )
    ],
    type: 'object'
)]
class BookResponseSchema
{

}
