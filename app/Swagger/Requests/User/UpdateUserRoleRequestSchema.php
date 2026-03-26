<?php

namespace App\Swagger\Requests\User;

use App\Enums\UserRole;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateUserRoleRequest',
    title: 'Update User Role Request',
    description: 'Request payload for updating of the specified user.',
    required: ['role'],
    properties: [
        new OA\Property(
            property: 'role',
            description: 'Role of the user.',
            type: 'string',
            example: UserRole::EDITOR->value,
            enum: [
                UserRole::VIEWER->value,
                UserRole::EDITOR->value,
                UserRole::ADMIN->value,
            ]
        )
    ],
    type: 'object'
)]
class UpdateUserRoleRequestSchema
{

}
