<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\Book;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('BookController -> store', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to create a book', function () {
            $data = bookPayload();

            postJson(route('book.store'), $data)
                ->assertForbidden();
            $this->assertDatabaseMissing('books', [
                'title' => $data['title'],
                'description' => $data['description'],
                'publication_date' => $data['publication_date'],
            ]);
        });

        it('fails if a viewer tries to create a book', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $data = bookPayload();

            postJson(route('book.store'), $data)
                ->assertForbidden();
            $this->assertDatabaseMissing('books', [
                'title' => $data['title'],
                'description' => $data['description'],
                'publication_date' => $data['publication_date'],
            ]);
        });

        it('allows an editor to create a book', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $data = bookPayload();

            postJson(route('book.store'), $data)
                ->assertCreated()
                ->assertJsonStructure(['data' => bookJsonStructure(true)]);
            $this->assertDatabaseHas('books', [
                'title' => $data['title'],
                'description' => $data['description'],
                'publication_date' => $data['publication_date'],
            ]);
        });

        it('allows an admin to create a book', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $data = bookPayload();

            postJson(route('book.store'), $data)
                ->assertCreated()
                ->assertJsonStructure(['data' => bookJsonStructure(true)]);
            $this->assertDatabaseHas('books', [
                'title' => $data['title'],
                'description' => $data['description'],
                'publication_date' => $data['publication_date'],
            ]);
        });

        it('allows a super-admin to create a book', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $data = bookPayload();

            postJson(route('book.store'), $data)
                ->assertCreated()
                ->assertJsonStructure(['data' => bookJsonStructure(true)]);
            $this->assertDatabaseHas('books', [
                'title' => $data['title'],
                'description' => $data['description'],
                'publication_date' => $data['publication_date'],
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

            postJson(route('book.store'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title']);
        });

        it('fails if the fields are too long', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            postJson(route('book.store'), bookPayload([
                'title' => str_repeat('A', 256),
                'slug' => str_repeat('B', 256),
                'description' => str_repeat('C', 5001),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'description']);
        });

        it('fails if the publication_date format is invalid', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            postJson(route('book.store'), bookPayload([
                'publication_date' => 'invalid-date',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['publication_date']);
        });

        it('fails if the publication_date is in the future', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            postJson(route('book.store'), bookPayload([
                'publication_date' => now()->addDay()->format('Y-m-d'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['publication_date']);
        });

        it('fails if the slug is taken by another book', function () {
            $otherBook = Book::factory()->create(['slug' => 'taken-slug']);
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            postJson(route('book.store'), bookPayload(['slug' => $otherBook->slug]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if the slug format is invalid', function () {
            $editor = User::factory()->editor()->create();
            $book = Book::factory()->create();
            Sanctum::actingAs($editor);

            postJson(route('book.store'), bookPayload([
                'slug' => 'Invalid Slug!'
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('succeeds if a slug is provided manually', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $slug = 'test-slug';

            postJson(route('book.store'), bookPayload(['slug' => $slug]))
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
        it('flushes the cache when a book is created', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            Cache::tags(['book'])->put('books', 'test_value', config('cache.ttl.books'));
            expect(Cache::tags(['book'])->get('books'))->toBe('test_value');

            postJson(route('book.store'), bookPayload())
                ->assertCreated();
            expect(Cache::tags(['book'])->get('books'))->toBeNull();
        });
    });
})->group('book');
