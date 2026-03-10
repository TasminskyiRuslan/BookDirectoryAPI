<?php

namespace App\Data\Auth\Results;

use App\Models\User;
use Spatie\LaravelData\Data;

class AuthData extends Data
{
    /**
     * @param User $user
     * @param string $accessToken
     * @param string|null $tokenType
     */
    public function __construct(
        public User $user,
        public string $accessToken,
        public ?string $tokenType = 'Bearer',
    ) {}
}
