<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Book;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('BookImageController -> update', function () {

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
        it('fails if an unauthenticated user tries to update the book image', function () {
            $targetBook = Book::factory()->create();

            postJson(route('book.image.update', $targetBook), imagePayload())
                ->assertForbidden();

            $targetBook->refresh();
            expect($targetBook->image_path)->toBeNull();
            Storage::disk('books')->assertMissing($targetBook->image_path);
        });

        it('fails if a viewer tries to update the book image', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetBook = Book::factory()->create();

            postJson(route('book.image.update', $targetBook), imagePayload())
                ->assertForbidden();

            $targetBook->refresh();
            expect($targetBook->image_path)->toBeNull();
            Storage::disk('books')->assertMissing($targetBook->image_path);
        });

        it('allows an editor to update the book image', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetBook = Book::factory()->create();

            postJson(route('book.image.update', $targetBook), imagePayload())
                ->assertOk()
                ->assertJsonStructure(['data' => bookJsonStructure(true)]);

            $targetBook->refresh();
            expect($targetBook->image_path)->not()->toBeNull();
            Storage::disk('books')->assertExists($targetBook->image_path);
        });

        it('allows an admin to update the book image', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetBook = Book::factory()->create();

            postJson(route('book.image.update', $targetBook), imagePayload())
                ->assertOk()
                ->assertJsonStructure(['data' => bookJsonStructure(true)]);

            $targetBook->refresh();
            expect($targetBook->image_path)->not()->toBeNull();
            Storage::disk('books')->assertExists($targetBook->image_path);
        });

        it('allows a super-admin to update the book image', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetBook = Book::factory()->create();

            postJson(route('book.image.update', $targetBook), imagePayload())
                ->assertOk()
                ->assertJsonStructure(['data' => bookJsonStructure(true)]);

            $targetBook->refresh();
            expect($targetBook->image_path)->not()->toBeNull();
            Storage::disk('books')->assertExists($targetBook->image_path);
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

            postJson(route('book.image.update', $targetBook), ['_method' => 'PUT'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the image is not a file', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            postJson(route('book.image.update', $targetBook), imagePayload([
                'image' => 'not-a-file',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the file is not an image', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            postJson(route('book.image.update', $targetBook), imagePayload([
                'image' => UploadedFile::fake()->create('document.pdf'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the image exceeds the 2048KB size limit', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            postJson(route('book.image.update', $targetBook), imagePayload([
                'image' => UploadedFile::fake()->create('book.jpg')->size(2049),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('succeeds if image uploads with all allowed extensions', function (string $ext) {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            postJson(route('book.image.update', $targetBook), imagePayload([
                'image' => UploadedFile::fake()->image("book.$ext"),
            ]))
                ->assertOk()
                ->assertJsonStructure(['data' => bookJsonStructure(true)]);
            $targetBook->refresh();
            expect($targetBook->image_path)->not->toBeNull();
            Storage::disk('books')->assertExists($targetBook->image_path);
        })->with(['jpg', 'jpeg', 'png']);

        it('fails if the book does not exist', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            postJson(route('book.image.update', 999), imagePayload())
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when an book image is updated', function () {
            $editor = User::factory()->editor()->create();
            $targetBook = Book::factory()->create();
            Sanctum::actingAs($editor);

            Cache::tags(['book'])->put('books', 'test_value', config('cache.ttl.books'));
            expect(Cache::tags(['book'])->get('books'))->toBe('test_value');
            postJson(route('book.image.update', $targetBook), imagePayload())
                ->assertOk();
            expect(Cache::tags(['book'])->get('books'))->toBeNull();
        });
    });
})->group('book');
