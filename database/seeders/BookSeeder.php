<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BookSeeder extends Seeder
{
    /**
     * Seed the books table and attach authors.
     */
    public function run(): void
    {
        Storage::disk('public')->deleteDirectory('books');

        $authors = Author::all();
        if ($authors->isEmpty()) {
            $authors = Author::factory()->count(50)->create();
        }

        Book::factory()->count(30)->create()->each(function (Book $book) use ($authors) {
            $book->authors()->attach(
                $authors->random(rand(1, 3))->pluck('id')
            );
        });
        Book::factory()->count(20)->withImage()->create()->each(function (Book $book) use ($authors) {
            $book->authors()->attach(
                $authors->random(rand(1, 3))->pluck('id')
            );
        });
    }
}
