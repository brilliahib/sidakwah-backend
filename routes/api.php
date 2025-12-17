<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ModulController;
use App\Http\Controllers\SubModulController;
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

    // Sub-module routes
    Route::prefix('sub-modules')->group(function () {
        Route::get('/modul/{modulId}', [SubModulController::class, 'getSubModulesByModul']);
        Route::get('/{id}', [SubModulController::class, 'show']);
    });

    Route::middleware('role:admin')->group(function () {
        // Module routes for admin
        Route::prefix('modules')->group(function () {
            Route::post('/', [ModulController::class, 'store']);
            Route::put('/{id}', [ModulController::class, 'update']);
            Route::delete('/{id}', [ModulController::class, 'destroy']);
        });

        // Sub-module routes for admin
        Route::prefix('sub-modules')->group(function () {
            Route::get('/', [SubModulController::class, 'index']);
            Route::post('/', [SubModulController::class, 'store']);
            Route::put('/{id}', [SubModulController::class, 'update']);
            Route::delete('/{id}', [SubModulController::class, 'destroy']);
        });
    });
});
