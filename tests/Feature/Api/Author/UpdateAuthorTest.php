<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\Author;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('AuthorController -> update', function () {

    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to update authors', function () {
            $author = Author::factory()->create();
            $data = authorPayload();

            patchJson(route('author.update', $author), $data)
                ->assertForbidden();

            $this->assertDatabaseMissing('authors', [
                'id' => $author->id,
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('fails if a viewer user tries to update authors', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $author = Author::factory()->create();
            $data = authorPayload();

            patchJson(route('author.update', $author), $data)
                ->assertForbidden();

            $this->assertDatabaseMissing('authors', [
                'id' => $author->id,
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('allows an editor user to update authors', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $author = Author::factory()->create();
            $data = authorPayload();

            patchJson(route('author.update', $author), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => authorJsonStructure()]);

            $this->assertDatabaseHas('authors', [
                'id' => $author->id,
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('allows an admin user to update authors', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $author = Author::factory()->create();
            $data = authorPayload();

            patchJson(route('author.update', $author), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => authorJsonStructure()]);

            $this->assertDatabaseHas('authors', [
                'id' => $author->id,
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'],
            ]);
        });

        it('allows a super-admin user to update authors', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $author = Author::factory()->create();
            $data = authorPayload();

            patchJson(route('author.update', $author), $data)
                ->assertOk()
                ->assertJsonStructure(['data' => authorJsonStructure()]);

            $this->assertDatabaseHas('authors', [
                'id' => $author->id,
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
        it('fails if present fields are empty', function () {
            $editor = User::factory()->editor()->create();
            $author = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $author), authorPayload([
                'last_name' => '',
                'first_name' => '',
                'slug' => ''
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name', 'slug']);
        });

        it('fails if present fields are null', function () {
            $editor = User::factory()->editor()->create();
            $author = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $author), authorPayload([
                'last_name' => null,
                'first_name' => null,
                'slug' => null
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name', 'slug']);
        });

        it('fails if the last_name, first_name and patronymic are too short', function () {
            $editor = User::factory()->editor()->create();
            $author = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $author), authorPayload([
                'last_name' => 'A',
                'first_name' => 'B',
                'patronymic' => 'C',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name', 'patronymic']);
        });

        it('fails if the last_name, first_name, patronymic, slug and biography are too long', function () {
            $editor = User::factory()->editor()->create();
            $author = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $author), authorPayload([
                'last_name' => str_repeat('A', 256),
                'first_name' => str_repeat('B', 256),
                'patronymic' => str_repeat('C', 256),
                'slug' => str_repeat('D', 256),
                'biography' => str_repeat('E', 5001),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['last_name', 'first_name', 'patronymic', 'slug', 'biography']);
        });

        it('fails if the birth_date and death_date format are invalid', function () {
            $editor = User::factory()->editor()->create();
            $author = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $author), authorPayload([
                'birth_date' => 'invalid-date',
                'death_date' => 'invalid-date',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['birth_date', 'death_date']);
        });

        it('fails if the birth_date and death_date are in the future', function () {
            $editor = User::factory()->editor()->create();
            $author = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $author), authorPayload([
                'birth_date' => now()->addDay()->format('Y-m-d'),
                'death_date' => now()->addDay()->format('Y-m-d'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['birth_date', 'death_date']);
        });

        it('fails if death_date is before birth_date', function () {
            $editor = User::factory()->editor()->create();
            $author = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $author), authorPayload([
                'birth_date' => now()->subDay()->format('Y-m-d'),
                'death_date' => now()->subDays(2)->format('Y-m-d'),
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['death_date']);
        });

        it('fails if slug is taken by another author', function () {
            $editor = User::factory()->editor()->create();
            $author = Author::factory()->create(['slug' => 'my-slug']);
            $otherAuthor = Author::factory()->create(['slug' => 'taken-slug']);
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $author), authorPayload([
                'slug' => $otherAuthor->slug,
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('fails if slug format is invalid', function () {
            $editor = User::factory()->editor()->create();
            $author = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $author), authorPayload([
                'slug' => 'Invalid Slug!'
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['slug']);
        });

        it('succeeds if slug remains the same (ignore current)', function () {
            $editor = User::factory()->editor()->create();
            $author = Author::factory()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', $author), [
                'slug' => $author->slug,
            ])
                ->assertOk()
                ->assertJsonFragment(['slug' => $author->slug]);
        });

        it('fails if the author does not exist', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);

            patchJson(route('author.update', 999))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('flushes cache if author is updated', function () {
            $editor = User::factory()->editor()->create();
            $author = Author::factory()->create();
            Sanctum::actingAs($editor);

            Cache::tags(['author'])->put('authors', 'test_value', config('cache.ttl.authors'));
            expect(Cache::tags(['author'])->get('authors'))->toBe('test_value');
            patchJson(route('author.update', $author), authorPayload([
                'last_name' => 'Franko'
            ]))->assertOk();
            expect(Cache::tags(['author'])->get('authors'))->toBeNull();
        });
    });
})->group('author');
