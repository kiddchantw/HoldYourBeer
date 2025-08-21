# Multilingual Switching Design Document

## 1. Overview

This document details the technical design for implementing a multilingual language switching feature in the "HoldYourBeer" application. The initial implementation will support two languages: English (`en`) and Traditional Chinese (`zh_TW`). The design is based on the requirements specified in `spec/features/multilingual_switching.feature`.

## 2. Implementation Strategy

We will leverage Laravel's built-in localization features.

### 2.1. Language Files

- Language strings will be stored in JSON files within the `lang` directory (e.g., `lang/en.json`, `lang/zh_TW.json`).
- We will use key-value pairs for translation strings. Example:
  ```json
  // lang/en.json
  {
    "Welcome to HoldYourBeer": "Welcome to HoldYourBeer",
    "Dashboard": "Dashboard"
  }
  
  // lang/zh_TW.json
  {
    "Welcome to HoldYourBeer": "歡迎來到 HoldYourBeer",
    "Dashboard": "儀表板"
  }
  ```

### 2.2. Middleware for Language Detection

- A new middleware, `app/Http/Middleware/SetLocale.php`, will be created.
- This middleware will be responsible for setting the application's locale for each incoming request.
- The middleware will be added to the `web` middleware group in `app/Http/Kernel.php`.

**Locale Detection Logic (in order of priority):**
1.  **Session:** Check if a `locale` variable is set in the user's session. This allows us to remember the user's preference.
2.  **Authenticated User Preference:** (Future enhancement) Check if the authenticated user has a `preferred_language` setting in their profile.
3.  **Browser Language:** Inspect the `Accept-Language` header from the browser.
4.  **Default Fallback:** If none of the above are present, use the default locale defined in `config/app.php` (`en`).

### 2.3. Language Switcher Component

- A new Blade component, `resources/views/components/language-switcher.blade.php`, will be created for the UI.
- This component will display the current language and provide links to switch to other available languages.
- The switcher will be a dropdown menu.

### 2.4. Route for Switching Language

- A new route, `GET /language/{locale}`, will be created in `routes/web.php`.
- This route will be handled by a new method in a controller (e.g., `LocaleController@switch`).
- The controller method will validate the requested `{locale}`, store it in the session (`session(['locale' => $locale])`), and then redirect the user back to their previous page.

## 3. Frontend Implementation

- In Blade templates, we will use Laravel's `__('key')` helper function to display translated strings.
  - Example: `<h1>{{ __('Welcome to HoldYourBeer') }}</h1>`
- The language switcher component will be included in the main navigation bar (`resources/views/layouts/navigation.blade.php`).

## 4. Database Considerations

- For now, user-generated content (e.g., beer names, tasting notes) will **not** be translated. The multilingual feature will only apply to the application's UI text.
- A `preferred_language` column could be added to the `users` table in the future to permanently store a user's language choice.

## 5. Testing Strategy

- A new feature test, `tests/Feature/MultilingualSwitchingTest.php`, will be created.
- This test will:
  - Assert that the language middleware correctly sets the locale based on session and browser headers.
  - Test the `/language/{locale}` route to ensure it correctly updates the session and redirects.
  - Assert that pages display content in the correct language by checking for specific translated strings.
