<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AddressingController;
use App\Http\Controllers\Api\ReportingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1/addresses')->group(function () {
    Route::get('/reverse', [AddressingController::class, 'reverse']);
    Route::get('/search', [AddressingController::class, 'search']);
});

Route::prefix('v1/reports')->group(function () {
    Route::get('/', [ReportingController::class, 'index']);
    Route::post('/', [ReportingController::class, 'store']);
});
