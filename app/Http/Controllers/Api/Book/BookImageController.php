<?php

namespace App\Http\Controllers\Api\Book;

use App\Actions\Book\UpdateBookImageAction;
use App\Data\Book\Requests\UpdateBookImageData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Book\BookResource;
use App\Models\Book;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class BookImageController extends Controller
{
    use AuthorizesRequests;

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
        return BookResource::make($book->loadMissing('authors')->loadCount('authors'))
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
