<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    /**
     * Define the author model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lastName = fake()->lastName();
        $firstName = fake()->firstName();
        $patronymic = fake()->optional()->firstName();

        return [
            'last_name' => $lastName,
            'first_name' => $firstName,
            'patronymic' => $patronymic,
            'slug' => Str::slug("$lastName $firstName $patronymic"),
        ];
    }
}
