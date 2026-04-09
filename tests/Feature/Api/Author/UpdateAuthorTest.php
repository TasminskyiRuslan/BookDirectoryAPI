<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\Author;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('AuthorController -> update', function () {

    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to update the author', function () {
            $targetAuthor = Author::factory()->create();
            $data = authorPayload();

            patchJson(route('author.update', $targetAuthor), $data)
                ->assertForbidden();

            $this->assertDatabaseMissing('authors', [
                'id' => $targetAuthor->id,
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('fails if a viewer tries to update the author', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetAuthor = Author::factory()->create();
            $data = authorPayload();

            patchJson(route('author.update', $targetAuthor), $data)
                ->assertForbidden();

            $this->assertDatabaseMissing('authors', [
                'id' => $targetAuthor->id,
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('allows an editor to update the author', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetAuthor = Author::factory()->create();
            $data = authorPayload();

            patchJson(route('author.update', $targetAuthor), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => authorJsonStructure(true, true)]);

            $this->assertDatabaseHas('authors', [
                'id' => $targetAuthor->id,
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('allows an admin to update the author', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetAuthor = Author::factory()->create();
            $data = authorPayload();

            patchJson(route('author.update', $targetAuthor), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => authorJsonStructure(true, true)]);

            $this->assertDatabaseHas('authors', [
                'id' => $targetAuthor->id,
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('allows a super-admin to update the author', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetAuthor = Author::factory()->create();
            $data = authorPayload();

            patchJson(route('author.update', $targetAuthor), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => authorJsonStructure(true, true)]);

            $this->assertDatabaseHas('authors', [
                'id' => $targetAuthor->id,
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
        it('fails if the present fields are empty', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $targetAuthor), authorPayload([
                'last_name' => '',
                'first_name' => '',
                'slug' => ''
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name', 'slug']);
        });

        it('fails if the present fields are null', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $targetAuthor), authorPayload([
                'last_name' => null,
                'first_name' => null,
                'slug' => null
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name', 'slug']);
        });

        it('fails if the fields are too short', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $targetAuthor), authorPayload([
                'last_name' => 'A',
                'first_name' => 'B',
                'patronymic' => 'C',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name', 'patronymic']);
        });

        it('fails if the fields are too long', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $targetAuthor), authorPayload([
                'last_name' => str_repeat('A', 256),
                'first_name' => str_repeat('B', 256),
                'patronymic' => str_repeat('C', 256),
                'slug' => str_repeat('D', 256),
                'biography' => str_repeat('E', 5001),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name', 'patronymic', 'slug', 'biography']);
        });

        it('fails if the birth_date or death_date format are invalid', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $targetAuthor), authorPayload([
                'birth_date' => 'invalid-date',
                'death_date' => 'invalid-date',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['birth_date', 'death_date']);
        });

        it('fails if the birth_date or death_date are in the future', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $targetAuthor), authorPayload([
                'birth_date' => now()->addDay()->format('Y-m-d'),
                'death_date' => now()->addDay()->format('Y-m-d'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['birth_date', 'death_date']);
        });

        it('fails if the death_date is before birth_date', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $targetAuthor), authorPayload([
                'birth_date' => now()->subDay()->format('Y-m-d'),
                'death_date' => now()->subDays(2)->format('Y-m-d'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['death_date']);
        });

        it('fails if the slug is taken by another author', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create(['slug' => 'my-slug']);
            $otherAuthor = Author::factory()->create(['slug' => 'taken-slug']);
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $targetAuthor), authorPayload([
                'slug' => $otherAuthor->slug,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if the slug format is invalid', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $targetAuthor), authorPayload([
                'slug' => 'Invalid Slug!'
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('succeeds if the slug remains the same (ignore current)', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $targetAuthor), [
                'slug' => $targetAuthor->slug,
            ])
                ->assertOk()
                ->assertJsonFragment(['slug' => $targetAuthor->slug]);
        });

        it('fails if the author does not exist', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', 999))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when an author is updated', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            Cache::tags(['author'])->put('authors', 'test_value', config('cache.ttl.authors'));
            expect(Cache::tags(['author'])->get('authors'))->toBe('test_value');
            Cache::tags(['book'])->put('books', 'test_value', config('cache.ttl.books'));
            expect(Cache::tags(['book'])->get('books'))->toBe('test_value');
            patchJson(route('author.update', $targetAuthor), authorPayload())
                ->assertOk();
            expect(Cache::tags(['author'])->get('authors'))->toBeNull()
                ->and(Cache::tags(['book'])->get('books'))->toBeNull();
        });
    });
})->group('author');
