<?php

namespace App\Data\Author\Requests;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\BeforeOrEqual;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class CreateAuthorData extends Data
{
    /**
     * @param string $lastName
     * @param string $firstName
     * @param string|null $patronymic
     * @param string|null $slug
     * @param Carbon|null $birthDate
     * @param Carbon|null $deathDate
     * @param string|null $biography
     */
    public function __construct(
        #[Required]
        #[StringType]
        #[Min(2)]
        #[Max(255)]
        #[MapName('last_name')]
        public string $lastName,

        #[Required]
        #[StringType]
        #[Min(2)]
        #[Max(255)]
        #[MapName('first_name')]
        public string $firstName,

        #[Nullable]
        #[StringType]
        #[Min(2)]
        #[Max(255)]
        public ?string $patronymic,

        #[Nullable]
        #[StringType]
        #[Max(255)]
        #[Unique('authors', 'slug')]
        #[Regex('/^[a-z0-9-]+$/')]
        public ?string $slug,

        #[Nullable]
        #[Date]
        #[BeforeOrEqual('today')]
        #[MapName('birth_date')]
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public ?Carbon $birthDate,

        #[Nullable]
        #[Date]
        #[AfterOrEqual('birth_date')]
        #[BeforeOrEqual('today')]
        #[MapName('death_date')]
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public ?Carbon $deathDate,

        #[Nullable]
        #[StringType]
        #[Max(5000)]
        public ?string $biography,
    ) {}
}
