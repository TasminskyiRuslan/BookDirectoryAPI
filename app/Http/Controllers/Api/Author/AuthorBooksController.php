<?php

namespace App\Http\Controllers\Api\Author;

use App\Actions\Author\SyncAuthorBooksAction;
use App\Data\Author\Requests\UpdateAuthorBooksData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Author\AuthorResource;
use App\Models\Author;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class AuthorBooksController extends Controller
{
    use AuthorizesRequests;

    #[OA\Put(
        path: '/authors/{author}/books',
        description: 'Update the specified author books.',
        summary: 'Update an author books',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateAuthorBooksRequest')
        ),
        tags: ['Author'],
        parameters: [
            new OA\Parameter(
                name: 'author',
                description: 'Author identifier (slug)',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'shevchenko-taras-grigorievich'
                ),
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Author books updated successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AuthorResponse'
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
                description: 'Author not found.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Update the specified author books.
     *
     * @param UpdateAuthorBooksData $authorBooksData
     * @param Author $author
     * @param SyncAuthorBooksAction $syncAuthorBooksAction
     * @return JsonResponse
     */
    public function update(UpdateAuthorBooksData $authorBooksData, Author $author, SyncAuthorBooksAction $syncAuthorBooksAction): JsonResponse
    {
        $this->authorize('update', $author);
        $author = $syncAuthorBooksAction->handle($authorBooksData, $author);
        return AuthorResource::make($author->loadCount('books')->loadMissing('books'))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
