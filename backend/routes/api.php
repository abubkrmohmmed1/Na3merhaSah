<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AddressingController;
use App\Http\Controllers\Api\ReportingController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;

// SEC-02: Rate limiting on auth endpoints (5 attempts per minute)
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/v1/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/v1/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('v1')->group(function () {
        Route::get('/reports', [ReportingController::class, 'index']);
        Route::post('/reports', [ReportingController::class, 'store']);
        Route::get('/reports/{id}', [ReportingController::class, 'show']);
        Route::post('/reports/{id}/feedback', [ReportingController::class, 'feedback'])->middleware('throttle:10,1');
        Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
        
        // SEC-07: Admin Routes protected by role middleware
        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('/stats', [\App\Http\Controllers\Api\AdminController::class, 'dashboard']);
        });
    });
});

// SEC-08: Public address endpoints with rate limiting
Route::middleware('throttle:30,1')->prefix('v1/addresses')->group(function () {
    Route::get('/reverse', [AddressingController::class, 'reverse']);
    Route::get('/search', [AddressingController::class, 'search']);
});
