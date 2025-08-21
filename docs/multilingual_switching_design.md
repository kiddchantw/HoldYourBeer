# Multilingual Switching Design Document

## 1. Overview

This document details the technical design for implementing a multilingual language switching feature in the "HoldYourBeer" application. The initial implementation will support two languages: English (`en`) and Traditional Chinese (`zh_TW`). The design is based on the requirements specified in `spec/features/multilingual_switching.feature`.

## 2. Implementation Strategy

We will use the `mcamara/laravel-localization` package to handle routing and locale management. This package simplifies the process of creating localized websites and aligns well with our feature requirements.

### 2.1. Package Configuration

- After installing the package, we will publish its configuration file to `config/laravellocalization.php`.
- In this file, we will define the supported locales: `en` and `zh_TW`.
- We will configure `hideDefaultLocaleInURL` to `true` to have cleaner URLs for the default language (e.g., `/dashboard` instead of `/en/dashboard`).

### 2.2. Language Files

- Language strings will continue to be stored in JSON files within the `lang` directory (e.g., `lang/en.json`, `lang/zh_TW.json`), as this is standard Laravel practice.

### 2.3. Middleware

- The package provides its own set of middleware. We will apply the `LaravelLocalizationRoutes`, `LaravelLocalizationRedirectFilter`, and `LocaleSessionRedirect` middleware to our `web` routes in `app/Http/Kernel.php`.
- This eliminates the need for a custom `SetLocale.php` middleware, as the package will handle locale detection from the URL, session, or browser headers automatically.

### 2.4. Routing

- All frontend routes in `routes/web.php` will be wrapped in a route group provided by the package.
  ```php
  // routes/web.php
  Route::group([
      'prefix' => LaravelLocalization::setLocale(),
      'middleware' => [ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath' ]
  ], function() {
      // Define all web routes here
      Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
      // ... other routes
  });
  ```
- This setup automatically handles localized URLs like `/dashboard` (for English, the default) and `/zh-TW/dashboard` (for Chinese).
- We no longer need a dedicated `GET /language/{locale}` route for switching languages.

### 2.5. Language Switcher Component

- A Blade component (`resources/views/components/language-switcher.blade.php`) will be created.
- This component will use the package's helper functions to generate the correct URLs for switching languages.
  ```blade
  // Example inside the component
  @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
      <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
          {{ $properties['native'] }}
      </a>
  @endforeach
  ```

## 3. Frontend Implementation

- We will continue to use Laravel's `__('key')` helper function in Blade templates.
- The language switcher component will be included in the main navigation (`resources/views/layouts/navigation.blade.php`) and the footer.

## 4. Database Considerations

- This remains unchanged. User-generated content will not be translated initially.

## 5. Testing Strategy

- The feature test `tests/Feature/MultilingualSwitchingTest.php` will be adapted.
- Tests will now focus on:
  - Asserting that visiting a localized URL (e.g., `/zh-TW/dashboard`) sets the application locale correctly.
  - Asserting that the language switcher component generates the correct localized URLs.
  - Verifying that the middleware correctly redirects users based on their session or browser language.
  - Testing that pages display content in the correct language.
