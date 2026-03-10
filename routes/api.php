<?php

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
});
