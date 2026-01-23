<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\User;
use App\Observers\BrandObserver;
use App\Observers\UserObserver;
use App\Services\GoogleAnalyticsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Google Analytics Service as singleton
        $this->app->singleton(GoogleAnalyticsService::class, function ($app) {
            return new GoogleAnalyticsService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default password validation rules
        // 設定預設密碼驗證規則：至少 8 個字元，必須包含英文字母與數字
        Password::defaults(function () {
            return Password::min(8)
                ->letters()     // 必須包含英文字母（不限大小寫）
                ->numbers();    // 必須包含數字
        });

        // Enforce HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // 防止在非 testing 環境執行破壞性資料庫指令
        // 這可以防止意外執行 migrate:fresh, migrate:refresh, db:wipe 等指令
        // 在 local/production 環境會拋出例外，只有 testing 環境允許這些操作
        if (! $this->app->environment('testing')) {
            DB::prohibitDestructiveCommands();
        }

        // 註冊品牌觀察者
        Brand::observe(BrandObserver::class);

        // 註冊用戶觀察者（Slack 通知）
        User::observe(UserObserver::class);

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
