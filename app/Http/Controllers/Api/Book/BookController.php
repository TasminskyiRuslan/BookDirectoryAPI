<?php

namespace App\Http\Controllers\Api\Book;

use App\Actions\Book\CreateBookAction;
use App\Actions\Book\DeleteBookAction;
use App\Actions\Book\UpdateBookAction;
use App\Data\Book\Requests\CreateBookData;
use App\Data\Book\Requests\UpdateBookData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Book\BookResource;
use App\Models\Book;
use App\Queries\Book\BookListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;
use Throwable;

class BookController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/books',
        description: 'Retrieve a paginated list of books with filters and sorting.',
        summary: 'Retrieve a list of books',
        security: [['sanctum' => []], []],
        tags: ['Book'],
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
            new OA\Parameter(
                name: 'include',
                description: 'Relations to include.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'authors_count,authors'
                ),
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
     * Retrieve a paginated list of authors with filters and sorting.
     *
     * @param BookListQuery $bookListQuery
     * @return JsonResponse
     */
    public function index(BookListQuery $bookListQuery): JsonResponse
    {
        $this->authorize('view-any', Book::class);
        $books = Cache::tags(['book'])->remember('books:' . http_build_query(request()->only('page', 'filter', 'sort', 'include')), config('cache.ttl.books'), function () use ($bookListQuery) {
            return $bookListQuery->get();
        });
        return BookResource::collection($books)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Post(
        path: '/books',
        description: 'Create a new book.',
        summary: 'Create a book',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateBookRequest')
        ),
        tags: ['Book'],
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
     * Create a new book.
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
         return BookResource::make($book->loadCount('authors')->loadMissing('authors'))
             ->response()
             ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/books/{book}',
        description: 'Retrieve detailed information about a specific book.',
        summary: 'Retrieve book details',
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
                description: 'Book details retrieved successfully.',
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
            )
        ]
    )]
    /**
     * Retrieve detailed information about a specific book.
     *
     * @param Book $book
     * @return JsonResponse
     */
    public function show(Book $book): JsonResponse
    {
        $this->authorize('view', $book);
        return BookResource::make($book->loadCount('authors')->loadMissing('authors'))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Patch(
        path: '/books/{book}',
        description: 'Update the specified book.',
        summary: 'Update a book',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateBookRequest')
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
                description: 'Book updated successfully.',
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
     * Update the specified book.
     *
     * @param UpdateBookData $bookData
     * @param Book $book
     * @param UpdateBookAction $updateBookAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(UpdateBookData $bookData, Book $book, UpdateBookAction $updateBookAction): JsonResponse
    {
        $this->authorize('update', $book);
        $book = $updateBookAction->handle($bookData, $book);
        return BookResource::make($book->loadCount('authors')->loadMissing('authors'))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/books/{book}',
        description: 'Remove the specified book.',
        summary: 'Remove a book',
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
                description: 'Book deleted successfully.'
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
     * Remove the specified book.
     *
     * @param Book $book
     * @param DeleteBookAction $deleteBookAction
     * @return Response
     * @throws Throwable
     */
    public function destroy(Book $book, DeleteBookAction $deleteBookAction): Response
    {
        $this->authorize('delete', $book);
        $deleteBookAction->handle($book);
        return response()->noContent();
    }
}
