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

class AuthorBooksController extends Controller
{
    use AuthorizesRequests;

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
        return AuthorResource::make($author->loadMissing('books'))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
