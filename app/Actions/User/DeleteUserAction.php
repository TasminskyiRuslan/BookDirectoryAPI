<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteUserAction
{
    /**
     * Delete the specified user.
     *
     * @param User $user
     * @return void
     * @throws Throwable
     */
    public function handle(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->delete();
        });
    }
}
