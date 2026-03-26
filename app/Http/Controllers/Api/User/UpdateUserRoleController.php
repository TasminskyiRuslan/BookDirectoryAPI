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

class UpdateUserRoleController extends Controller
{
    use AuthorizesRequests;

    /**
     * Update the role of a specific user.
     *
     * @param UpdateUserRoleData $userRoleData
     * @param User $user
     * @param UpdateUserRoleAction $updateUserRoleAction
     * @return JsonResponse
     */
    public function __invoke(UpdateUserRoleData $userRoleData, User $user, UpdateUserRoleAction $updateUserRoleAction): JsonResponse
    {
        $this->authorize('updateRole', $user);
        $updateUserRoleAction->handle($userRoleData, $user);
        return UserResource::make($user->fresh('roles'))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
