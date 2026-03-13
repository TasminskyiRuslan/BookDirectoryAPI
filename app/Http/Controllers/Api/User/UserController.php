<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use App\Queries\UserListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get a paginated list of users.
     *
     * @param UserListQuery $userListQuery
     * @return JsonResponse
     */
    public function index(UserListQuery $userListQuery): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        return UserResource::collection($userListQuery->handle())
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

//    /**
//     * Get the details of a specific user.
//     *
//     * @param User $user
//     * @return JsonResponse
//     */
//    public function show(User $user): JsonResponse
//    {
//        //
//    }
//
//    /**
//     * Delete the specified user account.
//     *
//     * @param User $user
//     * @return JsonResponse
//     */
//    public function destroy(User $user): JsonResponse
//    {
//        //
//    }
}
