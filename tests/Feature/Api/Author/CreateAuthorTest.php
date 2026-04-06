<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\Author;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('AuthorController -> store', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to create an author', function () {
            $data = authorPayload();

            postJson(route('author.store'), $data)
                ->assertForbidden();
            $this->assertDatabaseMissing('authors', [
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('fails if a viewer tries to create an author', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $data = authorPayload();

            postJson(route('author.store'), $data)
                ->assertForbidden();
            $this->assertDatabaseMissing('authors', [
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('allows an editor to create an author', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $data = authorPayload();

            postJson(route('author.store'), $data)
                ->assertCreated()
                ->assertJsonStructure(['data' => authorJsonStructure()]);
            $this->assertDatabaseHas('authors', [
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('allows an admin to create an author', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $data = authorPayload();

            postJson(route('author.store'), $data)
                ->assertCreated()
                ->assertJsonStructure(['data' => authorJsonStructure()]);
            $this->assertDatabaseHas('authors', [
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('allows a super-admin to create an author', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $data = authorPayload();

            postJson(route('author.store'), $data)
                ->assertCreated()
                ->assertJsonStructure(['data' => authorJsonStructure()]);
            $this->assertDatabaseHas('authors', [
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the required fields are missing', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            postJson(route('author.store'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name']);
        });

        it('fails if the fields are too short', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            postJson(route('author.store'), authorPayload([
                'last_name' => 'A',
                'first_name' => 'B',
                'patronymic' => 'C',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name', 'patronymic']);
        });

        it('fails if the fields are too long', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            postJson(route('author.store'), authorPayload([
                'last_name' => str_repeat('A', 256),
                'first_name' => str_repeat('B', 256),
                'patronymic' => str_repeat('C', 256),
                'slug' => str_repeat('D', 256),
                'biography' => str_repeat('E', 5001),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name', 'patronymic', 'slug']);
        });

        it('fails if the birth_date and death_date formats are invalid', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            postJson(route('author.store'), authorPayload([
                'birth_date' => 'invalid-date',
                'death_date' => 'invalid-date',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['birth_date', 'death_date']);
        });

        it('fails if the birth_date and death_date are in the future', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            postJson(route('author.store'), authorPayload([
                'birth_date' => now()->addDay()->format('Y-m-d'),
                'death_date' => now()->addDay()->format('Y-m-d'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['birth_date', 'death_date']);
        });

        it('fails if the death_date is before the birth+date', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            postJson(route('author.store'), authorPayload([
                'birth_date' => now()->subDay()->format('Y-m-d'),
                'death_date' => now()->subDays(2)->format('Y-m-d'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['death_date']);
        });

        it('fails if the slug is taken by another author', function () {
            $otherAuthor = Author::factory()->create(['slug' => 'taken-slug']);
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            postJson(route('author.store'), authorPayload(['slug' => $otherAuthor->slug]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if the slug format is invalid', function () {
            $editor = User::factory()->editor()->create();
            $author = Author::factory()->create();
            Sanctum::actingAs($editor);

            postJson(route('author.store'), authorPayload([
                'slug' => 'Invalid Slug!'
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('succeeds if the slug is provided manually', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $slug = 'test-slug';

            postJson(route('author.store'), authorPayload(['slug' => $slug]))
                ->assertCreated()
                ->assertJsonFragment(['slug' => $slug]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when an author is created', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            Cache::tags(['author'])->put('authors', 'test_value', config('cache.ttl.authors'));
            expect(Cache::tags(['author'])->get('authors'))->toBe('test_value');

            postJson(route('author.store'), authorPayload())->assertCreated();
            expect(Cache::tags(['author'])->get('authors'))->toBeNull();
        });
    });
})->group('author');
