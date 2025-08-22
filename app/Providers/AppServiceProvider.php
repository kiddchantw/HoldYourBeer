<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set locale based on URL segments
        $this->app->booted(function () {
            $request = request();
            if ($request) {
                $segments = $request->segments();
                if (!empty($segments) && in_array($segments[0], ['en', 'zh-TW'])) {
                    $locale = $segments[0];
                    app()->setLocale($locale);
                    session(['locale' => $locale]);

                    // Debug log
                    \Log::info('Locale set in AppServiceProvider', [
                        'segments' => $segments,
                        'locale' => $locale,
                        'app_locale' => app()->getLocale()
                    ]);
                }
            }
        });
    }
}
