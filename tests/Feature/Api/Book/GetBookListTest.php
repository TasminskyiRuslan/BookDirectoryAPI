<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Book;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('BookController -> index', function () {
    beforeEach(function () {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Cache::flush();
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | filters & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {
        it('filters books by a search string', function () {
            $book1 = Book::factory()->create(['title' => 'Hamlet']);
            $book2 = Book::factory()->create(['title' => 'Othello']);
            $searchString = substr($book1->title, 3);

            getJson(route('book.index', ['filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment(['id' => $book1->id]);
        });

        it('sorts books by created_at (desc) by default', function () {
            $oldBook = Book::factory()->create();
            $oldBook->setCreatedAt(now()->subDays(2))->save();
            $newBook = Book::factory()->create();
            $newBook->setCreatedAt(now()->subDay())->save();

            getJson(route('book.index'))
                ->assertOk()
                ->assertJsonFragment(['id' => $newBook->id])
                ->assertJsonFragment(['id' => $oldBook->id]);
        });

        it('sorts books by title (asc and desc)', function () {
            $book1 = Book::factory()->create(['title' => 'Book A']);
            $book2 = Book::factory()->create(['title' => 'Book B']);
            $book3 = Book::factory()->create(['title' => 'Book C']);

            getJson(route('book.index', ['sort' => 'title']))
                ->assertOk()
                ->assertJsonFragment(['id' => $book2->id])
                ->assertJsonFragment(['id' => $book3->id])
                ->assertJsonFragment(['id' => $book1->id]);
            getJson(route('book.index', ['sort' => '-title']))
                ->assertOk()
                ->assertJsonFragment(['id' => $book1->id])
                ->assertJsonFragment(['id' => $book3->id])
                ->assertJsonFragment(['id' => $book2->id]);
        });

        it('sorts books by created_at (asc and desc)', function () {
            $book1 = Book::factory()->create();
            $book1->setCreatedAt(now()->subDays(3))->save();
            $book2 = Book::factory()->create();
            $book2->setCreatedAt(now()->subDays(2))->save();
            $book3 = Book::factory()->create();
            $book3->setCreatedAt(now()->subDay())->save();

            getJson(route('book.index', ['sort' => 'created_at']))
                ->assertOk()
                ->assertJsonFragment(['id' => $book1->id])
                ->assertJsonFragment(['id' => $book2->id])
                ->assertJsonFragment(['id' => $book3->id]);
            getJson(route('book.index', ['sort' => '-created_at']))
                ->assertOk()
                ->assertJsonFragment(['id' => $book3->id])
                ->assertJsonFragment(['id' => $book2->id])
                ->assertJsonFragment(['id' => $book1->id]);
        });

        it('sorts books by publication_date (asc and desc)', function () {
            $book1 = Book::factory()->create(['publication_date' => now()->subYears(100)]);
            $book2 = Book::factory()->create(['publication_date' => now()->subYears(70)]);
            $book3 = Book::factory()->create(['publication_date' => now()->subYears(50)]);

            getJson(route('book.index', ['sort' => 'publication_date']))
                ->assertOk()
                ->assertJsonFragment(['id' => $book1->id])
                ->assertJsonFragment(['id' => $book2->id])
                ->assertJsonFragment(['id' => $book3->id]);
            getJson(route('book.index', ['sort' => '-publication_date']))
                ->assertOk()
                ->assertJsonFragment(['id' => $book3->id])
                ->assertJsonFragment(['id' => $book2->id])
                ->assertJsonFragment(['id' => $book1->id]);
        });

        it('includes authors_count by using the include query parameter', function () {
            Book::factory()->count(7)->create();

            getJson(route('book.index', ['include' => 'authors_count']))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => bookJsonStructure(true),
                    ]
                ]);
        });

        it('includes authors by using the include query parameter', function () {
            Book::factory()->count(7)->create();

            getJson(route('book.index', ['include' => 'authors']))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => bookJsonStructure(false, true),
                    ]
                ]);
        });

        it('includes authors_count and authors by using the include query parameter', function () {
            Book::factory()->count(7)->create();

            getJson(route('book.index', ['include' => 'authors_count,authors']))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => bookJsonStructure(true, true),
                    ]
                ]);
        });

        it('returns empty data when no books match the search', function () {
            Book::factory()->count(7)->create();

            getJson(route('book.index', ['filter[search]' => 'non-existent']))
                ->assertOk()
                ->assertJsonCount(0, 'data');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('allows an unauthenticated user to retrieve the list of books', function () {
            $books = Book::factory()->count(7)->create();

            getJson(route('book.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => bookJsonStructure()
                    ]
                ])
                ->assertJsonCount($books->count(), 'data');
        });

        it('allows a viewer to retrieve the list of books', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $books = Book::factory()->count(7)->create();

            getJson(route('book.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => bookJsonStructure()
                    ]
                ])
                ->assertJsonCount($books->count(), 'data');
        });

        it('allows an editor to retrieve the list of books', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $books = Book::factory()->count(7)->create();

            getJson(route('book.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => bookJsonStructure()
                    ]
                ])
                ->assertJsonCount($books->count(), 'data');
        });

        it('allows an admin to retrieve the list of books', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $books = Book::factory()->count(7)->create();

            getJson(route('book.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => bookJsonStructure()
                    ]
                ])
                ->assertJsonCount($books->count(), 'data');
        });

        it('allows a super-admin to retrieve the list of books', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $books = Book::factory()->count(7)->create();

            getJson(route('book.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => bookJsonStructure()
                    ]
                ])
                ->assertJsonCount($books->count(), 'data');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('stores the book list in the cache after the first request', function () {
            Cache::spy();
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);

            getJson(route('book.index'))->assertOk();
            Cache::shouldHaveReceived('tags')
                ->with(['book'])
                ->once();
        });

        it('returns data from the cache instead of the database on subsequent requests', function () {
            $oldTitle = 'Hamlet';
            $book = Book::factory()->create(['title' => $oldTitle]);

            getJson(route('book.index'))->assertOk();

            $newTitle = 'Othello';
            DB::table('books')->update(['title' => $newTitle]);

            getJson(route('book.index'))
                ->assertOk()
                ->assertJsonFragment(['title' => $oldTitle])
                ->assertJsonMissing(['title' => $newTitle]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | pagination
    |--------------------------------------------------------------------------
    */
    describe('pagination', function () {
        it('returns a paginated list of books', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            User::factory()->count(30)->create();

            getJson(route('book.index'))
                ->assertOk()
                ->assertJsonStructure(paginationJsonStructure());
        });
    });
})->group('book');
