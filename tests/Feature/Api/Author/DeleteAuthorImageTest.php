<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Author;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('AuthorImageController -> destroy', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('authors');
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete the author image', function () {
            $targetAuthor = Author::factory()->withImage()->create();
            Storage::disk('authors')->put($targetAuthor->image_path, 'fake');

            deleteJson(route('author.image.destroy', $targetAuthor))
                ->assertForbidden();
            $targetAuthor->refresh();
            expect($targetAuthor->image_path)->not()->toBeNull();
            Storage::disk('authors')->assertExists($targetAuthor->image_path);
        });

        it('fails if a viewer tries to delete the author image', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetAuthor = Author::factory()->withImage()->create();
            Storage::disk('authors')->put($targetAuthor->image_path, 'fake');

            deleteJson(route('author.image.destroy', $targetAuthor))
                ->assertForbidden();
            $targetAuthor->refresh();
            expect($targetAuthor->image_path)->not()->toBeNull();
            Storage::disk('authors')->assertExists($targetAuthor->image_path);
        });

        it('fails if an editor tries to delete the author image', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetAuthor = Author::factory()->withImage()->create();
            Storage::disk('authors')->put($targetAuthor->image_path, 'fake');

            deleteJson(route('author.image.destroy', $targetAuthor))
                ->assertNoContent();
            $targetAuthor->refresh();
            expect($targetAuthor->image_path)->toBeNull();
            Storage::disk('authors')->assertMissing($targetAuthor->image_path);
        });

        it('allows an admin to delete the author image', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetAuthor = Author::factory()->withImage()->create();
            Storage::disk('authors')->put($targetAuthor->image_path, 'fake');

            deleteJson(route('author.image.destroy', $targetAuthor))
                ->assertNoContent();
            $targetAuthor->refresh();
            expect($targetAuthor->image_path)->toBeNull();
            Storage::disk('authors')->assertMissing($targetAuthor->image_path);
        });

        it('allows a super-admin to delete the author image', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetAuthor = Author::factory()->withImage()->create();
            Storage::disk('authors')->put($targetAuthor->image_path, 'fake');

            deleteJson(route('author.image.destroy', $targetAuthor))
                ->assertNoContent();
            $targetAuthor->refresh();
            expect($targetAuthor->image_path)->toBeNull();
            Storage::disk('authors')->assertMissing($targetAuthor->image_path);
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

            deleteJson(route('author.image.destroy', 999))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when the author image is deleted', function () {
            $admin = User::factory()->admin()->create();
            $targetAuthor = Author::factory()->withImage()->create();
            Sanctum::actingAs($admin);

            Cache::tags(['author'])->put('authors', 'test_value', config('cache.ttl.authors'));
            expect(Cache::tags(['author'])->get('authors'))->toBe('test_value');
            Cache::tags(['book'])->put('books', 'test_value', config('cache.ttl.books'));
            expect(Cache::tags(['book'])->get('books'))->toBe('test_value');
            deleteJson(route('author.image.destroy', $targetAuthor))
                ->assertNoContent();
            expect(Cache::tags(['author'])->get('authors'))->toBeNull()
                ->and(Cache::tags(['book'])->get('books'))->toBeNull();
        });
    });
})->group('author');
