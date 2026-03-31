<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    /**
     * Define the Author model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lastName = fake()->lastName();
        $firstName = fake()->firstName();
        $patronymic = fake()->optional()->firstName();
        $birthDate = fake()->date('Y-m-d', 'now');

        return [
            'last_name' => $lastName,
            'first_name' => $firstName,
            'patronymic' => $patronymic,
            'slug' => Str::slug("$lastName $firstName $patronymic"),
            'birth_date' => $birthDate,
            'death_date' => fake()->optional()->dateTimeBetween($birthDate, 'now')?->format('Y-m-d'),
            'biography' => fake()->paragraph(),
        ];
    }
}
