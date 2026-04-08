<?php

use App\Enums\UserPermission;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Author\AuthorController;
use App\Http\Controllers\Api\Author\AuthorImageController;
use App\Http\Controllers\Api\Book\BookController;
use App\Http\Controllers\Api\User\UpdateUserRoleController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication actions
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
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
| User actions
|--------------------------------------------------------------------------
*/
Route::prefix('users')->middleware('auth:sanctum')->group(function () {
    // Get users list action
    Route::get('/', [UserController::class, 'index'])
        ->name('user.index');

    // Show user action
    Route::get('/{user}', [UserController::class, 'show'])
        ->name('user.show');

    // Delete user action
    Route::delete('/{user}', [UserController::class, 'destroy'])
        ->name('user.destroy');

    // Update user role action
    Route::put('/{user}/role', UpdateUserRoleController::class)
        ->name('user.role.update');
});

/*
|--------------------------------------------------------------------------
| Author actions
|--------------------------------------------------------------------------
*/
Route::prefix('authors')->group(function () {
    // Get authors list action
    Route::get('/', [AuthorController::class, 'index'])
        ->name('author.index');

    // Create author action
    Route::post('/', [AuthorController::class, 'store'])
        ->name('author.store');

    // Show author action
    Route::get('/{author}', [AuthorController::class, 'show'])
        ->name('author.show');

    // Update author action
    Route::patch('/{author}', [AuthorController::class, 'update'])
        ->name('author.update');

    // Delete author action
    Route::delete('/{author}', [AuthorController::class, 'destroy'])
        ->name('author.destroy');

    // Update author image action
    Route::put('/{author}/image', [AuthorImageController::class, 'update'])
        ->name('author.image.update');
});

/*
|--------------------------------------------------------------------------
| Book actions
|--------------------------------------------------------------------------
*/
Route::prefix('books')->group(function () {
    // Get books list action
    Route::get('/', [BookController::class, 'index'])
        ->name('book.index');

    // Create book action
    Route::post('/', [BookController::class, 'store'])
        ->name('book.store');

    // Show book action
    Route::get('/{book}', [BookController::class, 'show'])
        ->name('book.show');

    // Update book action
    Route::patch('/{book}', [BookController::class, 'update'])
        ->name('book.update');

    // Delete book action
    Route::delete('/{book}', [BookController::class, 'destroy'])
        ->name('book.destroy');
});
