<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('UserController -> destroy', function () {
    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails if a user is not authenticated', function () {
            $targetUser = User::factory()->viewer()->create();

            deleteJson(route('user.destroy', $targetUser))
                ->assertUnauthorized();
        });

        it('fails if a viewer tries to delete a user', function () {
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);
            $targetUser = User::factory()->viewer()->create();

            deleteJson(route('user.destroy', $targetUser))
                ->assertForbidden();
        });

        it('fails if an editor tries to delete a user', function () {
            $editor = User::factory()->editor()->create();
            Sanctum::actingAs($editor);
            $targetUser = User::factory()->viewer()->create();

            deleteJson(route('user.destroy', $targetUser))
                ->assertForbidden();
        });

        it('fails if an admin tries to delete an admin', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->admin()->create();

            deleteJson(route('user.destroy', $targetUser))
                ->assertForbidden();
        });

        it('fails if an admin tries to delete a super-admin', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $targetUser->assignRole(UserRole::SUPER_ADMIN->value);

            deleteJson(route('user.destroy', $targetUser))
                ->assertForbidden();
        });

        it('fails if a user tries to delete himself', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            deleteJson(route('user.destroy', $admin))
                ->assertForbidden();
        });

        it('allows an admin to delete a user', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->viewer()->create();

            deleteJson(route('user.destroy', $targetUser))
                ->assertNoContent();
            $this->assertDatabaseMissing('users', [
                'id' => $targetUser->id,
            ]);
        });

        it('allows a super-admin to delete a user', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetUser = User::factory()->viewer()->create();

            deleteJson(route('user.destroy', $targetUser))
                ->assertNoContent();
            $this->assertDatabaseMissing('users', [
                'id' => $targetUser->id,
            ]);
        });

        it('allows a super-admin to delete an admin', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);
            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
            Sanctum::actingAs($superAdmin);
            $targetUser = User::factory()->admin()->create();

            deleteJson(route('user.destroy', $targetUser))
                ->assertNoContent();
            $this->assertDatabaseMissing('users', [
                'id' => $targetUser->id,
            ]);
        });

        it('deletes user tokens when the user is deleted', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);
            $targetUser = User::factory()->viewer()->create();
            $targetUser->createToken('access_token');

            deleteJson(route('user.destroy', $targetUser))
                ->assertNoContent();
            $this->assertDatabaseMissing('personal_access_tokens', [
                'tokenable_id' => $targetUser->id,
            ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {

        it('fails if a user does not exist', function () {
            $admin = User::factory()->admin()->create();
            Sanctum::actingAs($admin);

            deleteJson(route('user.destroy', 999))
                ->assertNotFound();
        });
    });
})->group('user');
