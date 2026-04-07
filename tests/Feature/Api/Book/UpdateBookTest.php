<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\Book;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('BookController -> update', function () {

    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to update the book', function () {
            $targetBook = Book::factory()->create();
            $data = bookPayload();

            patchJson(route('book.update', $targetBook), $data)
                ->assertForbidden();

            $this->assertDatabaseMissing('books', [
                'id' => $targetBook->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'publication_date' => $data['publication_date'],
            ]);
        });

        it('fails if a viewer tries to update the book', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetBook = Book::factory()->create();
            $data = bookPayload();

            patchJson(route('book.update', $targetBook), $data)
                ->assertForbidden();

            $this->assertDatabaseMissing('books', [
                'id' => $targetBook->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'publication_date' => $data['publication_date'],
            ]);
        });

        it('allows an editor to update the book', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetBook = Book::factory()->create();
            $data = bookPayload();

            patchJson(route('book.update', $targetBook), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => bookJsonStructure(true)]);

            $this->assertDatabaseHas('books', [
                'id' => $targetBook->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'publication_date' => $data['publication_date'],
            ]);
        });

        it('allows an admin to update the book', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetBook = Book::factory()->create();
            $data = bookPayload();

            patchJson(route('book.update', $targetBook), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => bookJsonStructure(true)]);

            $this->assertDatabaseHas('books', [
                'id' => $targetBook->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'publication_date' => $data['publication_date'],
            ]);
        });

        it('allows a super-admin to update the book', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetBook = Book::factory()->create();
            $data = bookPayload();

            patchJson(route('book.update', $targetBook), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => bookJsonStructure(true)]);

            $this->assertDatabaseHas('books', [
                'id' => $targetBook->id,
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
        it('fails if the present fields are empty', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('book.update', $targetBook), bookPayload([
                'title' => '',
                'slug' => ''
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug']);
        });

        it('fails if the present fields are null', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('book.update', $targetBook), bookPayload([
                'title' => null,
                'slug' => null
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug']);
        });

        it('fails if the fields are too long', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('book.update', $targetBook), bookPayload([
                'title' => str_repeat('A', 256),
                'slug' => str_repeat('B', 256),
                'description' => str_repeat('C', 5001),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title', 'slug', 'description']);
        });

        it('fails if the publication_date format is invalid', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('book.update', $targetBook), bookPayload([
                'publication_date' => 'invalid-date',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['publication_date']);
        });

        it('fails if the publication_date is in the future', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('book.update', $targetBook), bookPayload([
                'publication_date' => now()->addDay()->format('Y-m-d'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['publication_date']);
        });

        it('fails if the slug is taken by another book', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create(['slug' => 'my-slug']);
            $otherBook = Book::factory()->create(['slug' => 'taken-slug']);
            Sanctum::actingAs($editor);

            patchJson(route('book.update', $targetBook), bookPayload([
                'slug' => $otherBook->slug,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if the slug format is invalid', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('book.update', $targetBook), bookPayload([
                'slug' => 'Invalid Slug!'
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('succeeds if the slug remains the same (ignore current)', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('book.update', $targetBook), [
                'slug' => $targetBook->slug,
            ])
                ->assertOk()
                ->assertJsonFragment(['slug' => $targetBook->slug]);
        });

        it('fails if the book does not exist', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            patchJson(route('book.update', 999))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when a book is updated', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            Cache::tags(['book'])->put('books', 'test_value', config('cache.ttl.books'));
            expect(Cache::tags(['book'])->get('books'))->toBe('test_value');
            patchJson(route('book.update', $targetBook), bookPayload())
                ->assertOk();
            expect(Cache::tags(['book'])->get('books'))->toBeNull();
        });
    });
})->group('book');
