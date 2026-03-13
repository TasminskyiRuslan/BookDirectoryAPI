<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('UserController -> index', function () {

    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | filters & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {

        it('filters users by search string in name or email', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            $john = User::factory()->create(['name' => 'John Doe', 'email' => 'john@gmail.com']);
            $jane = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@yahoo.com']);

            $searchString = substr($john->name, 5);
            getJson(route('users.index', ['filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.email', $john->email);
        });

        it('filters users exact by role', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            $viewers = User::factory()->viewer()->count(5)->create();
            $editors = User::factory()->editor()->count(3)->create();
            $admins = User::factory()->admin()->count(2)->create();

            getJson(route('users.index', ['filter[role]' => UserRole::EDITOR->value]))
                ->assertOk()
                ->assertJsonCount($editors->count(), 'data');
        });

        it('sorts users by name', function () {
            $admin = User::factory()->admin()->create(['name' => 'Admin', 'email' => 'admin@gmail.com']);

            Sanctum::actingAs($admin);

            $ben = User::factory()->create(['name' => 'Ben', 'email' => 'ben@gmail.com']);
            $frank = User::factory()->create(['name' => 'Frank', 'email' => 'frank@gmail.com']);

            getJson(route('users.index', ['sort' => 'name']))
                ->assertOk()
                ->assertJsonPath('data.0.name', $admin->name)
                ->assertJsonPath('data.1.name', $ben->name)
                ->assertJsonPath('data.2.name', $frank->name);

            getJson(route('users.index', ['sort' => '-name']))
                ->assertOk()
                ->assertJsonPath('data.0.name', $frank->name)
                ->assertJsonPath('data.1.name', $ben->name)
                ->assertJsonPath('data.2.name', $admin->name);
        });

        it('sorts users by created_at', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            $ben = User::factory()->create(['name' => 'Ben', 'email' => 'ben@gmail.com']);
            $ben->setCreatedAt(now()->subDays(2))->save();

            $frank = User::factory()->create(['name' => 'Frank', 'email' => 'frank@gmail.com']);
            $frank->setCreatedAt(now()->subDay())->save();

            getJson(route('users.index', ['sort' => 'created_at']))
                ->assertOk()
                ->assertJsonPath('data.0.email', $ben->email)
                ->assertJsonPath('data.1.email', $frank->email)
                ->assertJsonPath('data.2.email', $admin->email);

            getJson(route('users.index', ['sort' => '-created_at']))
                ->assertOk()
                ->assertJsonPath('data.0.email', $admin->email)
                ->assertJsonPath('data.1.email', $frank->email)
                ->assertJsonPath('data.2.email', $ben->email);
        });

        it('returns empty data when no users match search', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            $users = User::factory()->count(7)->create();

            getJson(route('users.index', ['filter[search]' => 'non-existent-name-123']))
                ->assertOk()
                ->assertJsonCount(0, 'data');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {

        it('fails if user is not authenticated', function () {
             getJson(route('users.index'))
                ->assertUnauthorized();
        });

        it('fails if user does not have user.index permission (viewer)', function () {
            $viewer = User::factory()->viewer()->create();

            Sanctum::actingAs($viewer);

            getJson(route('users.index'))
                ->assertForbidden();
        });

        it('fails if user does not have user.index permission (editor)', function () {
            $editor = User::factory()->editor()->create();

            Sanctum::actingAs($editor);

            getJson(route('users.index'))
                ->assertForbidden();
        });

        it('allows if user has user.index permission (admin)', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            $users = User::factory()->count(7)->create();

            getJson(route('users.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
        });

        it('allows if user has user.index permission (super-admin)', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);

            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);


            Sanctum::actingAs($superAdmin);

            $users = User::factory()->count(7)->create();

            getJson(route('users.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ]);
        });
    });
})->group('user');
