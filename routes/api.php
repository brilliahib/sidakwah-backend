<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    // Authenticated user routes
    Route::prefix('auth')->group(function () {
        Route::get('/get-auth', [AuthController::class, 'getAuth']);
    });
});
