<?php

namespace App\Http\Controllers\Api\Author;

use App\Actions\Author\CreateAuthorAction;
use App\Actions\Author\UpdateAuthorAction;
use App\Data\Author\Requests\CreateAuthorData;
use App\Data\Author\Requests\UpdateAuthorData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Author\AuthorResource;
use App\Models\Author;
use App\Queries\Author\AuthorListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;
use Throwable;

class AuthorController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/authors',
        description: 'Gets a paginated list of authors with filters and sorting.',
        summary: 'Get a list of authors',
        security: [['sanctum' => []], []],
        tags: ['Authors'],
        parameters: [
            new OA\Parameter(
                name: 'filter[search]',
                description: 'Search by last_name, first_name, patronymic or biography.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort by author fields.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['created_at', '-created_at', 'full_name', '-full_name', 'birth_date', '-birth_date', 'death_date', '-death_date']
                ),
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Author list retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/AuthorResponse')
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User does not have permissions.'
            )
        ]
    )]
    /**
     * Gets a paginated list of authors with filters and sorting.
     *
     * @param AuthorListQuery $authorListQuery
     * @return JsonResponse
     */
    public function index(AuthorListQuery $authorListQuery): JsonResponse
    {
        $this->authorize('view-any', Author::class);
        $authors = Cache::tags(['author'])->remember('authors:' . md5(json_encode(request()->only(['page', 'filter', 'sort']))), config('cache.ttl.authors'), function () use ($authorListQuery) {
            return $authorListQuery->handle();
        });
        return AuthorResource::collection($authors)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Post(
        path: '/authors',
        description: 'Creates a new author.',
        summary: 'Create an author',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateAuthorRequest')
        ),
        tags: ['Authors'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_CREATED,
                description: 'Author created successfully.',
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
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Creates a new author.
     *
     * @param CreateAuthorData $authorData
     * @param CreateAuthorAction $createAuthorAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(CreateAuthorData $authorData, CreateAuthorAction $createAuthorAction): JsonResponse
    {
        $this->authorize('create', Author::class);
        $author = $createAuthorAction->handle($authorData);
        return AuthorResource::make($author)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/authors/{author}',
        description: 'Gets detailed information about a specific author.',
        summary: 'Get author details',
        tags: ['Authors'],
        parameters: [
            new OA\Parameter(
                name: 'author',
                description: 'Author identifier (slug)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Author details retrieved successfully.',
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
                description: 'User not found.'
            )
        ]
    )]
    /**
     * Gets detailed information about a specific author.
     *
     * @param Author $author
     * @return JsonResponse
     */
    public function show(Author $author): JsonResponse
    {
        $this->authorize('view', $author);
        return AuthorResource::make($author)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    /**
     * Updates the specified author.
     *
     * @param UpdateAuthorData $authorData
     * @param Author $author
     * @param UpdateAuthorAction $updateAuthorAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(UpdateAuthorData $authorData, Author $author, UpdateAuthorAction $updateAuthorAction): JsonResponse
    {
        $this->authorize('update', $author);
        $author = $updateAuthorAction->handle($authorData, $author);
        return AuthorResource::make($author->fresh())
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
