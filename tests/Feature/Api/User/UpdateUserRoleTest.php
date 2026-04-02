<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

describe('UpdateUserRoleController', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if an unauthenticated user tries to update a user\'s role', function () {
            $targetUser = User::factory()->viewer()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::EDITOR->value])
                ->assertUnauthorized();
        });

        it('fails if a viewer tries to update a user\'s role', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetUser = User::factory()->viewer()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::EDITOR->value])
                ->assertForbidden();
        });

        it('fails if an editor tries to update a user\'s role', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetUser = User::factory()->viewer()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::EDITOR->value])
                ->assertForbidden();
        });

        it('fails if an admin tries to update an admin\'s role', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->admin()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::EDITOR->value])
                ->assertForbidden();
        });

        it('fails if an admin tries to update a super-admin\'s role', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $targetUser->assignRole(UserRole::SUPER_ADMIN->value);

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::EDITOR->value])
                ->assertForbidden();
        });

        it('fails if an admin tries to update his role', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            putJson(route('user.role.update', $admin), ['role' => UserRole::EDITOR->value])
                ->assertForbidden();
        });

        it('allows an admin to update a viewer\'s role', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->viewer()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::EDITOR->value])
                ->assertOk()
                ->assertJsonStructure(['data' => userJsonStructure()]);
            expect($targetUser->hasRole(UserRole::VIEWER))->toBeFalse()
                ->and($targetUser->hasRole(UserRole::EDITOR))->toBeTrue();
        });

        it('allows an admin to update an editor\'s role ', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->editor()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::ADMIN->value])
                ->assertOk()
                ->assertJsonStructure(['data' => userJsonStructure()]);
            expect($targetUser->hasRole(UserRole::EDITOR))->toBeFalse()
                ->and($targetUser->hasRole(UserRole::ADMIN))->toBeTrue();
        });

        it('allows a super-admin to update any user\'s role', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetUser = User::factory()->admin()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::EDITOR->value])
                ->assertOk()
                ->assertJsonStructure(['data' => userJsonStructure()]);
            expect($targetUser->hasRole(UserRole::ADMIN))->toBeFalse()
                ->and($targetUser->hasRole(UserRole::EDITOR))->toBeTrue();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if required fields are missing', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->viewer()->create();

            putJson(route('user.role.update', $targetUser), [])
                ->assertUnprocessable();
        });

        it('fails if a user role is invalid', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->viewer()->create();

            putJson(route('user.role.update', $targetUser), ['role' => 'invalid-role'])
                ->assertUnprocessable();
        });

        it('fails if a user role is being updated to a super-admin', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->viewer()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::SUPER_ADMIN->value])
                ->assertUnprocessable();
        });

        it('fails if a user does not exist', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            putJson(route('user.role.update', 99999), ['role' => UserRole::EDITOR->value])
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | business logic
    |--------------------------------------------------------------------------
    */
    describe('business logic', function () {
        it('does nothing when assigning same role', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->viewer()->create();

            putJson(route('user.role.update', $targetUser), ['role' => UserRole::VIEWER->value])
                ->assertOk()
                ->assertJsonStructure(['data' => userJsonStructure()]);
            expect($targetUser->hasRole(UserRole::VIEWER))->toBeTrue();
        });
    });
})->group('user');
