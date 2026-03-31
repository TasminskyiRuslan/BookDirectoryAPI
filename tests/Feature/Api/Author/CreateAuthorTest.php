<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\Author;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('AuthorController -> store', function () {

    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {

        it('fails if an unauthenticated user tries to create authors', function () {
            $data = creationAuthorPayload();

            postJson(route('author.store'), $data)
                ->assertUnauthorized();

            $this->assertDatabaseMissing('authors', [
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('fails if a viewer user tries to create authors', function () {
            $viewer = User::factory()->viewer()->create();

            Sanctum::actingAs($viewer);

            $data = creationAuthorPayload();

            postJson(route('author.store'), $data)
                ->assertForbidden();

            $this->assertDatabaseMissing('authors', [
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('allows an editor user to create authors', function () {
            $editor = User::factory()->editor()->create();

            Sanctum::actingAs($editor);

            $data = creationAuthorPayload();

            postJson(route('author.store'), $data)
                ->assertCreated()
                ->assertJsonStructure(['data' => authorJsonStructure()]);

            $this->assertDatabaseHas('authors', [
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('allows an admin user to create authors', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            $data = creationAuthorPayload();

            postJson(route('author.store'), $data)
                ->assertCreated()
                ->assertJsonStructure(['data' => authorJsonStructure()]);

            $this->assertDatabaseHas('authors', [
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('allows a super-admin user to get a list of authors', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);

            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);

            Sanctum::actingAs($superAdmin);

            $data = creationAuthorPayload();

            postJson(route('author.store'), $data)
                ->assertCreated()
                ->assertJsonStructure(['data' => authorJsonStructure()]);

            $this->assertDatabaseHas('authors', [
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {

        it('fails if required fields are missing', function () {

            $editor = User::factory()->editor()->create();

            Sanctum::actingAs($editor);

            postJson(route('author.store'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name']);
        });

        it('fails if the last_name, first_name and patronymic is too short', function () {

            $editor = User::factory()->editor()->create();

            Sanctum::actingAs($editor);

            postJson(route('author.store'), creationAuthorPayload([
                'last_name' => 'A',
                'first_name' => 'B',
                'patronymic' => 'C',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name', 'patronymic']);
        });

        it('fails if the last_name, first_name, patronymic, slug and biography is too long', function () {

            $editor = User::factory()->editor()->create();

            Sanctum::actingAs($editor);

            postJson(route('author.store'), creationAuthorPayload([
                'last_name' => str_repeat('A', 256),
                'first_name' => str_repeat('B', 256),
                'patronymic' => str_repeat('C', 256),
                'slug' => str_repeat('D', 256),
                'biography' => str_repeat('E', 5001),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name', 'patronymic', 'slug']);
        });

        it('fails if the birth_date and death_date format is invalid', function () {

            $editor = User::factory()->editor()->create();

            Sanctum::actingAs($editor);

            postJson(route('author.store'), creationAuthorPayload([
                'birth_date' => 'invalid-date',
                'death_date' => 'invalid-date',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['birth_date', 'death_date']);
        });

        it('fails if the birth_date and death_date is after today', function () {

            $editor = User::factory()->editor()->create();

            Sanctum::actingAs($editor);

            postJson(route('author.store'), creationAuthorPayload([
                'birth_date' => now()->addDay(),
                'death_date' => now()->addDay(),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['birth_date', 'death_date']);
        });

        it('fails if the death_date is before birth_date', function () {

            $editor = User::factory()->editor()->create();

            Sanctum::actingAs($editor);

            postJson(route('author.store'), creationAuthorPayload([
                'birth_date' => now()->subDay(),
                'death_date' => now()->subDays(2),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['death_date']);
        });

        it('fails if slug is not unique', function () {
            $existingAuthor = Author::factory()->create(['slug' => 'test-slug']);

            $editor = User::factory()->editor()->create();

            Sanctum::actingAs($editor);

            postJson(route('author.store'), creationAuthorPayload(['slug' => 'test-slug']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('success if slug is provided manually', function () {

            $editor = User::factory()->editor()->create();

            Sanctum::actingAs($editor);

            $slug = 'test-slug';
            postJson(route('author.store'), creationAuthorPayload(['slug' => $slug]))
                ->assertCreated()
                ->assertJsonFragment(['slug' => $slug]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes cache if author is created', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            Cache::tags(['author'])->put('authors', 'test_value', config('cache.ttl.authors'));

            expect(Cache::tags(['author'])->get('authors'))->toBe('test_value');

            postJson(route('author.store'), creationAuthorPayload([
                'last_name' => 'Franko'
            ]))->assertCreated();

            expect(Cache::tags(['author'])->get('authors'))->toBeNull();
        });
    });

})->group('author');
