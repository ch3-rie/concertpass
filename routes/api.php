<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\ConcertApiController;
use App\Http\Controllers\Api\Admin\DashboardApiController;
use App\Http\Controllers\Api\Admin\UserApiController;
use App\Http\Controllers\Api\Admin\VenueApiController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::post('/login', [AuthController::class, 'login']);

// Protected API routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Admin API routes
    Route::middleware(['api_admin'])->prefix('admin')->name('api.admin.')->group(function () {
        Route::apiResource('users', UserApiController::class);
        Route::apiResource('concerts', ConcertApiController::class);
        Route::apiResource('venues', VenueApiController::class);
    });
});
