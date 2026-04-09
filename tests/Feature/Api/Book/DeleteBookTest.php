<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Book;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('BookController -> destroy', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete the book', function () {
            $targetBook = Book::factory()->create();

            deleteJson(route('book.destroy', $targetBook))
                ->assertForbidden();
            $this->assertDatabaseHas('books', [
                'id' => $targetBook->id,
            ]);
        });

        it('fails if a viewer tries to delete the books', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetBook = Book::factory()->create();

            deleteJson(route('book.destroy', $targetBook))
                ->assertForbidden();
            $this->assertDatabaseHas('books', [
                'id' => $targetBook->id,
            ]);
        });

        it('fails if an editor tries to delete the book', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetBook = Book::factory()->create();

            deleteJson(route('book.destroy', $targetBook))
                ->assertForbidden();
            $this->assertDatabaseHas('books', [
                'id' => $targetBook->id,
            ]);
        });

        it('allows an admin to delete the book and its image', function () {
            Storage::fake('books');
            $filename = 'test-image';

            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetBook = Book::factory()->withImage($filename)->create();

            Storage::disk('books')->put($filename, 'fake');

            deleteJson(route('book.destroy', $targetBook))
                ->assertNoContent();
            $this->assertDatabaseMissing('books', [
                'id' => $targetBook->id,
            ]);
            Storage::disk('books')->assertMissing($filename);
        });

        it('allows a super-admin to delete the book and its image', function () {
            Storage::fake('books');
            $filename = 'test-image';

            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetBook = Book::factory()->withImage($filename)->create();

            Storage::disk('books')->put($filename, 'fake');

            deleteJson(route('book.destroy', $targetBook))
                ->assertNoContent();
            $this->assertDatabaseMissing('books', [
                'id' => $targetBook->id,
            ]);
            Storage::disk('books')->assertMissing($filename);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the book does not exist', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            deleteJson(route('book.destroy', 999))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when the book is deleted', function () {
            $admin = User::factory()->admin()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($admin);

            Cache::tags(['book'])->put('books', 'test_value', config('cache.ttl.books'));
            expect(Cache::tags(['book'])->get('books'))->toBe('test_value');
            Cache::tags(['author'])->put('authors', 'test_value', config('cache.ttl.authors'));
            expect(Cache::tags(['author'])->get('authors'))->toBe('test_value');
            deleteJson(route('book.destroy', $targetBook))
                ->assertNoContent();
            expect(Cache::tags(['book'])->get('books'))->toBeNull()
                ->and(Cache::tags(['author'])->get('authors'))->toBeNull();
        });
    });
})->group('book');
