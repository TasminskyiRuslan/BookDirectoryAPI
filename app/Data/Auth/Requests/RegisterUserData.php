<?php

namespace App\Data\Auth\Requests;

use App\Data\Casts\LowercaseCast;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class RegisterUserData extends Data
{
    /**
     * @param string $name
     * @param string $email
     * @param string $password
     */
    public function __construct(
        #[Required]
        #[StringType]
        #[Min(2)]
        #[Max(255)]
        public string $name,

        #[Required]
        #[Email]
        #[Max(255)]
        #[Unique(table: 'users', column: 'email')]
        #[WithCast(castClass: LowercaseCast::class)]
        public string $email,

        #[Required]
        #[StringType]
        #[Confirmed]
        #[Password(min: 8)]
        public string $password,
    ) {}
}
