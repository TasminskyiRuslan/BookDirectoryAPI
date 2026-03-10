<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Data\Auth\Requests\RegisterUserData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\AuthResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class RegisterController extends Controller
{
    /**
     * Register a new user and return authentication data.
     *
     * @param RegisterUserData $userData
     * @param RegisterUserAction $registerUserAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function __invoke(RegisterUserData $userData, RegisterUserAction $registerUserAction): JsonResponse
    {
        $authData = $registerUserAction->handle($userData);
        return AuthResource::make($authData)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }
}
