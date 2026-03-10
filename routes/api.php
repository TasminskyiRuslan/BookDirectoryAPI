<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
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
