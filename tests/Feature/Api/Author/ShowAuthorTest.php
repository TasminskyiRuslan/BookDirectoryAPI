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
        it('allows an unauthenticated user to show the author', function () {
            $author = Author::factory()->create();

            getJson(route('author.show', $author))
                 ->assertOk()
                 ->assertJsonStructure([
                     'data' => authorJsonStructure()
                 ])
                 ->assertJsonFragment(['id' => $author->id]);
        });

        it('allows a viewer user to show the author', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $author = Author::factory()->create();

            getJson(route('author.show', $author))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => authorJsonStructure()
                ])
                ->assertJsonFragment(['id' => $author->id]);
        });

        it('allows an editor user to show the author', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $author = Author::factory()->create();

            getJson(route('author.show', $author))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => authorJsonStructure()
                ])
                ->assertJsonFragment(['id' => $author->id]);
        });

        it('allows an admin user to show the author', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $author = Author::factory()->create();

            getJson(route('author.show', $author))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => authorJsonStructure()
                ])
                ->assertJsonFragment(['id' => $author->id]);
        });

        it('allows a super-admin user to show the author', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $author = Author::factory()->create();

            getJson(route('author.show', $author))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => authorJsonStructure()
                ])
                ->assertJsonFragment(['id' => $author->id]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the author does not exist', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            getJson(route('author.show', 999))
                ->assertNotFound();
        });
    });
})->group('author');
