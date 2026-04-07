<?php

namespace App\Data\Book\Requests;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\BeforeOrEqual;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Symfony\Contracts\Service\Attribute\Required;

class CreateBookData extends Data
{
    /**
     * @param string $title
     * @param string|null $slug
     * @param string|null $description
     * @param Carbon|null $publicationDate
     */
    public function __construct(
        #[Required]
        #[StringType]
        #[Max(255)]
        public string $title,

        #[Nullable]
        #[StringType]
        #[Max(255)]
        #[Unique(table: 'books', column: 'slug')]
        #[Regex('/^[a-z0-9-]+$/')]
        public ?string $slug,

        #[Nullable]
        #[StringType]
        #[Max(5000)]
        public ?string $description,

        #[Nullable]
        #[Date]
        #[BeforeOrEqual('today')]
        #[MapName('publication_date')]
        #[WithCast(castClass: DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public ?Carbon $publicationDate,
    ) {}
}
