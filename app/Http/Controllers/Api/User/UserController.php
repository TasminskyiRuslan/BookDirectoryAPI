<?php

namespace App\Http\Controllers\Api\User;

use App\Actions\User\DeleteUserAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use App\Queries\UserListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UserController extends Controller
{
    use AuthorizesRequests;

    #[OA\Get(
        path: '/users',
        description: 'Retrieve a paginated list of users with filters and sorting.',
        summary: 'Get list of users',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'filter[search]',
                description: 'Search by name or email.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'filter[role]',
                description: 'Filter by user role.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort by user fields.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['created_at', '-created_at', 'name', '-name']
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
                description: 'User list retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/UserResponse')
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'User does not have permissions.'
            ),
        ]
    )]
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

    #[OA\Get(
        path: '/users/{user}',
        description: 'Retrieve detailed information for a specific user.',
        summary: 'Get user details',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'User identifier (id)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User details retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserResponse'
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
     * Get the details of a specific user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        return UserResource::make($user)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/users/{user}',
        description: 'Permanently remove the specified user from the database.',
        summary: 'Delete user',
        security: [['sanctum' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'User identifier (id)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'User deleted successfully.'
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
     * Delete the specified user.
     *
     * @param User $user
     * @param DeleteUserAction $deleteUserAction
     * @return Response
     */
    public function destroy(User $user, DeleteUserAction $deleteUserAction): Response
    {
        $this->authorize('delete', $user);
        $deleteUserAction->handle($user);
        return response()->noContent();
    }
}
