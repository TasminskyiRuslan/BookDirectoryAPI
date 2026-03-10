<?php

namespace App\Http\Resources\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read User $user
 * @property-read string $accessToken
 * @property-read string|null $tokenType
 */
class AuthResource extends JsonResource
{
    /**
     * Transform the auth resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => UserResource::make($this->user),
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType ?? 'Bearer',
        ];
    }
}
