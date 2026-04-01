<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('LoginController', function () {
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
            postJson(route('auth.login'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email', 'password']);
        });

        it('fails if the email is too long', function () {
            $longEmail = str_repeat('a', 256) . '@example.com';
            postJson(route('auth.login'), [
                'email' => $longEmail,
                'password' => 'password123'
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the email does not exist', function () {
            postJson(route('auth.login'), [
                'email' => 'nonexistent@example.com',
                'password' => 'password123',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the email format is invalid', function () {
            postJson(route('auth.login'), [
                'email' => 'invalid-email',
                'password' => 'password123',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails if the password is incorrect', function () {
            $user = User::factory()->create();

            postJson(route('auth.login'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('can authenticate a user and return an access token', function () {
            $password = 'password123';
            $user = User::factory()->create(['password' => $password,]);
            $data = [
                'email' => $user->email,
                'password' => $password,
            ];

            postJson(route('auth.login'), $data)
                ->assertOk()
                ->assertJsonPath('data.user.email', $data['email'])
                ->assertJsonStructure(['data' => authJsonStructure()]);
        });
    });
})->group('auth');
