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

describe('AuthorBooksController -> update', function () {

    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to update the author books', function () {
            $targetAuthor = Author::factory()->create();
            $books = Book::factory()->count(3)->create();
            $bookIds = $books->pluck('id')->toArray();

            putJson(route('author.books.update', $targetAuthor), ['book_ids' => $bookIds])
                ->assertForbidden();

            foreach ($bookIds as $bookId) {
                $this->assertDatabaseMissing('author_book', [
                    'author_id' => $targetAuthor->id,
                    'book_id' => $bookId,
                ]);
            }
        });

        it('fails if a viewer tries to update the author books', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetAuthor = Author::factory()->create();
            $books = Book::factory()->count(3)->create();
            $bookIds = $books->pluck('id')->toArray();

            putJson(route('author.books.update', $targetAuthor), ['book_ids' => $bookIds])
                ->assertForbidden();

            foreach ($bookIds as $bookId) {
                $this->assertDatabaseMissing('author_book', [
                    'author_id' => $targetAuthor->id,
                    'book_id' => $bookId,
                ]);
            }
        });

        it('allows an editor to update the author books', function () {
            $this->withoutExceptionHandling();
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetAuthor = Author::factory()->create();
            $books = Book::factory()->count(3)->create();
            $bookIds = $books->pluck('id')->toArray();

            putJson(route('author.books.update', $targetAuthor), ['book_ids' => $bookIds])
                ->assertOk()
                ->assertJsonStructure(['data' => authorJsonStructure(true, true)])
                ->assertJsonPath('data.books.*.id', $bookIds);

            foreach ($bookIds as $bookId) {
                $this->assertDatabaseHas('author_book', [
                    'author_id' => $targetAuthor->id,
                    'book_id' => $bookId,
                ]);
            }
        });

        it('allows an admin to update the author books', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetAuthor = Author::factory()->create();
            $books = Book::factory()->count(3)->create();
            $bookIds = $books->pluck('id')->toArray();

            putJson(route('author.books.update', $targetAuthor), ['book_ids' => $bookIds])
                ->assertOk()
                ->assertJsonStructure(['data' => authorJsonStructure(true, true)])
                ->assertJsonPath('data.books.*.id', $bookIds);

            foreach ($bookIds as $bookId) {
                $this->assertDatabaseHas('author_book', [
                    'author_id' => $targetAuthor->id,
                    'book_id' => $bookId,
                ]);
            }
        });

        it('allows a super-admin to update the author books', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetAuthor = Author::factory()->create();
            $books = Book::factory()->count(3)->create();
            $bookIds = $books->pluck('id')->toArray();

            putJson(route('author.books.update', $targetAuthor), ['book_ids' => $bookIds])
                ->assertOk()
                ->assertJsonStructure(['data' => authorJsonStructure(true, true)])
                ->assertJsonPath('data.books.*.id', $bookIds);

            foreach ($bookIds as $bookId) {
                $this->assertDatabaseHas('author_book', [
                    'author_id' => $targetAuthor->id,
                    'book_id' => $bookId,
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
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            putJson(route('author.books.update', $targetAuthor), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['book_ids']);
        });

        it('fails if the book_ids is not an array', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            putJson(route('author.books.update', $targetAuthor), ['book_ids' => 1])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['book_ids']);
        });

        it('fails if the book_ids elements are not an integer', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            putJson(route('author.books.update', $targetAuthor), ['book_ids' => ['a']])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['book_ids.0']);
        });

        it('fails if the book_ids array is empty', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            putJson(route('author.books.update', $targetAuthor), ['book_ids' => []])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['book_ids']);
        });

        it('fails if the book_ids elements are not exists in the books table', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            putJson(route('author.books.update', $targetAuthor), ['book_ids' => [1]])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['book_ids.0']);
        });

        it('fails if the author does not exist', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            putJson(route('author.books.update', 999), ['book_ids' => [1, 2, 3]])
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when an author books is updated', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);
            $books = Book::factory()->count(3)->create();
            $bookIds = $books->pluck('id')->toArray();

            Cache::tags(['author'])->put('authors', 'test_value', config('cache.ttl.authors'));
            expect(Cache::tags(['author'])->get('authors'))->toBe('test_value');
            Cache::tags(['book'])->put('books', 'test_value', config('cache.ttl.books'));
            expect(Cache::tags(['book'])->get('books'))->toBe('test_value');
            putJson(route('author.books.update', $targetAuthor), ['book_ids' => $bookIds])
                ->assertOk();
            expect(Cache::tags(['author'])->get('authors'))->toBeNull()
                ->and(Cache::tags(['book'])->get('books'))->toBeNull();
        });
    });
})->group('author');
