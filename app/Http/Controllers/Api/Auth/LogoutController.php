<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RevokeCurrentTokenAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LogoutController extends Controller
{
    /**
     * Logout the authenticated user by revoking the current access token.
     *
     * @param Request $request
     * @param RevokeCurrentTokenAction $revokeCurrentTokenAction
     * @return Response
     */
    public function __invoke(Request $request, RevokeCurrentTokenAction $revokeCurrentTokenAction): Response
    {
        $revokeCurrentTokenAction->handle(auth()->user());
        return response()->noContent();
    }
}
