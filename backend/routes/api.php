<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AddressingController;
use App\Http\Controllers\Api\ReportingController;

Route::post('/v1/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/v1/register', [App\Http\Controllers\Api\AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('v1')->group(function () {
        Route::get('/reports', [ReportingController::class, 'index']);
        Route::post('/reports', [ReportingController::class, 'store']);
    });
});

Route::prefix('v1/addresses')->group(function () {
    Route::get('/reverse', [AddressingController::class, 'reverse']);
    Route::get('/search', [AddressingController::class, 'search']);
});
