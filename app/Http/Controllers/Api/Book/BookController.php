<?php

namespace App\Http\Controllers\Api\Book;

use App\Actions\Book\CreateBookAction;
use App\Data\Book\Requests\CreateBookData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Book\BookResource;
use App\Models\Book;
use App\Queries\Book\BookListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;
use Throwable;

class BookController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/books',
        description: 'Gets a paginated list of books with filters and sorting.',
        summary: 'Get a list of books',
        security: [['sanctum' => []], []],
        tags: ['Books'],
        parameters: [
            new OA\Parameter(
                name: 'filter[search]',
                description: 'Search by title, description, authors.last_name, authors.first_name or authors.patronymic.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'filter[author]',
                description: 'Filter by authors slug.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort by book fields.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['created_at', '-created_at', 'title', '-title', 'publication_date', '-publication_date']
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
                description: 'Book list retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/BookResponse')
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
     * @param BookListQuery $bookListQuery
     * @return JsonResponse
     */
    public function index(BookListQuery $bookListQuery): JsonResponse
    {
        $this->authorize('view-any', Book::class);
        $books = Cache::tags(['book'])->remember('books:' . http_build_query(request()->only('page', 'filter', 'sort')), config('cache.ttl.books'), function () use ($bookListQuery) {
            return $bookListQuery->get();
        });
        return BookResource::collection($books)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Post(
        path: '/books',
        description: 'Creates a new book.',
        summary: 'Book an author',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateBookRequest')
        ),
        tags: ['Books'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_CREATED,
                description: 'Book created successfully.',
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
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Creates a new book.
     *
     * @param CreateBookData $bookData
     * @param CreateBookAction $createBookAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(CreateBookData $bookData, CreateBookAction $createBookAction): JsonResponse
    {
         $this->authorize('create', Book::class);
         $book = $createBookAction->handle($bookData);
         return BookResource::make($book)
             ->response()
             ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    /**
     * Gets detailed information about a specific book.
     *
     * @param Book $book
     * @return JsonResponse
     */
    public function show(Book $book): JsonResponse
    {
        $this->authorize('view', $book);
        return BookResource::make($book)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
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
