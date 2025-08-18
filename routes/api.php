<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/sanctum/token', [AuthController::class, 'token']);

// Protected routes
use App\Http\Controllers\Api\BeerController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/beers', [BeerController::class, 'store']);
});
