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
    
    // Beer routes
    Route::resource('beers', BeerController::class);
    Route::get('/api/brands/suggestions', [BeerController::class, 'getBrandSuggestions'])->name('brands.suggestions');
    Route::get('/api/beers/suggestions', [BeerController::class, 'getBeerSuggestions'])->name('beers.suggestions');
});

require __DIR__.'/auth.php';
