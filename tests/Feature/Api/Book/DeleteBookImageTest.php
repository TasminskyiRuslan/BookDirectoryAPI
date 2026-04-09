<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Models\book;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('BookImageController -> destroy', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('books');
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to delete the book image', function () {
            $targetBook = book::factory()->withImage()->create();
            Storage::disk('books')->put($targetBook->image_path, 'fake');

            deleteJson(route('book.image.destroy', $targetBook))
                ->assertForbidden();
            $targetBook->refresh();
            expect($targetBook->image_path)->not()->toBeNull();
            Storage::disk('books')->assertExists($targetBook->image_path);
        });

        it('fails if a viewer tries to delete the book image', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetBook = book::factory()->withImage()->create();
            Storage::disk('books')->put($targetBook->image_path, 'fake');

            deleteJson(route('book.image.destroy', $targetBook))
                ->assertForbidden();
            $targetBook->refresh();
            expect($targetBook->image_path)->not()->toBeNull();
            Storage::disk('books')->assertExists($targetBook->image_path);
        });

        it('fails if an editor tries to delete the book image', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetBook = book::factory()->withImage()->create();
            Storage::disk('books')->put($targetBook->image_path, 'fake');

            deleteJson(route('book.image.destroy', $targetBook))
                ->assertNoContent();
            $targetBook->refresh();
            expect($targetBook->image_path)->toBeNull();
            Storage::disk('books')->assertMissing($targetBook->image_path);
        });

        it('allows an admin to delete the book image', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetBook = book::factory()->withImage()->create();
            Storage::disk('books')->put($targetBook->image_path, 'fake');

            deleteJson(route('book.image.destroy', $targetBook))
                ->assertNoContent();
            $targetBook->refresh();
            expect($targetBook->image_path)->toBeNull();
            Storage::disk('books')->assertMissing($targetBook->image_path);
        });

        it('allows a super-admin to delete the book image', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetBook = book::factory()->withImage()->create();
            Storage::disk('books')->put($targetBook->image_path, 'fake');

            deleteJson(route('book.image.destroy', $targetBook))
                ->assertNoContent();
            $targetBook->refresh();
            expect($targetBook->image_path)->toBeNull();
            Storage::disk('books')->assertMissing($targetBook->image_path);
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

            deleteJson(route('book.image.destroy', 999))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when the book image is deleted', function () {
            $admin = User::factory()->admin()->create();
            $targetBook = book::factory()->withImage()->create();
            Sanctum::actingAs($admin);

            Cache::tags(['book'])->put('books', 'test_value', config('cache.ttl.books'));
            expect(Cache::tags(['book'])->get('books'))->toBe('test_value');
            deleteJson(route('book.image.destroy', $targetBook))
                ->assertNoContent();
            expect(Cache::tags(['book'])->get('books'))->toBeNull();
        });
    });
})->group('book');
