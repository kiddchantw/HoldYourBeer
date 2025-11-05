<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'setLocale' => \App\Http\Middleware\SetLocale::class,
            'auth.locale' => \App\Http\Middleware\AuthLocaleRedirect::class,
            'log.api' => \App\Http\Middleware\LogApiRequests::class,
            'api.deprecation' => \App\Http\Middleware\ApiDeprecation::class,
            'firebase.auth' => \App\Http\Middleware\FirebaseAuthMiddleware::class,
        ]);

        // Add global middleware for all requests
        $middleware->append(\App\Http\Middleware\AddSecurityHeaders::class);

        // Add API middleware group
        $middleware->group('api', [
            \App\Http\Middleware\LogApiRequests::class,
            'throttle:api',
        ]);

        // Set middleware priority
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\AuthLocaleRedirect::class,
            \Illuminate\Auth\Middleware\Authenticate::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
