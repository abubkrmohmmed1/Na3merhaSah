<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SegmentsController;
use App\Http\Controllers\Api\B2BOrderController;
use App\Http\Controllers\Api\SpatialLookupController;
use App\Http\Controllers\Api\AddressingController;

foreach (['v1/b2b', 'b2b'] as $prefix) {
    Route::middleware(['api.key', 'api.logger', 'tier.limit'])->prefix($prefix)->group(function () {
        Route::get('/profile', [SegmentsController::class, 'b2bProfile']);
        
        // Orders
        Route::get('/orders', [B2BOrderController::class, 'index']);
        Route::post('/orders', [B2BOrderController::class, 'store']);
        Route::get('/orders/{id}', [B2BOrderController::class, 'show']);
        Route::post('/orders/{id}/status', [B2BOrderController::class, 'updateStatus']);

        Route::get('/real-estate/property-card', [SegmentsController::class, 'propertyCard']);
        Route::get('/insurance/verify-address', [SegmentsController::class, 'verifyAddress']);
        Route::get('/insurance/risk-profile', [SegmentsController::class, 'riskProfile']);
        Route::get('/advertising/neighborhood-stats', [SegmentsController::class, 'neighborhoodStats']);

        Route::get('/spatial/states', [SpatialLookupController::class, 'getStates']);
        Route::get('/spatial/localities', [SpatialLookupController::class, 'getLocalities']);
        Route::get('/spatial/neighborhoods', [SpatialLookupController::class, 'getNeighborhoods']);
        Route::get('/spatial/radius', [SpatialLookupController::class, 'radiusSearch']);
        Route::get('/export', [SpatialLookupController::class, 'exportGeoJSON']);
        Route::post('/batch/geocode', [SpatialLookupController::class, 'batchGeocode']);
        Route::get('/addresses/reverse', [AddressingController::class, 'reverse']);
        Route::get('/addresses/search', [AddressingController::class, 'search']);
        Route::get('/addresses/decode', [AddressingController::class, 'decode']); // فك تشفير Plus Code / S2 Token
    });
}
