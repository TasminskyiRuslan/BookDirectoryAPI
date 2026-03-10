<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class MeController extends Controller
{
    /**
     * Retrieve the currently authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        return UserResource::make(auth()->user())
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
