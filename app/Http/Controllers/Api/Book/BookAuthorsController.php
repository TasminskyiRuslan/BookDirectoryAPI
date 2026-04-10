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

class BookAuthorsController extends Controller
{
    use AuthorizesRequests;

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
