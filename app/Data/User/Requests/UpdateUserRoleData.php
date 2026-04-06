<?php

namespace App\Data\User\Requests;

use App\Enums\UserRole;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;

class UpdateUserRoleData extends Data
{
    /**
     * @param UserRole $role
     */
    public function __construct(
        #[Required]
        #[StringType]
        #[Enum(enum: UserRole::class)]
        #[In(UserRole::ADMIN->value, UserRole::EDITOR->value, UserRole::VIEWER->value)]
        public UserRole $role,
    ) {}
}
