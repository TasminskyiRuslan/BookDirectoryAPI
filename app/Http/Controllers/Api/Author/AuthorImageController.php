<?php

namespace App\Http\Controllers\Api\Author;

use App\Actions\Author\UpdateAuthorImageAction;
use App\Data\Author\Requests\UpdateAuthorImageData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Author\AuthorResource;
use App\Models\Author;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;
use Throwable;

class AuthorImageController extends Controller
{
    use AuthorizesRequests;

    #[OA\Post(
        path: '/authors/{author}/image',
        description: 'Update the specified author image.',
        summary: 'Update an author image',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/UpdateAuthorImageRequest')
            )
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
                description: 'Author image updated successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AuthorFullResponse'
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
     * Update the specified author image.
     *
     * @param UpdateAuthorImageData $authorImageData
     * @param Author $author
     * @param UpdateAuthorImageAction $updateAuthorImageAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(UpdateAuthorImageData $authorImageData, Author $author, UpdateAuthorImageAction $updateAuthorImageAction): JsonResponse
    {
        $this->authorize('update', $author);
        $author = $updateAuthorImageAction->handle($authorImageData, $author);
        return AuthorResource::make($author->loadMissing('books')->loadCount('books'))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
