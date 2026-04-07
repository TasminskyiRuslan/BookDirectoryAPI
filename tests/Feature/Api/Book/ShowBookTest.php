<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Book;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('BookController -> show', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('allows an unauthenticated user to retrieve the book', function () {
            $targetBook = Book::factory()->create();

            getJson(route('book.show', $targetBook))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => bookJsonStructure(true)
                ])
                ->assertJsonFragment(['id' => $targetBook->id]);
        });

        it('allows a viewer to retrieve the book', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetBook = Book::factory()->create();

            getJson(route('book.show', $targetBook))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => bookJsonStructure(true)
                ])
                ->assertJsonFragment(['id' => $targetBook->id]);
        });

        it('allows an editor to retrieve the book', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetBook = Book::factory()->create();

            getJson(route('book.show', $targetBook))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => bookJsonStructure(true)
                ])
                ->assertJsonFragment(['id' => $targetBook->id]);
        });

        it('allows an admin to retrieve the book', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetBook = Book::factory()->create();

            getJson(route('book.show', $targetBook))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => bookJsonStructure(true)
                ])
                ->assertJsonFragment(['id' => $targetBook->id]);
        });

        it('allows a super-admin to retrieve the book', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetBook = Book::factory()->create();

            getJson(route('book.show', $targetBook))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => bookJsonStructure(true)
                ])
                ->assertJsonFragment(['id' => $targetBook->id]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if the book does not exist', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            getJson(route('book.show', 999))
                ->assertNotFound();
        });
    });
})->group('book');
