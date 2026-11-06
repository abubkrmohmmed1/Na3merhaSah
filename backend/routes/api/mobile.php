<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileController;
use App\Http\Controllers\Admin\AdminEventsController;

Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {
    Route::get('/feed', [MobileController::class, 'feed']);
    Route::get('/notifications', [MobileController::class, 'notifications']);
    Route::post('/notifications/read', [MobileController::class, 'markNotificationsRead']);
    Route::get('/profile', [MobileController::class, 'profile']);
    Route::get('/map-features', [MobileController::class, 'mapFeatures']);
    Route::get('/search', [MobileController::class, 'search']);
    Route::get('/layers', [MobileController::class, 'getLayers']);
    Route::post('/boundary-challenge', [MobileController::class, 'submitBoundaryChallenge']);
    Route::get('/places/{id}', [MobileController::class, 'placeDetail']);
    Route::post('/places', [MobileController::class, 'addPlace']);
    Route::post('/places/{id}/confirm', [MobileController::class, 'confirmPlace']);
    Route::post('/places/{id}/flag', [MobileController::class, 'flagPlace']);
    Route::post('/places/{id}/vote', [MobileController::class, 'votePlace']);
    Route::post('/places/{id}/suggest-edit', [MobileController::class, 'suggestEdit']);
    Route::post('/confirm-suggestion/{id}', [MobileController::class, 'confirmSuggestion']);
    Route::get('/leaderboard', [MobileController::class, 'leaderboard']);
    Route::get('/achievements', [MobileController::class, 'achievements']);
    Route::get('/quests', [MobileController::class, 'quests']);
    Route::post('/quests/{id}/claim', [MobileController::class, 'claimQuest']);
    Route::get('/territory/highlights', [MobileController::class, 'territoryHighlights']);
    Route::get('/territory/leaderboard/{neighborhoodId}', [MobileController::class, 'territoryLeaderboard']);
    Route::get('/events', [AdminEventsController::class, 'mobileIndex']);
    Route::get('/events/{id}/leaderboard', [AdminEventsController::class, 'mobileLeaderboard']);
});
