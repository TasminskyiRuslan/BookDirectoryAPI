<?php

use App\Enums\UserRole;
use App\Models\Author;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('AuthorController -> index', function () {
    beforeEach(function () {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Cache::flush();
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | filters & sorting
    |--------------------------------------------------------------------------
    */
    describe('filters & sorting', function () {
        it('filters authors by search string', function () {
            $author1 = Author::factory()->create(['last_name' => 'Shevchenko', 'first_name' => 'Taras', 'patronymic' => 'Grigorovich']);
            $author2 = Author::factory()->create(['last_name' => 'Bondar', 'first_name' => 'Andrii']);
            $searchString = substr($author1->last_name, 3);

            getJson(route('author.index', ['filter[search]' => $searchString]))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment(['id' => $author1->id]);
        });

        it('sorts authors by created_at (desc) by default', function () {
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
            $author1 = Author::factory()->create(['last_name' => 'Shevchenko', 'first_name' => 'Taras', 'patronymic' => 'Grigorovich']);
            $author2 = Author::factory()->create(['last_name' => 'Bondar', 'first_name' => 'Andrii']);
            $author3 = Author::factory()->create(['last_name' => 'Shevchenko', 'first_name' => 'Natalia']);

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

        it('returns empty data when no authors match search', function () {
            Author::factory()->count(7)->create();

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

        it('allows a viewer to get a list of authors', function () {
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

        it('allows an editor to get a list of authors', function () {
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

        it('allows an admin to get a list of authors', function () {
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

        it('allows a super-admin to get a list of authors', function () {
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
    | caching
    |--------------------------------------------------------------------------
    */
    describe('caching', function () {
        it('stores the author list in cache after the first request', function () {
            Cache::spy();
            $viewer = User::factory()->viewer()->create();
            Sanctum::actingAs($viewer);

            getJson(route('author.index'))->assertOk();
            Cache::shouldHaveReceived('tags')
                ->with(['author'])
                ->once();
        });

        it('returns data from cache instead of the database on subsequent requests', function () {
            $oldLastname = 'Shevchenko';
            Author::factory()->create(['last_name' => $oldLastname]);

            getJson(route('author.index'))->assertOk();

            $newLastname = 'Franko';
            DB::table('authors')->update(['last_name' => $newLastname]);

            getJson(route('author.index'))
                ->assertOk()
                ->assertJsonFragment(['last_name' => $oldLastname])
                ->assertJsonMissing(['last_name' => $newLastname]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | pagination
    |--------------------------------------------------------------------------
    */
    describe('pagination', function () {
        it('returns a paginated list of authors', function () {
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
