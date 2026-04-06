<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('UserController -> show', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user attempts to retrieve the user', function () {
            $targetUser = User::factory()->viewer()->create();

            getJson(route('user.show', $targetUser))
                ->assertUnauthorized();
        });

        it('fails if a viewer attempts to retrieve the user', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetUser = User::factory()->viewer()->create();

            getJson(route('user.show', $targetUser))
                ->assertForbidden();
        });

        it('fails if an editor attempts to retrieve the user', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetUser = User::factory()->viewer()->create();

            getJson(route('user.show', $targetUser))
                ->assertForbidden();
        });

        it('allows an admin to retrieve the user', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->viewer()->create();

            getJson(route('user.show', $targetUser))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => userJsonStructure()
                ])
                ->assertJsonFragment(['id' => $targetUser->id]);
        });

        it('allows a super-admin to retrieve the user', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetUser = User::factory()->viewer()->create();

            getJson(route('user.show', $targetUser))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => userJsonStructure()
                ])
                ->assertJsonFragment(['id' => $targetUser->id]);
        });
    });

   /*
   |--------------------------------------------------------------------------
   | validation
   |--------------------------------------------------------------------------
   */
    describe('validation', function () {
        it('fails if the user does not exist', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            getJson(route('user.show', 999))
                ->assertNotFound();
        });
    });
})->group('user');
