<?php

namespace App\Http\Controllers\Api\Book;

use App\Actions\Book\SyncBookAuthorsAction;
use App\Data\Book\Requests\UpdateBookAuthorsData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Book\BookResource;
use App\Models\Book;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class BookAuthorsController extends Controller
{
    use AuthorizesRequests;

    #[OA\Put(
        path: '/books/{book}/authors',
        description: 'Update the specified book authors.',
        summary: 'Update a book authors',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateBookAuthorsRequest')
        ),
        tags: ['Book'],
        parameters: [
            new OA\Parameter(
                name: 'book',
                description: 'Book identifier (slug)',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'the-tragical-history-of-hamlet-prince-of-denmark'
                ),
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Book authors updated successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/BookResponse'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User does not have permissions.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'Book not found.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Update the specified book authors.
     *
     * @param UpdateBookAuthorsData $bookAuthorsData
     * @param Book $book
     * @param SyncBookAuthorsAction $syncBookAuthorsAction
     * @return JsonResponse
     */
    public function update(UpdateBookAuthorsData $bookAuthorsData, Book $book, SyncBookAuthorsAction $syncBookAuthorsAction): JsonResponse
    {
        $this->authorize('update', $book);
        $book = $syncBookAuthorsAction->handle($bookAuthorsData, $book);
        return BookResource::make($book->loadCount('authors')->loadMissing('authors'))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
