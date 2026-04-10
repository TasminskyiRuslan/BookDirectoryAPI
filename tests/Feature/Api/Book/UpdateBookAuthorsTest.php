<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\Author;
use App\Models\Book;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

describe('BookAuthorsController -> update', function () {

    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to update the book authors', function () {
            $targetBook = Book::factory()->create();
            $authors = Author::factory()->count(3)->create();
            $authorIds = $authors->pluck('id')->toArray();

            putJson(route('book.authors.update', $targetBook), ['author_ids' => $authorIds])
                ->assertForbidden();

            foreach ($authorIds as $bookId) {
                $this->assertDatabaseMissing('author_book', [
                    'author_id' => $targetBook->id,
                    'book_id' => $bookId,
                ]);
            }
        });

        it('fails if a viewer tries to update the book authors', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetBook = Book::factory()->create();
            $authors = Author::factory()->count(3)->create();
            $authorIds = $authors->pluck('id')->toArray();

            putJson(route('book.authors.update', $targetBook), ['author_ids' => $authorIds])
                ->assertForbidden();

            foreach ($authorIds as $bookId) {
                $this->assertDatabaseMissing('author_book', [
                    'author_id' => $targetBook->id,
                    'book_id' => $bookId,
                ]);
            }
        });

        it('allows an editor to update the book authors', function () {
            $this->withoutExceptionHandling();
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetBook = Book::factory()->create();
            $authors = Author::factory()->count(3)->create();
            $authorIds = $authors->pluck('id')->toArray();

            putJson(route('book.authors.update', $targetBook), ['author_ids' => $authorIds])
                ->assertOk()
                ->assertJsonStructure(['data' => bookJsonStructure(true, true)])
                ->assertJsonPath('data.authors.*.id', $authorIds);

            foreach ($authorIds as $authorId) {
                $this->assertDatabaseHas('author_book', [
                    'book_id' => $targetBook->id,
                    'author_id' => $authorId,
                ]);
            }
        });

        it('allows an admin to update the book authors', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetBook = Book::factory()->create();
            $authors = Author::factory()->count(3)->create();
            $authorIds = $authors->pluck('id')->toArray();

            putJson(route('book.authors.update', $targetBook), ['author_ids' => $authorIds])
                ->assertOk()
                ->assertJsonStructure(['data' => bookJsonStructure(true, true)])
                ->assertJsonPath('data.authors.*.id', $authorIds);

            foreach ($authorIds as $authorId) {
                $this->assertDatabaseHas('author_book', [
                    'book_id' => $targetBook->id,
                    'author_id' => $authorId,
                ]);
            }
        });

        it('allows a super-admin to update the book authors', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetBook = Book::factory()->create();
            $authors = Author::factory()->count(3)->create();
            $authorIds = $authors->pluck('id')->toArray();

            putJson(route('book.authors.update', $targetBook), ['author_ids' => $authorIds])
                ->assertOk()
                ->assertJsonStructure(['data' => bookJsonStructure(true, true)])
                ->assertJsonPath('data.authors.*.id', $authorIds);

            foreach ($authorIds as $authorId) {
                $this->assertDatabaseHas('author_book', [
                    'book_id' => $targetBook->id,
                    'author_id' => $authorId,
                ]);
            }
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
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            putJson(route('book.authors.update', $targetBook), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['author_ids']);
        });

        it('fails if the author_ids is not an array', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            putJson(route('book.authors.update', $targetBook), ['author_ids' => 1])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['author_ids']);
        });

        it('fails if the author_ids elements are not an integer', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            putJson(route('book.authors.update', $targetBook), ['author_ids' => ['a']])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['author_ids.0']);
        });

        it('fails if the author_ids array is empty', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            putJson(route('book.authors.update', $targetBook), ['author_ids' => []])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['author_ids']);
        });

        it('fails if the author_ids elements are not exists in the authors table', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            putJson(route('book.authors.update', $targetBook), ['author_ids' => [1]])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['author_ids.0']);
        });

        it('fails if the book does not exist', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            putJson(route('book.authors.update', 999), ['author_ids' => [1, 2, 3]])
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when an book authors is updated', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);
            $authors = Author::factory()->count(3)->create();
            $authorIds = $authors->pluck('id')->toArray();


            Cache::tags(['book'])->put('books', 'test_value', config('cache.ttl.books'));
            expect(Cache::tags(['book'])->get('books'))->toBe('test_value');
            Cache::tags(['author'])->put('authors', 'test_value', config('cache.ttl.authors'));
            expect(Cache::tags(['author'])->get('authors'))->toBe('test_value');
            putJson(route('book.authors.update', $targetBook), ['author_ids' => $authorIds])
                ->assertOk();
            expect(Cache::tags(['book'])->get('books'))->toBeNull()
                ->and(Cache::tags(['author'])->get('authors'))->toBeNull();
        });
    });
})->group('book');
