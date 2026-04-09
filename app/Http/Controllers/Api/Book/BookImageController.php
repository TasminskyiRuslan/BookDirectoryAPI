<?php

namespace App\Http\Controllers\Api\Book;

use App\Actions\Book\DeleteBookImageAction;
use App\Actions\Book\UpdateBookImageAction;
use App\Data\Book\Requests\UpdateBookImageData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Book\BookResource;
use App\Models\Book;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;
use Throwable;

class BookImageController extends Controller
{
    use AuthorizesRequests;

    #[OA\Post(
        path: '/books/{book}/image',
        description: 'Update the specified book image.',
        summary: 'Update a book image',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/UpdateBookImageRequest')
            )
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
                description: 'Book image updated successfully.',
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
     * Update the specified book image.
     *
     * @param UpdateBookImageData $bookImageData
     * @param Book $book
     * @param UpdateBookImageAction $updateBookImageAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(UpdateBookImageData $bookImageData, Book $book, UpdateBookImageAction $updateBookImageAction): JsonResponse
    {
        $this->authorize('update', $book);
        $book = $updateBookImageAction->handle($bookImageData, $book);
        return BookResource::make($book->loadCount('authors')->loadMissing('authors'))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/books/{book}/image',
        description: 'Remove the specified book image.',
        summary: 'Remove a book image',
        security: [['sanctum' => []]],
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
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Book image deleted successfully.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User does not have permissions.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'Book not found.'
            )
        ]
    )]
    /**
     * Remove the specified book image.
     *
     * @param Book $book
     * @param DeleteBookImageAction $deleteBookImageAction
     * @return Response
     */
    public function destroy(Book $book, DeleteBookImageAction $deleteBookImageAction): Response
    {
        $this->authorize('update', $book);
        $deleteBookImageAction->handle($book);
        return response()->noContent();
    }
}
