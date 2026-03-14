<?php

use App\Enums\UserPermission;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication actions
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(callback: function () {
    // Register action
    Route::post('/register', RegisterController::class)
        ->name('auth.register');

    // Login action
    Route::post('/login', LoginController::class)
        ->name('auth.login');

    // Me action
    Route::get('/me', MeController::class)
        ->middleware('auth:sanctum')
        ->name('auth.me');

    // Logout action
    Route::delete('/logout', LogoutController::class)
        ->middleware('auth:sanctum')
        ->name('auth.logout');
});

/*
|--------------------------------------------------------------------------
| Users actions
|--------------------------------------------------------------------------
*/
Route::prefix('users')->middleware('auth:sanctum')->group(callback: function () {
    // Get users list action
    Route::get('/', [UserController::class, 'index'])
        ->middleware('can:' . UserPermission::USER_INDEX->value)
        ->name('users.index');

    // Show user action
    Route::get('/{user}', [UserController::class, 'show'])
        ->middleware('can:' . UserPermission::USER_SHOW->value)
        ->name('users.show');
});
