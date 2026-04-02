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
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;

class UpdateAuthorData extends Data
{
    public function __construct(
        #[Sometimes]
        #[StringType]
        #[Min(2)]
        #[Max(255)]
        #[MapName('last_name')]
        public string|Optional $lastName,

        #[Sometimes]
        #[StringType]
        #[Min(2)]
        #[Max(255)]
        #[MapName('first_name')]
        public string|Optional $firstName,

        #[Sometimes]
        #[Nullable]
        #[StringType]
        #[Min(2)]
        #[Max(255)]
        public string|Optional|null $patronymic,

        #[Sometimes]
        #[StringType]
        #[Max(255)]
        #[Unique(table: 'authors', column: 'slug', ignore: new RouteParameterReference('author.id'))]
        #[Regex('/^[a-z0-9-]+$/')]
        public string|Optional $slug,

        #[Sometimes]
        #[Nullable]
        #[Date]
        #[BeforeOrEqual('today')]
        #[MapName('birth_date')]
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public Carbon|Optional|null $birthDate,

        #[Sometimes]
        #[Nullable]
        #[Date]
        #[AfterOrEqual('birth_date')]
        #[BeforeOrEqual('today')]
        #[MapName('death_date')]
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public Carbon|Optional|null $deathDate,

        #[Sometimes]
        #[Nullable]
        #[StringType]
        #[Max(5000)]
        public string|Optional|null $biography,
    ) {}
}
