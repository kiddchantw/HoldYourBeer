# Multilingual Switching Design Document

## 1. Overview

This document details the technical design for implementing a multilingual language switching feature in the "HoldYourBeer" application. The initial implementation will support two languages: English (`en`) and Traditional Chinese (`zh_TW`). The design is based on the requirements specified in `spec/features/multilingual_switching.feature`.

## 2. Implementation Strategy

We will use the `mcamara/laravel-localization` package to handle routing and locale management. This package simplifies the process of creating localized websites and aligns well with our feature requirements.

### 2.1. Package Configuration

- After installing the package, we will publish its configuration file to `config/laravellocalization.php`.
- In this file, we will define the supported locales: `en` and `zh-TW`.
- We will configure `hideDefaultLocaleInURL` to `false`. This ensures that the locale is always present in the URL (e.g., `/en/dashboard`, `/zh-TW/dashboard`) for consistency.

### 2.2. Language Files

- Language strings will continue to be stored in JSON files within the `lang` directory (e.g., `lang/en.json`, `lang/zh_TW.json`), as this is standard Laravel practice.

### 2.3. Middleware

- A custom middleware `app/Http/Middleware/SetLocale.php` is used.
- This middleware is registered in `bootstrap/app.php` with the alias `setLocale`.
- It is responsible for reading the `{locale}` parameter from the URL and setting the application's locale accordingly.

### 2.4. Routing

- All frontend routes in `routes/web.php` are wrapped in a route group that includes the `{locale}` prefix.
  ```php
  // routes/web.php
  Route::group(['prefix' => '{locale}', 'middleware' => ['setLocale'], 'where' => ['locale' => 'en|zh-TW']], function() {
      // Define all web routes here
      Route::get('/dashboard', [DashboardController::class, 'index'])->name('localized.dashboard');
      // ... other routes
  });
  ```
- This setup ensures that all user-facing web pages are explicitly localized.

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
