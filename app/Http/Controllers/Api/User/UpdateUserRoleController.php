<?php

namespace App\Http\Controllers\Api\User;

use App\Actions\User\UpdateUserRoleAction;
use App\Data\User\Requests\UpdateUserRoleData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class UpdateUserRoleController extends Controller
{
    use AuthorizesRequests;

    #[OA\Put(
        path: '/users/{user}/role',
        description: 'Updates the role of the specified user.',
        summary: 'Updates user role',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserRoleRequest')
        ),
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
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            )
        ]
    )]
    /**
     * Updates the role of the specified user.
     *
     * @param UpdateUserRoleData $userRoleData
     * @param User $user
     * @param UpdateUserRoleAction $updateUserRoleAction
     * @return JsonResponse
     */
    public function __invoke(UpdateUserRoleData $userRoleData, User $user, UpdateUserRoleAction $updateUserRoleAction): JsonResponse
    {
        $this->authorize('update-role', $user);
        $updateUserRoleAction->handle($userRoleData, $user);
        return UserResource::make($user->fresh('roles'))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
