<?php

namespace App\Http\Controllers\Api\Author;

use App\Http\Controllers\Controller;
use App\Http\Resources\Author\AuthorResource;
use App\Models\Author;
use App\Queries\Author\AuthorListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AuthorController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get a paginated list of authors.
     *
     * @param AuthorListQuery $authorListQuery
     * @return JsonResponse
     */
    public function index(AuthorListQuery $authorListQuery)
    {
        $this->authorize('view-any', Author::class);
        return AuthorResource::collection($authorListQuery->handle())
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
