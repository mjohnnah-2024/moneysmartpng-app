<?php

use App\Http\Controllers\Api\V1\AchievementApiController;
use App\Http\Controllers\Api\V1\RecommendationApiController;
use App\Http\Controllers\Api\V1\SafeToSpendApiController;
use App\Http\Controllers\Api\V1\SquadApiController;
use App\Http\Controllers\Api\V1\StreakApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('v1')->name('api.v1.')->group(function () {
    Route::get('safe-to-spend', SafeToSpendApiController::class)->name('safe-to-spend');
    Route::get('recommendations', RecommendationApiController::class)->name('recommendations');
    Route::get('streaks', StreakApiController::class)->name('streaks');
    Route::get('achievements', AchievementApiController::class)->name('achievements');
    Route::get('squads', SquadApiController::class)->name('squads');
});
