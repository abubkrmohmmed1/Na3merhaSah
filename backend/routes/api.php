<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AddressingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SubmissionsController;
use App\Http\Controllers\Api\SpatialLookupController;
use App\Http\Controllers\Api\MobileController;

// SEC-02: Auth
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/v1/login', [AuthController::class, 'login']);
    Route::post('/v1/register', [AuthController::class, 'register']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) { return $request->user(); });
    Route::prefix('v1')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

// SEC-08: Public address
Route::middleware('throttle:30,1')->prefix('v1/addresses')->group(function () {
    Route::get('/reverse', [AddressingController::class, 'reverse']);
    Route::get('/search', [AddressingController::class, 'search']);
    Route::get('/decode', [AddressingController::class, 'decode']); // فك تشفير Plus Code / S2 Token
});

// Spatial lookup (public)
Route::middleware('throttle:60,1')->prefix('v1/spatial')->group(function () {
    Route::get('/states', [SpatialLookupController::class, 'getStates']);
    Route::get('/localities', [SpatialLookupController::class, 'getLocalities']);
    Route::get('/neighborhoods', [SpatialLookupController::class, 'getNeighborhoods']);
    Route::get('/pending-counts', [SpatialLookupController::class, 'getPendingCounts']);
    Route::get('/radius', [SpatialLookupController::class, 'radiusSearchPublic']);
});

// User submissions
Route::middleware('auth:sanctum')->prefix('v1/submissions')->group(function () {
    Route::post('/address', [SubmissionsController::class, 'submitAddress']);
    Route::post('/neighborhood', [SubmissionsController::class, 'submitNeighborhood']);
    Route::post('/village', [SubmissionsController::class, 'submitVillage']);
    Route::get('/my', [SubmissionsController::class, 'mySubmissions']);
});

// Routes split into files
require __DIR__.'/api/mobile.php';
require __DIR__.'/api/b2b.php';

// Admin review
Route::middleware(['auth:sanctum', 'ability:admin,surveyor'])->prefix('v1/admin/submissions')->group(function () {
    Route::get('/pending/count', [SubmissionsController::class, 'pendingCount']);
    Route::post('/address/{id}/approve', [SubmissionsController::class, 'approveSubmission']);
    Route::post('/address/{id}/reject', [SubmissionsController::class, 'rejectSubmission']);
    Route::post('/proposal/{id}/approve', [SubmissionsController::class, 'approveProposal']);
    Route::post('/proposal/{id}/reject', [SubmissionsController::class, 'rejectProposal']);
});
