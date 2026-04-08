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
use Throwable;

class AuthorImageController extends Controller
{
    use AuthorizesRequests;

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
