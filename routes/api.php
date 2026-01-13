<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MaterialContentController;
use App\Http\Controllers\ModulController;
use App\Http\Controllers\SubModulController;
use App\Http\Controllers\UserController;
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

    // Material content routes
    Route::prefix('material-contents')->group(function () {
        Route::get('/latest', [MaterialContentController::class, 'getLatestMaterials']);
        Route::get('/sub-modul/{subModulId}', [MaterialContentController::class, 'getBySubModul']);
        Route::get('/{id}', [MaterialContentController::class, 'show']);
    });

    // Comment routes
    Route::prefix('comments')->group(function () {
        Route::post('/', [CommentController::class, 'store']);
        Route::get('/material-content/{materialContentId}', [CommentController::class, 'indexByMaterialContent']);
        Route::put('/{id}', [CommentController::class, 'update']);
        Route::delete('/{id}', [CommentController::class, 'destroy']);
    });

    Route::middleware('role:admin')->group(function () {
        // User management routes for admin
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
            Route::post('/{id}/reset-password', [UserController::class, 'resetPassword']);
        });

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

        // Material content routes for admin
        Route::prefix('material-contents')->group(function () {
            Route::get('/', [MaterialContentController::class, 'index']);
            Route::post('/', [MaterialContentController::class, 'store']);
            Route::put('/{id}', [MaterialContentController::class, 'update']);
            Route::delete('/{id}', [MaterialContentController::class, 'destroy']);
        });
    });
});
