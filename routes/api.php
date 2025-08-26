<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'token');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Beer endpoints
    Route::get('/beers', [\App\Http\Controllers\Api\BeerController::class, 'index']);
    Route::post('/beers', [\App\Http\Controllers\Api\BeerController::class, 'store']);
    Route::post('/beers/{id}/count_actions', [\App\Http\Controllers\Api\BeerController::class, 'countAction']);
    Route::get('/beers/{id}/tasting_logs', [\App\Http\Controllers\Api\BeerController::class, 'tastingLogs']);
});