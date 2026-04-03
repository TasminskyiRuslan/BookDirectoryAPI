<?php

namespace App\Http\Resources\Book;

use App\Http\Resources\Author\AuthorResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property mixed $id
 * @property mixed $title
 * @property mixed $slug
 * @property mixed $description
 * @property mixed $image_path
 * @property mixed $publication_date
 */
class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'publication_date' => $this->publication_date?->toDateString(),
            'authors' => AuthorResource::collection($this->whenLoaded('authors')),
        ];
    }
}
