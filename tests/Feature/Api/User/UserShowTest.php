<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
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

        it('fails if user is not authenticated', function () {
            $targetUser = User::factory()->viewer()->create();

            getJson(route('users.show', $targetUser))
                ->assertUnauthorized();
        });

        it('fails if user does not have user.show permission (viewer)', function () {
            $viewer = User::factory()->viewer()->create();

            Sanctum::actingAs($viewer);

            $targetUser = User::factory()->viewer()->create();

            getJson(route('users.show', $targetUser))
                ->assertForbidden();
        });

        it('fails if user does not have user.show permission (editor)', function () {
            $editor = User::factory()->editor()->create();

            Sanctum::actingAs($editor);

            $targetUser = User::factory()->viewer()->create();

            getJson(route('users.show', $targetUser))
                ->assertForbidden();
        });

        it('allows if user has user.show permission (admin)', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            $targetUser = User::factory()->viewer()->create();

            getJson(route('users.show', $targetUser))
                ->assertOk()
                ->assertJsonStructure([
                    'data' =>  userJsonStructure()
                ])
                ->assertJsonFragment(['id' => $targetUser->id]);
        });

        it('allows if user has user.show permission (super-admin)', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);

            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);

            Sanctum::actingAs($superAdmin);

            $targetUser = User::factory()->viewer()->create();

            getJson(route('users.show', $targetUser))
                ->assertOk()
                ->assertJsonStructure([
                    'data' =>  userJsonStructure()
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

        it('fails if user does not exist', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            getJson(route('users.show', 999))
                ->assertNotFound();
        });

    });
})->group('user');
