<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ModulController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Public module routes
Route::get('/modules/latest', [ModulController::class, 'getLatestModuls']);

Route::middleware('auth:api')->group(function () {
    // Authenticated user routes
    Route::prefix('auth')->group(function () {
        Route::get('/get-auth', [AuthController::class, 'getAuth']);
    });

    // Module routes for users
    Route::prefix('modules')->group(function () {
        Route::get('/', [ModulController::class, 'index']);
        Route::get('/{id}', [ModulController::class, 'show']);
    });

    Route::middleware('role:admin')->group(function () {
        // Module routes for admin
        Route::prefix('modules')->group(function () {
            Route::post('/', [ModulController::class, 'store']);
            Route::put('/{id}', [ModulController::class, 'update']);
            Route::delete('/{id}', [ModulController::class, 'destroy']);
        });
    });
});
