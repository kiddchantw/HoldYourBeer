<?php

use App\Http\Controllers\Api\V1\AuthController as V1AuthController;
use App\Http\Controllers\Api\V1\BeerController as V1BeerController;
use App\Http\Controllers\Api\V1\BrandController as V1BrandController;
use App\Http\Controllers\Api\V1\FeedbackController as V1FeedbackController;
use App\Http\Controllers\Api\V2\BrandController as V2BrandController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Version 1 Routes
|--------------------------------------------------------------------------
|
| Current stable API version. All new development should use versioned routes.
|
*/

Route::prefix('v1')->name('v1.')->group(function () {
    // Authentication routes with strict rate limiting
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/register', [V1AuthController::class, 'register'])->name('register');
        Route::post('/login', [V1AuthController::class, 'token'])->name('login');
    });

    // Public feedback endpoint (allows anonymous submissions)
    Route::post('/feedback', [V1FeedbackController::class, 'store'])->name('feedback.store');

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->name('user');

        Route::post('/logout', [V1AuthController::class, 'logout'])->name('logout');

        // Beer endpoints
        Route::get('/beers', [V1BeerController::class, 'index'])->name('beers.index');
        Route::post('/beers', [V1BeerController::class, 'store'])->name('beers.store');

        // Count actions with specific rate limiting
        Route::middleware('throttle:count-actions')->group(function () {
            Route::post('/beers/{id}/count_actions', [V1BeerController::class, 'countAction'])->name('beers.count_action');
        });

        Route::get('/beers/{id}/tasting_logs', [V1BeerController::class, 'tastingLogs'])->name('beers.tasting_logs');

        // Brand endpoints
        Route::get('/brands', [V1BrandController::class, 'index'])->name('brands.index');

        // Feedback endpoints (authenticated users)
        Route::get('/feedback', [V1FeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/feedback/{feedback}', [V1FeedbackController::class, 'show'])->name('feedback.show');
        Route::patch('/feedback/{feedback}', [V1FeedbackController::class, 'update'])->name('feedback.update');
        Route::delete('/feedback/{feedback}', [V1FeedbackController::class, 'destroy'])->name('feedback.destroy');

        // Admin-only feedback endpoint
        Route::get('/admin/feedback', [V1FeedbackController::class, 'adminIndex'])->name('admin.feedback.index');
    });
});

/*
|--------------------------------------------------------------------------
| API Version 2 Routes (Example)
|--------------------------------------------------------------------------
|
| Example of how to add new API versions. V2 demonstrates enhanced
| brand endpoints with pagination and search capabilities.
|
*/

Route::prefix('v2')->name('v2.')->group(function () {
    // Authentication routes inherit from V1
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/register', [V1AuthController::class, 'register'])->name('register');
        Route::post('/login', [V1AuthController::class, 'token'])->name('login');
    });

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->name('user');

        Route::post('/logout', [V1AuthController::class, 'logout'])->name('logout');

        // Beer endpoints (inherit from V1)
        Route::get('/beers', [V1BeerController::class, 'index'])->name('beers.index');
        Route::post('/beers', [V1BeerController::class, 'store'])->name('beers.store');

        Route::middleware('throttle:count-actions')->group(function () {
            Route::post('/beers/{id}/count_actions', [V1BeerController::class, 'countAction'])->name('beers.count_action');
        });

        Route::get('/beers/{id}/tasting_logs', [V1BeerController::class, 'tastingLogs'])->name('beers.tasting_logs');

        // Enhanced brand endpoints with pagination (V2 feature)
        Route::get('/brands', [V2BrandController::class, 'index'])->name('brands.index');
    });
});

/*
|--------------------------------------------------------------------------
| Legacy Non-Versioned Routes (Deprecated)
|--------------------------------------------------------------------------
|
| These routes are maintained for backward compatibility.
| All routes redirect to v1 endpoints.
|
| DEPRECATED: Will be removed in a future version.
| Please update your client to use /api/v1/* endpoints.
|
*/

Route::middleware('api.deprecation')->group(function () {
    // Authentication routes with strict rate limiting
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/register', [V1AuthController::class, 'register']);
        Route::post('/login', [V1AuthController::class, 'token']);
    });

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/logout', [V1AuthController::class, 'logout']);

        // Beer endpoints
        Route::get('/beers', [V1BeerController::class, 'index']);
        Route::post('/beers', [V1BeerController::class, 'store']);

        // Count actions with specific rate limiting
        Route::middleware('throttle:count-actions')->group(function () {
            Route::post('/beers/{id}/count_actions', [V1BeerController::class, 'countAction']);
        });

        Route::get('/beers/{id}/tasting_logs', [V1BeerController::class, 'tastingLogs']);

        // Brand endpoints
        Route::get('/brands', [V1BrandController::class, 'index']);
    });
});