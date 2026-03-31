<?php

namespace App\Actions\Auth;

use App\Models\User;

class RevokeCurrentTokenAction
{
    /**
     * Revokes the current access token for the user.
     *
     * @param User $user
     * @return void
     */
    public function handle(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
