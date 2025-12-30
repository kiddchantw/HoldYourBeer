<?php

use App\Http\Controllers\BeerController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialLoginController;
use Illuminate\Support\Facades\Route;

// Cookie Consent Route (no auth required)
Route::post('/cookie-consent', [CookieConsentController::class, 'store'])->name('cookie-consent.store');

// Routes without locale prefix (default English)
Route::get('/', function () {
    // Only redirect to login if user is not authenticated
    if (!\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('localized.login', ['locale' => 'en']);
    }
    return redirect()->route('localized.dashboard', ['locale' => 'en']);
});

// Privacy Policy Route (fallback for non-localized URL)
Route::get('/privacy-policy', function () {
    return redirect()->route('localized.privacy-policy', ['locale' => app()->getLocale()]);
})->name('privacy-policy');

// Routes with locale prefix
Route::group(['prefix' => '{locale}', 'middleware' => ['setLocale'], 'where' => ['locale' => 'en|zh-TW']], function() {
    Route::get('/', function () {
        // If user is not authenticated, redirect to login
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return redirect()->route('localized.login', ['locale' => app()->getLocale()]);
        }
        return redirect()->route('localized.dashboard', ['locale' => app()->getLocale()]);
    });

    // Privacy Policy Route (支援多語系)
    Route::get('/privacy-policy', function () {
        return view('privacy-policy');
    })->name('localized.privacy-policy');

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

    // Email verification route (public, no auth required)
    // This allows users to verify email even if not logged in
    // Note: We don't use 'signed' middleware here because the signature is generated for API route
    // The controller will manually verify the signature
    Route::get('verify-email/{id}/{hash}', \App\Http\Controllers\Auth\VerifyEmailController::class)
        ->middleware('throttle:6,1')
        ->name('localized.verification.verify');

    Route::middleware(['auth.locale','auth'])->group(function () {
        Route::post('logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
            ->name('localized.logout');

        // Handle GET request to logout (perform logout and redirect to login page)
        Route::get('logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
            ->name('localized.logout.get');

        // Email verification prompt (requires auth)
        Route::get('verify-email', \App\Http\Controllers\Auth\EmailVerificationPromptController::class)
            ->name('localized.verification.notice');
        Route::post('email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('localized.verification.send');
    });

    Route::middleware(['auth.locale', 'auth'])->group(function () {
        Route::get('/charts', [ChartsController::class, 'index'])->name('charts');
        Route::get('/charts/export', [ChartsController::class, 'export'])->name('charts.export');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::put('password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');

        Route::post('/tasting/{id}/increment', [\App\Http\Controllers\TastingController::class, 'increment'])->name('tasting.increment');
        Route::post('/tasting/{id}/decrement', [\App\Http\Controllers\TastingController::class, 'decrement'])->name('tasting.decrement');
        Route::post('/tasting/{id}/count', [\App\Http\Controllers\TastingController::class, 'count'])->name('tasting.count');

        // Beer routes
        Route::get('/beers/create', [BeerController::class, 'create'])->name('beers.create');
        Route::post('/beers', [BeerController::class, 'store'])->name('beers.store');
        Route::get('/beer-history/{beerId}', [\App\Http\Controllers\TastingController::class, 'history'])
            ->whereNumber('beerId')
            ->name('beers.history');

        // Feedback submission route
        Route::post('/feedback', [\App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store');
    });

    Route::middleware(['auth', 'admin', 'setLocale'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return redirect()->route('admin.users.index', ['locale' => app()->getLocale()]);
        })->name('admin.dashboard');

        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');

        // Brand CRUD routes
        Route::resource('brands', \App\Http\Controllers\Admin\BrandController::class)
            ->names('admin.brands');

        // Brand soft delete routes
        Route::post('brands/{id}/restore', [\App\Http\Controllers\Admin\BrandController::class, 'restore'])->name('admin.brands.restore');
        Route::delete('brands/{id}/force-delete', [\App\Http\Controllers\Admin\BrandController::class, 'forceDelete'])->name('admin.brands.force-delete');

        // Feedback CRUD routes
        Route::resource('feedback', \App\Http\Controllers\Admin\FeedbackController::class)
            ->names('admin.feedback');
    });

    Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirectToProvider'])->name('localized.social.redirect');
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])->name('localized.social.callback');

    // OAuth link/unlink routes (require authentication)
    Route::middleware('auth')->group(function () {
        Route::get('/auth/{provider}/link', [SocialLoginController::class, 'linkProvider'])->name('localized.social.link');
        Route::delete('/auth/{provider}/unlink', [SocialLoginController::class, 'unlinkProvider'])->name('localized.social.unlink');
    });
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

// OAuth link/unlink routes (require authentication, without locale prefix)
Route::middleware('auth')->group(function () {
    Route::get('/auth/{provider}/link', [SocialLoginController::class, 'linkProvider'])->name('social.link');
    Route::delete('/auth/{provider}/unlink', [SocialLoginController::class, 'unlinkProvider'])->name('social.unlink');
});

// Test OAuth configuration (development only)
if (app()->environment('local')) {
    Route::get('/test-oauth', [\App\Http\Controllers\TestOAuthController::class, 'showConfig'])->name('test.oauth');
}

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
    Route::post('/tasting/{id}/increment', [\App\Http\Controllers\TastingController::class, 'increment'])->name('tasting.increment.fallback');
    Route::post('/tasting/{id}/decrement', [\App\Http\Controllers\TastingController::class, 'decrement'])->name('tasting.decrement.fallback');
    Route::post('/tasting/{id}/count', [\App\Http\Controllers\TastingController::class, 'count'])->name('tasting.count.fallback');

    // Beers
    Route::get('/beers/create', [BeerController::class, 'create'])->name('beers.create.fallback');
    Route::post('/beers', [BeerController::class, 'store'])->name('beers.store.fallback');
    Route::get('/beer-history/{beerId}', [\App\Http\Controllers\TastingController::class, 'history'])->whereNumber('beerId')->name('beers.history.fallback');
});
