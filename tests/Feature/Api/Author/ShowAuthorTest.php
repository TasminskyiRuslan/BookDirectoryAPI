<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Author;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('AuthorController -> show', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('allows an unauthenticated user to show an author', function () {
            $targetAuthor = Author::factory()->create();

            getJson(route('author.show', $targetAuthor))
                 ->assertOk()
                 ->assertJsonStructure([
                     'data' => authorJsonStructure()
                 ])
                 ->assertJsonFragment(['id' => $targetAuthor->id]);
        });

        it('allows a viewer to show an author', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetAuthor = Author::factory()->create();

            getJson(route('author.show', $targetAuthor))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => authorJsonStructure()
                ])
                ->assertJsonFragment(['id' => $targetAuthor->id]);
        });

        it('allows an editor to show an author', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetAuthor = Author::factory()->create();

            getJson(route('author.show', $targetAuthor))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => authorJsonStructure()
                ])
                ->assertJsonFragment(['id' => $targetAuthor->id]);
        });

        it('allows an admin to show an author', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetAuthor = Author::factory()->create();

            getJson(route('author.show', $targetAuthor))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => authorJsonStructure()
                ])
                ->assertJsonFragment(['id' => $targetAuthor->id]);
        });

        it('allows a super-admin to show an author', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetAuthor = Author::factory()->create();

            getJson(route('author.show', $targetAuthor))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => authorJsonStructure()
                ])
                ->assertJsonFragment(['id' => $targetAuthor->id]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if an author does not exist', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            getJson(route('author.show', 999))
                ->assertNotFound();
        });
    });
})->group('author');
