<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\FeatureController as AdminFeatureController;
use App\Http\Controllers\Api\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Api\Admin\StatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Web-based voting is handled via routes/web.php using HTMX.
| Features are synced from GitHub Issues only - no public API for creation.
|
*/

Route::prefix('v1')->group(function () {
    
    // Admin endpoints only
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
