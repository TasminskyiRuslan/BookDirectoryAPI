<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
|--------------------------------------------------------------------------
| payloads
|--------------------------------------------------------------------------
*/

/**
 * Generate a registration payload with optional overrides.
 *
 * @param array $overrides
 * @return array
 */
function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name'     => fake()->name(),
        'email'    => fake()->unique()->safeEmail(),
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ], $overrides);
}

/**
 * Generate an author payload with optional overrides.
 *
 * @param array $overrides
 * @return array
 */
function authorPayload(array $overrides = []): array
{
    $birthDate = fake()->date('Y-m-d', 'now');

    return array_merge([
        'last_name'     => fake()->lastName(),
        'first_name'    => fake()->firstName(),
        'patronymic' => fake()->optional()->firstName(),
        'birth_date' => $birthDate,
        'death_date' => fake()->optional()->dateTimeBetween($birthDate, 'now')?->format('Y-m-d'),
        'biography'    => fake()->paragraph(),
    ], $overrides);
}

/**
 * Generate a book payload with optional overrides.
 *
 * @param array $overrides
 * @return array
 */
function bookPayload(array $overrides = []): array
{
    return array_merge([
        'title' => fake()->sentence(3),
        'description' => fake()->paragraph(),
        'publication_date' => fake()->date(),
    ], $overrides);
}

/*
|--------------------------------------------------------------------------
| json structures
|--------------------------------------------------------------------------
*/

/**
 * Get the expected JSON structure for a user object.
 *
 * @return array
 */
function userJsonStructure(): array {
    return [
        'id',
        'name',
        'email',
        'role',
    ];
}

/**
 * Get the expected JSON structure for an authentication response.
 *
 * @return array
 */
function authJsonStructure(): array {
    return [
        'user' => userJsonStructure(),
        'access_token',
        'token_type',
    ];
}

/**
 * Get the expected JSON structure for an author object.
 *
 * @return array
 */
function authorJsonStructure(): array {
    return [
        'id',
        'last_name',
        'first_name',
        'patronymic',
        'slug',
        'birth_date',
        'death_date',
        'biography',
    ];
}

/**
 * Get the expected JSON structure for a book object.
 *
 * @return array
 */
function bookJsonStructure(): array {
    return [
        'id',
        'title',
        'slug',
        'description',
        'image_url',
        'publication_date',
        'authors' => [
            '*' => authorJsonStructure(),
        ],
    ];
}
