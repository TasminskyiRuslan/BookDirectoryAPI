<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the Book model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'image_path' => null,
            'publication_date' => fake()->date(),
        ];
    }

    /**
     * Add an image to the book.
     *
     * @param string|null $path
     * @return static
     */
    public function withImage(?string $path = null): static
    {
        return $this->state(function (array $attributes) use ($path) {
            return ['image_path' => $path ?? 'books/' . fake()->uuid() . '.jpg'];
        });
    }
}
