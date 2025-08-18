<?php

use App\Http\Controllers\BeerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/tasting/{userBeerCount}/increment', [\App\Http\Controllers\TastingController::class, 'increment'])->name('tasting.increment');
    Route::post('/tasting/{userBeerCount}/decrement', [\App\Http\Controllers\TastingController::class, 'decrement'])->name('tasting.decrement');
});

require __DIR__.'/auth.php';
