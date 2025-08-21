<?php

use App\Http\Controllers\BeerController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/charts', [ChartsController::class, 'index'])->name('charts');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/tasting/{userBeerCount}/increment', [\App\Http\Controllers\TastingController::class, 'increment'])->name('tasting.increment');
    Route::post('/tasting/{userBeerCount}/decrement', [\App\Http\Controllers\TastingController::class, 'decrement'])->name('tasting.decrement');

    // Beer routes
    Route::get('/beers/create', [BeerController::class, 'create'])->name('beers.create');
    Route::post('/beers', [BeerController::class, 'store'])->name('beers.store');
    Route::get('/beers/{beer}/history', [\App\Http\Controllers\TastingController::class, 'history'])->name('beers.history');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    Route::get('/users', [DashboardController::class, 'users'])->name('admin.users.index');
});

Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirectToProvider'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])->name('social.callback');

require __DIR__.'/auth.php';
