<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use App\Models\Author;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('AuthorImageController -> update', function () {

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
        it('fails if an unauthenticated user tries to update the author image', function () {
            $targetAuthor = Author::factory()->create();

            postJson(route('author.image.update', $targetAuthor), imagePayload())
                ->assertForbidden();

            $targetAuthor->refresh();
            expect($targetAuthor->image_path)->toBeNull();
            Storage::disk('authors')->assertMissing($targetAuthor->image_path);
        });

        it('fails if a viewer tries to update the author image', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetAuthor = Author::factory()->create();

            postJson(route('author.image.update', $targetAuthor), imagePayload())
                ->assertForbidden();

            $targetAuthor->refresh();
            expect($targetAuthor->image_path)->toBeNull();
            Storage::disk('authors')->assertMissing($targetAuthor->image_path);
        });

        it('allows an editor to update the author image', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetAuthor = Author::factory()->create();

            postJson(route('author.image.update', $targetAuthor), imagePayload())
                ->assertOk()
                ->assertJsonStructure(['data' => authorJsonStructure(true)]);

            $targetAuthor->refresh();
            expect($targetAuthor->image_path)->not()->toBeNull();
            Storage::disk('authors')->assertExists($targetAuthor->image_path);
        });

        it('allows an admin to update the author image', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetAuthor = Author::factory()->create();

            postJson(route('author.image.update', $targetAuthor), imagePayload())
                ->assertOk()
                ->assertJsonStructure(['data' => authorJsonStructure(true)]);

            $targetAuthor->refresh();
            expect($targetAuthor->image_path)->not()->toBeNull();
            Storage::disk('authors')->assertExists($targetAuthor->image_path);
        });

        it('allows a super-admin to update the author image', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetAuthor = Author::factory()->create();

            postJson(route('author.image.update', $targetAuthor), imagePayload())
                ->assertOk()
                ->assertJsonStructure(['data' => authorJsonStructure(true)]);

            $targetAuthor->refresh();
            expect($targetAuthor->image_path)->not()->toBeNull();
            Storage::disk('authors')->assertExists($targetAuthor->image_path);
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

            postJson(route('author.image.update', $targetAuthor), ['_method' => 'PUT'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the image is not a file', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            postJson(route('author.image.update', $targetAuthor), imagePayload([
                'image' => 'not-a-file',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the file is not an image', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            postJson(route('author.image.update', $targetAuthor), imagePayload([
                'image' => UploadedFile::fake()->create('document.pdf'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('fails if the image exceeds the 2048KB size limit', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            postJson(route('author.image.update', $targetAuthor), imagePayload([
                'image' => UploadedFile::fake()->create('author.jpg')->size(2049),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['image']);
        });

        it('succeeds if image uploads with all allowed extensions', function (string $ext) {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            postJson(route('author.image.update', $targetAuthor), imagePayload([
                'image' => UploadedFile::fake()->image("author.$ext"),
            ]))
                ->assertOk()
                ->assertJsonStructure(['data' => authorJsonStructure(true)]);
            $targetAuthor->refresh();
            expect($targetAuthor->image_path)->not->toBeNull();
            Storage::disk('authors')->assertExists($targetAuthor->image_path);
        })->with(['jpg', 'jpeg', 'png']);

        it('fails if the author does not exist', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            postJson(route('author.image.update', 999), imagePayload())
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes the cache when an author image is updated', function () {
            $editor = User::factory()->editor()->create();
            $targetAuthor = Author::factory()->create();
            Sanctum::actingAs($editor);

            Cache::tags(['author'])->put('authors', 'test_value', config('cache.ttl.authors'));
            expect(Cache::tags(['author'])->get('authors'))->toBe('test_value');
            Cache::tags(['book'])->put('books', 'test_value', config('cache.ttl.books'));
            expect(Cache::tags(['book'])->get('books'))->toBe('test_value');
            postJson(route('author.image.update', $targetAuthor), imagePayload())
                ->assertOk();
            expect(Cache::tags(['author'])->get('authors'))->toBeNull()
                ->and(Cache::tags(['book'])->get('books'))->toBeNull();
        });
    });
})->group('author');
