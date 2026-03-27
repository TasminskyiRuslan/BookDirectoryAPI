<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\Author;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('AuthorController -> index', function () {

    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | filters & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {

        it('filters authors by search string in last_name, first_name, patronymic or biography', function () {
            $viewer = User::factory()->viewer()->create();

            Sanctum::actingAs($viewer);

            $author1 = Author::factory()->create(['last_name' => 'Шевченко', 'first_name' => 'Тарас', 'patronymic' => 'Григорович']);
            $author2 = Author::factory()->create(['last_name' => 'Бондар', 'first_name' => 'Анрі']);

            $searchString = mb_substr($author1->last_name, 3);
            getJson(route('author.index', ['filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment(['id' => $author1->id]);
        });

        it('sorts authors by created_at (desc) by default', function () {
            $viewer = User::factory()->viewer()->create();

            Sanctum::actingAs($viewer);

            $oldAuthor = Author::factory()->create();
            $oldAuthor->setCreatedAt(now()->subDays(2))->save();

            $newAuthor = Author::factory()->create();
            $newAuthor->setCreatedAt(now()->subDay())->save();

            getJson(route('author.index'))
                ->assertOk()
                ->assertJsonFragment(['id' => $newAuthor->id])
                ->assertJsonFragment(['id' => $oldAuthor->id]);
        });

        it('sorts authors by full_name (asc and desc)', function () {
            $viewer = User::factory()->viewer()->create();

            Sanctum::actingAs($viewer);

            $author1 = Author::factory()->create(['last_name' => 'Шевченко', 'first_name' => 'Тарас', 'patronymic' => 'Григорович']);
            $author2 = Author::factory()->create(['last_name' => 'Бондар', 'first_name' => 'Анрі']);
            $author3 = Author::factory()->create(['last_name' => 'Шевченко', 'first_name' => 'Наталія']);

            getJson(route('author.index', ['sort' => 'full_name']))
                ->assertOk()
                ->assertJsonFragment(['id' => $author2->id])
                ->assertJsonFragment(['id' => $author3->id])
                ->assertJsonFragment(['id' => $author1->id]);

            getJson(route('author.index', ['sort' => '-full_name']))
                ->assertOk()
                ->assertJsonFragment(['id' => $author1->id])
                ->assertJsonFragment(['id' => $author3->id])
                ->assertJsonFragment(['id' => $author2->id]);
        });

        it('sorts authors by created_at (asc and desc)', function () {
            $viewer = User::factory()->viewer()->create();

            Sanctum::actingAs($viewer);

            $author1 = Author::factory()->create();
            $author1->setCreatedAt(now()->subDays(3))->save();

            $author2 = Author::factory()->create();
            $author2->setCreatedAt(now()->subDays(2))->save();

            $author3 = Author::factory()->create();
            $author3->setCreatedAt(now()->subDay())->save();

            getJson(route('author.index', ['sort' => 'created_at']))
                ->assertOk()
                ->assertJsonFragment(['id' => $author1->id])
                ->assertJsonFragment(['id' => $author2->id])
                ->assertJsonFragment(['id' => $author3->id]);

            getJson(route('author.index', ['sort' => '-created_at']))
                ->assertOk()
                ->assertJsonFragment(['id' => $author3->id])
                ->assertJsonFragment(['id' => $author2->id])
                ->assertJsonFragment(['id' => $author1->id]);
        });

        it('sorts authors by birth_date (asc and desc)', function () {
            $viewer = User::factory()->viewer()->create();

            Sanctum::actingAs($viewer);

            $author1 = Author::factory()->create(['birth_date' => now()->subYears(100)]);
            $author2 = Author::factory()->create(['birth_date' => now()->subYears(70)]);
            $author3 = Author::factory()->create(['birth_date' => now()->subYears(50)]);

            getJson(route('author.index', ['sort' => 'birth_date']))
                ->assertOk()
                ->assertJsonFragment(['id' => $author1->id])
                ->assertJsonFragment(['id' => $author2->id])
                ->assertJsonFragment(['id' => $author3->id]);

            getJson(route('author.index', ['sort' => '-birth_date']))
                ->assertOk()
                ->assertJsonFragment(['id' => $author3->id])
                ->assertJsonFragment(['id' => $author2->id])
                ->assertJsonFragment(['id' => $author1->id]);
        });

        it('sorts authors by death_date (asc and desc)', function () {
            $viewer = User::factory()->viewer()->create();

            Sanctum::actingAs($viewer);

            $author1 = Author::factory()->create(['death_date' => now()->subYears(30)]);
            $author2 = Author::factory()->create(['death_date' => now()->subYears(20)]);
            $author3 = Author::factory()->create(['death_date' => now()->subYears(10)]);

            getJson(route('author.index', ['sort' => 'death_date']))
                ->assertOk()
                ->assertJsonFragment(['id' => $author1->id])
                ->assertJsonFragment(['id' => $author2->id])
                ->assertJsonFragment(['id' => $author3->id]);

            getJson(route('author.index', ['sort' => '-death_date']))
                ->assertOk()
                ->assertJsonFragment(['id' => $author3->id])
                ->assertJsonFragment(['id' => $author2->id])
                ->assertJsonFragment(['id' => $author1->id]);
        });

        it('returns empty data when no users match search', function () {
            $viewer = User::factory()->viewer()->create();

            Sanctum::actingAs($viewer);

            $authors = Author::factory()->count(7)->create();

            getJson(route('author.index', ['filter[search]' => 'non-existent']))
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

        it('allows an unauthenticated user to get a list of authors', function () {
            $authors = Author::factory()->count(7)->create();

            getJson(route('author.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => authorJsonStructure()
                    ]
                ])
                ->assertJsonCount($authors->count(), 'data');
        });

        it('allows a viewer user to get a list of authors', function () {
            $viewer = User::factory()->viewer()->create();

            Sanctum::actingAs($viewer);

            $authors = Author::factory()->count(7)->create();

            getJson(route('author.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => authorJsonStructure()
                    ]
                ])
                ->assertJsonCount($authors->count(), 'data');
        });

        it('allows an editor user to get a list of authors', function () {
            $editor = User::factory()->editor()->create();

            Sanctum::actingAs($editor);

            $authors = Author::factory()->count(7)->create();

            getJson(route('author.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => authorJsonStructure()
                    ]
                ])
                ->assertJsonCount($authors->count(), 'data');
        });

        it('allows an admin user to get a list of authors', function () {
            $admin = User::factory()->admin()->create();

            Sanctum::actingAs($admin);

            $authors = Author::factory()->count(7)->create();

            getJson(route('author.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => authorJsonStructure()
                    ]
                ])
                ->assertJsonCount($authors->count(), 'data');
        });

        it('allows a super-admin user to get a list of authors', function () {
            $superAdmin = User::factory()->create([
                'name' => config('super-admin.name'),
                'email' => config('super-admin.email'),
                'password' => config('super-admin.password')
            ]);

            $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);

            Sanctum::actingAs($superAdmin);

            $authors = Author::factory()->count(7)->create();

            getJson(route('author.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => authorJsonStructure()
                    ]
                ])
                ->assertJsonCount($authors->count(), 'data');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | pagination
    |--------------------------------------------------------------------------
    */
    describe('pagination', function () {

        it('returns paginated a list of users', function () {
            $viewer = User::factory()->viewer()->create();

            Sanctum::actingAs($viewer);

            User::factory()->count(30)->create();

            getJson(route('author.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data',
                    'links',
                    'meta'
                ]);
        });

    });
})->group('author');
