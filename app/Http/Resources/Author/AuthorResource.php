<?php

namespace App\Http\Resources\Author;

use App\Http\Resources\Book\BookResource;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read int $id
 * @property-read string $last_name
 * @property-read string $first_name
 * @property-read string $patronymic
 * @property-read string $slug
 * @property-read CarbonImmutable|null $birth_date
 * @property-read CarbonImmutable|null $death_date
 * @property-read string $biography
 * @property mixed $image_path
 */
class AuthorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'last_name' => $this->last_name,
		    'first_name' => $this->first_name,
		    'patronymic' => $this->patronymic,
		    'slug' => $this->slug,
            'birth_date' => $this->birth_date?->toDateString(),
            'death_date' => $this->death_date?->toDateString(),
		    'biography' => $this->biography,
            'image_url' => $this->image_path ? Storage::disk('authors')->url($this->image_path) : null,
            'books' => BookResource::collection($this->whenLoaded('books')),
        ];
    }
}
