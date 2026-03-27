<?php

namespace App\Models;

use App\Observers\AuthorObserver;
use Database\Factories\AuthorFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property string $last_name
 * @property string $first_name
 * @property string|null $patronymic
 * @property string $slug
 * @property Carbon|null $birth_date
 * @property Carbon|null $death_date
 * @property string|null $biography
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Book> $books
 * @property-read int|null $books_count
 * @method static AuthorFactory factory($count = null, $state = [])
 * @method static Builder<static>|Author newModelQuery()
 * @method static Builder<static>|Author newQuery()
 * @method static Builder<static>|Author query()
 * @method static Builder<static>|Author whereBiography($value)
 * @method static Builder<static>|Author whereBirthDate($value)
 * @method static Builder<static>|Author whereCreatedAt($value)
 * @method static Builder<static>|Author whereDeathDate($value)
 * @method static Builder<static>|Author whereFirstName($value)
 * @method static Builder<static>|Author whereId($value)
 * @method static Builder<static>|Author whereLastName($value)
 * @method static Builder<static>|Author wherePatronymic($value)
 * @method static Builder<static>|Author whereSlug($value)
 * @method static Builder<static>|Author whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Author extends Model
{
    /** @use HasFactory<AuthorFactory> */
    use HasFactory, HasSlug;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'last_name',
        'first_name',
        'patronymic',
        'slug',
        'birth_date',
        'death_date',
        'biography',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'death_date' => 'date',
        ];
    }

    /**
     * Get the route key name for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Configure the slug generation options for the Author model.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['last_name', 'first_name', 'patronymic'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Bootstrap any model events and attach the observer.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::observe(AuthorObserver::class);
    }

    /**
     * Get the books associated with the author.
     *
     * @return BelongsToMany
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }
}
