<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\FeatureController as AdminFeatureController;
use App\Http\Controllers\Api\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Api\Admin\StatsController;
use App\Http\Controllers\Api\FeatureController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\VoteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    // Public endpoints
    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/{project:slug}/features', [FeatureController::class, 'indexByProject']);
    
    Route::post('projects/{project:slug}/features', [FeatureController::class, 'store'])
        ->middleware('throttle:feature-submissions');
    
    Route::get('features/{feature}', [FeatureController::class, 'show']);
    
    Route::post('features/{feature}/vote', [VoteController::class, 'store'])
        ->middleware('throttle:feature-votes');
    
    Route::delete('features/{feature}/vote', [VoteController::class, 'destroy'])
        ->middleware('throttle:feature-votes');

    // Admin endpoints
    Route::middleware('admin.api')->prefix('admin')->group(function () {
        // Project management
        Route::post('projects', [AdminProjectController::class, 'store']);
        Route::patch('projects/{project}', [AdminProjectController::class, 'update']);

        // Feature management
        Route::patch('features/{feature}', [AdminFeatureController::class, 'update']);
        Route::delete('features/{feature}', [AdminFeatureController::class, 'destroy']);

        // Statistics
        Route::get('stats', [StatsController::class, 'index']);
    });
});
