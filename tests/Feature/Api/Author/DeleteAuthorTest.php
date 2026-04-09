<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Author;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('AuthorController -> destroy', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete the author', function () {
            $targetAuthor = Author::factory()->create();

            deleteJson(route('author.destroy', $targetAuthor))
                ->assertForbidden();
            $this->assertDatabaseHas('authors', [
                'id' => $targetAuthor->id,
            ]);
        });

        it('fails if a viewer tries to delete the author', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetAuthor = Author::factory()->create();

            deleteJson(route('author.destroy', $targetAuthor))
                ->assertForbidden();
            $this->assertDatabaseHas('authors', [
                'id' => $targetAuthor->id,
            ]);
        });

        it('fails if an editor tries to delete the author', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetAuthor = Author::factory()->create();

            deleteJson(route('author.destroy', $targetAuthor))
                ->assertForbidden();
            $this->assertDatabaseHas('authors', [
                'id' => $targetAuthor->id,
            ]);
        });

        it('allows an admin to delete the author and its image', function () {
            Storage::fake('authors');
            $filename = 'test-image';

            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetAuthor = Author::factory()->withImage($filename)->create();

            Storage::disk('authors')->put($filename, 'fake');

            deleteJson(route('author.destroy', $targetAuthor))
                ->assertNoContent();
            $this->assertDatabaseMissing('authors', [
                'id' => $targetAuthor->id,
            ]);
            Storage::disk('authors')->assertMissing($filename);
        });

        it('allows a super-admin to delete the author and its image', function () {
            Storage::fake('authors');
            $filename = 'test-image';

            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetAuthor = Author::factory()->withImage($filename)->create();

            Storage::disk('authors')->put($filename, 'fake');

            deleteJson(route('author.destroy', $targetAuthor))
                ->assertNoContent();
            $this->assertDatabaseMissing('authors', [
                'id' => $targetAuthor->id,
            ]);
            Storage::disk('authors')->assertMissing($filename);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the author does not exist', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            deleteJson(route('author.destroy', 999))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when the author is deleted', function () {
            $admin = User::factory()->admin()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($admin);

            Cache::tags(['author'])->put('authors', 'test_value', config('cache.ttl.authors'));
            expect(Cache::tags(['author'])->get('authors'))->toBe('test_value');
            Cache::tags(['book'])->put('books', 'test_value', config('cache.ttl.books'));
            expect(Cache::tags(['book'])->get('books'))->toBe('test_value');
            deleteJson(route('author.destroy', $targetAuthor))
                ->assertNoContent();
            expect(Cache::tags(['author'])->get('authors'))->toBeNull()
                ->and(Cache::tags(['book'])->get('books'))->toBeNull();
        });
    });
})->group('author');
