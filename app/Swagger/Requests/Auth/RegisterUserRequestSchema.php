<?php

namespace App\Swagger\Requests\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterUserRequest',
    title: 'Register User Request',
    description: 'Request payload for registering a new user.',
    required: ['name', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(
            property: 'name',
            description: 'Full name of the user.',
            type: 'string',
            example: 'John Doe',
            maxLength: 100,
            minLength: 3
        ),
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
            example: 'password123',
            minLength: 8
        ),
        new OA\Property(
            property: 'password_confirmation',
            description: 'Password confirmation of the user. (must match password)',
            type: 'string',
            format: 'password',
            example: 'password123',
            minLength: 8
        ),
    ],
    type: 'object'
)]
class RegisterUserRequestSchema
{
}
