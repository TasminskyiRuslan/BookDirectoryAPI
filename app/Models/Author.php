<?php

namespace App\Models;

use Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

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
            ->generateSlugsOnCreate()
            ->doNotGenerateSlugsOnUpdate();
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
