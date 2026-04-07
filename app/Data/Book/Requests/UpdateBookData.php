<?php

namespace App\Data\Book\Requests;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\BeforeOrEqual;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Max;
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

class UpdateBookData extends Data
{
    /**
     * @param string|Optional $title
     * @param string|Optional $slug
     * @param string|Optional|null $description
     * @param Carbon|Optional|null $publicationDate
     */
    public function __construct(
        #[Sometimes]
        #[StringType]
        #[Max(255)]
        public string|Optional $title,

        #[Sometimes]
        #[StringType]
        #[Max(255)]
        #[Unique(table: 'books', column: 'slug', ignore: new RouteParameterReference('book.id'))]
        #[Regex('/^[a-z0-9-]+$/')]
        public string|Optional $slug,

        #[Sometimes]
        #[Nullable]
        #[StringType]
        #[Max(5000)]
        public string|Optional|null $description,

        #[Sometimes]
        #[Nullable]
        #[Date]
        #[BeforeOrEqual('today')]
        #[MapName('publication_date')]
        #[WithCast(castClass: DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public Carbon|Optional|null $publicationDate,
    ) {}
}
