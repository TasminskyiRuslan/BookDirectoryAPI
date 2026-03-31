<?php

namespace App\Swagger\Requests\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginUserRequest',
    title: 'Login User Request',
    description: 'Request payload for authenticating a user.',
    required: ['email', 'password'],
    properties: [
        new OA\Property(
            property: 'email',
            description: 'Email address of the user.',
            type: 'string',
            format: 'email',
            example: 'john@example.com',
            maxLength: 255
        ),
        new OA\Property(
            property: 'password',
            description: 'Account password of the user.',
            type: 'string',
            format: 'password',
            example: 'password123'
        ),
    ],
    type: 'object'
)]
class LoginUserRequestSchema
{
}
