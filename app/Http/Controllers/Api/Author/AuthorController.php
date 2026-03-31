<?php

namespace App\Http\Controllers\Api\Author;

use App\Actions\Author\CreateAuthorAction;
use App\Data\Author\Requests\CreateAuthorData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Author\AuthorResource;
use App\Models\Author;
use App\Queries\Author\AuthorListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;
use Throwable;

class AuthorController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/authors',
        description: 'Retrieve a paginated list of authors with filters and sorting.',
        summary: 'Get list of authors',
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
        ]
    )]
    /**
     * Get a paginated list of authors.
     *
     * @param AuthorListQuery $authorListQuery
     * @return JsonResponse
     */
    public function index(AuthorListQuery $authorListQuery)
    {
        $this->authorize('view-any', Author::class);
        $authors = Cache::tags(['author'])->remember('authors:' . md5(json_encode(request()->only(['page', 'filter', 'sort']))), config('cache.ttl.authors'), function () use ($authorListQuery) {
            return $authorListQuery->handle();
        });
        return AuthorResource::collection($authors)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    /**
     * Create a new author.
     *
     * @param CreateAuthorData $authorData
     * @param CreateAuthorAction $createAuthorAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(CreateAuthorData $authorData, CreateAuthorAction $createAuthorAction): JsonResponse
    {
        $this->authorize('create', Author::class);
        return AuthorResource::make($createAuthorAction->handle($authorData))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
