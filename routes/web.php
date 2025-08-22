<?php

use App\Http\Controllers\BeerController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialLoginController;
use Illuminate\Support\Facades\Route;

// Routes without locale prefix (default English)
Route::get('/', function () {
    // Only redirect to login if user is not authenticated
    if (!\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('localized.login', ['locale' => 'en']);
    }
    return redirect()->route('localized.dashboard', ['locale' => 'en']);
});

// Routes with locale prefix
Route::group(['prefix' => '{locale}', 'middleware' => ['setLocale'], 'where' => ['locale' => 'en|zh-TW']], function() {
    Route::get('/', function () {
        // If user is not authenticated, redirect to login
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return redirect()->route('localized.login', ['locale' => app()->getLocale()]);
        }
        return redirect()->route('localized.dashboard', ['locale' => app()->getLocale()]);
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('localized.dashboard');

    // Auth routes with locale
    Route::middleware('guest')->group(function () {
        Route::get('register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])
            ->name('localized.register');
        Route::post('register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);

        Route::get('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
            ->name('localized.login');
        Route::post('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
    });

    Route::middleware(['auth.locale','auth'])->group(function () {
        Route::post('logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
            ->name('localized.logout');

        // Email verification routes (localized)
        Route::get('verify-email', \App\Http\Controllers\Auth\EmailVerificationPromptController::class)
            ->name('verification.notice');
        Route::get('verify-email/{id}/{hash}', \App\Http\Controllers\Auth\VerifyEmailController::class)
            ->middleware(['signed','throttle:6,1'])
            ->name('verification.verify');
        Route::post('email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
    });

    Route::middleware(['auth.locale', 'auth'])->group(function () {
        Route::get('/charts', [ChartsController::class, 'index'])->name('charts');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::put('password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');

        Route::post('/tasting/{userBeerCount}/increment', [\App\Http\Controllers\TastingController::class, 'increment'])->name('tasting.increment');
        Route::post('/tasting/{userBeerCount}/decrement', [\App\Http\Controllers\TastingController::class, 'decrement'])->name('tasting.decrement');

        // Beer routes
        Route::get('/beers/create', [BeerController::class, 'create'])->name('beers.create');
        Route::post('/beers', [BeerController::class, 'store'])->name('beers.store');
        Route::get('/beer-history/{beerId}', [\App\Http\Controllers\TastingController::class, 'history'])
            ->whereNumber('beerId')
            ->name('beers.history');
    });

    Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
        Route::get('/users', [DashboardController::class, 'users'])->name('admin.users.index');
    });

    Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirectToProvider'])->name('localized.social.redirect');
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])->name('localized.social.callback');
});

// Non-localized admin routes to avoid locale-collision on "/admin/*"
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard.fallback');
});

// Social login routes (without locale prefix)
Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirectToProvider'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])->name('social.callback');

// ------------------------------------------------------------------
// Fallback non-localized routes (default English) for tests/BC
// ------------------------------------------------------------------

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Auth (login/register/forgot/reset/verify/confirm) - classic routes
require __DIR__.'/auth.php';

// Authenticated area
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit.fallback');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update.fallback');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy.fallback');
    Route::put('/password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update.fallback');

    // Charts
    Route::get('/charts', [ChartsController::class, 'index'])->name('charts.fallback');

    // Tasting actions
    Route::post('/tasting/{userBeerCount}/increment', [\App\Http\Controllers\TastingController::class, 'increment'])->name('tasting.increment.fallback');
    Route::post('/tasting/{userBeerCount}/decrement', [\App\Http\Controllers\TastingController::class, 'decrement'])->name('tasting.decrement.fallback');

    // Beers
    Route::get('/beers/create', [BeerController::class, 'create'])->name('beers.create.fallback');
    Route::post('/beers', [BeerController::class, 'store'])->name('beers.store.fallback');
    Route::get('/beer-history/{beerId}', [\App\Http\Controllers\TastingController::class, 'history'])->whereNumber('beerId')->name('beers.history.fallback');
});
