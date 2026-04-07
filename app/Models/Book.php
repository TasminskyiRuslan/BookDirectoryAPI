<?php

namespace App\Models;

use App\Observers\Book\BookObserver;
use Database\Factories\BookFactory;
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
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string|null $image_path
 * @property Carbon|null $publication_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Author> $authors
 * @property-read int|null $authors_count
 * @method static BookFactory factory($count = null, $state = [])
 * @method static Builder<static>|Book newModelQuery()
 * @method static Builder<static>|Book newQuery()
 * @method static Builder<static>|Book query()
 * @method static Builder<static>|Book whereCreatedAt($value)
 * @method static Builder<static>|Book whereDescription($value)
 * @method static Builder<static>|Book whereId($value)
 * @method static Builder<static>|Book whereImagePath($value)
 * @method static Builder<static>|Book wherePublicationDate($value)
 * @method static Builder<static>|Book whereSlug($value)
 * @method static Builder<static>|Book whereTitle($value)
 * @method static Builder<static>|Book whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory, HasSlug;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'image_path',
        'publication_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
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
            ->generateSlugsFrom(['title'])
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
        static::observe(BookObserver::class);
    }

    /**
     * Get the authors associated with the book.
     *
     * @return BelongsToMany
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    /**
     * Remove the image path.
     *
     * @return $this
     */
    public function removeImage(): static
    {
        $this->image_path = null;
        return $this;
    }
}
