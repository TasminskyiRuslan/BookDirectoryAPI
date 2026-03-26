<?php

namespace App\Actions\User;

use App\Data\User\Requests\UpdateUserRoleData;
use App\Models\User;

class UpdateUserRoleAction
{
    /**
     * Update the role for the specified user.
     *
     * @param UpdateUserRoleData $userRoleData
     * @param User $user
     * @return void
     */
    public function handle(UpdateUserRoleData $userRoleData, User $user): void
    {
        $user->syncRoles([$userRoleData->role->value]);
    }
}
