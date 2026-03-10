<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Data\Auth\Requests\LoginUserData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\AuthResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
class LoginController extends Controller
{
    /**
     * Authenticate a user and return an access token.
     *
     * @param LoginUserData $userData
     * @param LoginUserAction $loginUserAction
     * @return JsonResponse
     */
    public function __invoke(LoginUserData $userData, LoginUserAction $loginUserAction): JsonResponse
    {
        $authData = $loginUserAction->handle($userData);
        return AuthResource::make($authData)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
