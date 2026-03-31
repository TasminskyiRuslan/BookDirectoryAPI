<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('RegisterController', function () {

    beforeEach(function () {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {

        it('fails if required fields are missing', function () {
            postJson(route('auth.register'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name', 'email', 'password']);
        });

        it('fails if the name is too short', function () {
            postJson(route('auth.register'), registrationPayload(['name' => 'A']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        it('fails if the name is too long', function () {
            $longName = str_repeat('A', 256);
            postJson(route('auth.register'), registrationPayload(['name' => $longName]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        it('fails if the email is too long', function () {
            $longEmail = str_repeat('a', 256) . '@example.com';
            postJson(route('auth.register'), registrationPayload(['email' => $longEmail]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the email is already taken', function () {
            $data = registrationPayload();

            User::factory()->create(['email' => $data['email']]);

            postJson(route('auth.register'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the email format is invalid', function () {
            postJson(route('auth.register'), registrationPayload(['email' => 'invalid-email']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the password is too short', function () {
            postJson(route('auth.register'), registrationPayload([
                'password' => '123',
                'password_confirmation' => '123',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails if the password confirmation does not match', function () {
            postJson(route('auth.register'), registrationPayload([
                'password_confirmation' => 'different',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {

        it('can register a user and return an access token', function () {
            $data = registrationPayload();

            postJson(route('auth.register'), $data)
                ->assertCreated()
                ->assertJsonPath('data.user.email', $data['email'])
                ->assertJsonStructure(['data' => authJsonStructure()]);

            $user = User::whereEmail($data['email'])->first();

            expect($user)->not->toBeNull()
                ->and(Hash::check($data['password'], $user->password))->toBeTrue();
        });
    });

})->group('auth');
