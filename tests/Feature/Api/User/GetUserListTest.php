<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
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
            getJson(route('user.index', ['filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment(['id' => $john->id]);
        });

        it('filters users exact by role', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            $viewers = User::factory()->viewer()->count(5)->create();
            $editors = User::factory()->editor()->count(3)->create();
            $admins = User::factory()->admin()->count(2)->create();

            getJson(route('user.index', ['filter[role]' => UserRole::EDITOR->value]))
                ->assertOk()
                ->assertJsonCount($editors->count(), 'data');
        });

        it('sorts users by created_at (desc) by default', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            $oldUser = User::factory()->create();
            $oldUser->setCreatedAt(now()->subDays(2))->save();

            $newUser = User::factory()->create();
            $newUser->setCreatedAt(now()->subDay())->save();

            getJson(route('user.index'))
                ->assertOk()
                ->assertJsonFragment(['id' => $admin->id])
                ->assertJsonFragment(['id' => $newUser->id])
                ->assertJsonFragment(['id' => $oldUser->id]);
        });

        it('sorts users by name (asc and desc)', function () {
            $admin = User::factory()->admin()->create(['name' => 'Admin', 'email' => 'admin@gmail.com']);

            Sanctum::actingAs($admin);

            $ben = User::factory()->create(['name' => 'Ben', 'email' => 'ben@gmail.com']);
            $frank = User::factory()->create(['name' => 'Frank', 'email' => 'frank@gmail.com']);

            getJson(route('user.index', ['sort' => 'name']))
                ->assertOk()
                ->assertJsonFragment(['id' => $admin->id])
                ->assertJsonFragment(['id' => $ben->id])
                ->assertJsonFragment(['id' => $frank->id]);

            getJson(route('user.index', ['sort' => '-name']))
                ->assertOk()
                ->assertJsonFragment(['id' => $frank->id])
                ->assertJsonFragment(['id' => $ben->id])
                ->assertJsonFragment(['id' => $admin->id]);
        });

        it('sorts users by created_at (asc and desc)', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            $ben = User::factory()->create(['name' => 'Ben', 'email' => 'ben@gmail.com']);
            $ben->setCreatedAt(now()->subDays(2))->save();

            $frank = User::factory()->create(['name' => 'Frank', 'email' => 'frank@gmail.com']);
            $frank->setCreatedAt(now()->subDay())->save();

            getJson(route('user.index', ['sort' => 'created_at']))
                ->assertOk()
                ->assertJsonFragment(['id' => $ben->id])
                ->assertJsonFragment(['id' => $frank->id])
                ->assertJsonFragment(['id' => $admin->id]);

            getJson(route('user.index', ['sort' => '-created_at']))
                ->assertOk()
                ->assertJsonFragment(['id' => $admin->id])
                ->assertJsonFragment(['id' => $frank->id])
                ->assertJsonFragment(['id' => $ben->id]);
        });

        it('returns empty data when no users match search', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            $users = User::factory()->count(7)->create();

            getJson(route('user.index', ['filter[search]' => 'non-existent-name-123']))
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
             getJson(route('user.index'))
                ->assertUnauthorized();
        });

        it('fails if a viewer tries to get a list of users', function () {
            $viewer = User::factory()->viewer()->create();

            Sanctum::actingAs($viewer);

            getJson(route('user.index'))
                ->assertForbidden();
        });

        it('fails if an editor tries to get a list of users', function () {
            $editor = User::factory()->editor()->create();

            Sanctum::actingAs($editor);

            getJson(route('user.index'))
                ->assertForbidden();
        });

        it('allows an admin to get a list of users', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            $users = User::factory()->count(7)->create();

            getJson(route('user.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ])
                ->assertJsonCount($users->count() + 1, 'data');
        });

        it('allows if a super-admin to get a list of users', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);

            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);


            Sanctum::actingAs($superAdmin);

            $users = User::factory()->count(7)->create();

            getJson(route('user.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => userJsonStructure()
                    ]
                ])
                ->assertJsonCount($users->count() + 1, 'data');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | pagination
    |--------------------------------------------------------------------------
    */
    describe('pagination', function () {

        it('returns paginated a list of users', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            User::factory()->count(30)->create();

            getJson(route('user.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data',
                    'links',
                    'meta'
                ]);
        });

    });
})->group('user');
