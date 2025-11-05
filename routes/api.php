<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BeerController;
use App\Http\Controllers\Api\BrandController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes with strict rate limiting
Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'token']);
});

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Beer endpoints
    Route::get('/beers', [BeerController::class, 'index']);
    Route::post('/beers', [BeerController::class, 'store']);

    // Count actions with specific rate limiting
    Route::middleware('throttle:count-actions')->group(function () {
        Route::post('/beers/{id}/count_actions', [BeerController::class, 'countAction']);
    });

    Route::get('/beers/{id}/tasting_logs', [BeerController::class, 'tastingLogs']);

    // Brand endpoints
    Route::get('/brands', [BrandController::class, 'index']);
});