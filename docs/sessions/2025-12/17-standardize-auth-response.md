# Standardize Auth API Response

## Problem
The current authentication API (Registration/Login) returns a flat JSON structure:
```json
{
  "user": { ... },
  "token": "...",
  "refresh_token": "..."
}
```
This is inconsistent with standard REST API practices which often use a top-level `data` wrapper (and optionally `meta`). The client application expects a consistent structure for easier parsing and error handling.

## Objective
Refactor the Authentication API (Login and Register endpoints) to return a standardized JSON response format:
```json
{
  "data": {
    "user": { ... },
    "access_token": "...",  // Standardize naming if needed
    "refresh_token": "...",
    "expires_in": ...
  }
}
```

## Plan
1.  **Analyze Controllers**: Identify `AuthController` (or equivalent) handling API login/registration in `app/Http/Controllers/Api/V1`.
2.  **Refactor**: Modify the response logic to wrap the result in an arrays/resource `['data' => ...]` structure.
3.  **Update Client**: Update Flutter `AuthService` and `LoginResponse.fromJson` to handle the new structure.

## Context
- **Backend**: Laravel 12
- **Frontend**: Flutter
- **Route File**: `routes/api.php`

## Email Verification Enhancements

### Problem
- Email verification links needed to work for both web browsers and mobile apps
- Universal Links require HTTPS and registered domains, not suitable for local development
- Custom URL Schemes needed for iOS/Android deep linking in development
- Email verification endpoints were protected by authentication middleware, preventing unauthenticated users from verifying

### Solutions

#### 1. Dynamic URL Generation Based on Environment
**File**: `app/Notifications/VerifyEmailNotification.php`

- Modified `verificationUrl()` method to dynamically generate URLs based on environment and configuration
- **Local Environment**: Defaults to Custom URL Scheme (`holdyourbeer://`) unless explicitly set to `universal`
- **Production Environment**: Uses HTTPS URL (`https://holdyourbeers.com`)
- Configuration keys added to `config/app.php`:
  - `mobile_link_mode`: `'universal'` or `'scheme'`
  - `mobile_scheme`: Custom URL scheme (default: `'holdyourbeer'`)
  - `mobile_web_base`: Base URL for production (default: `'https://holdyourbeers.com'`)

**URL Format**:
- Custom URL Scheme: `holdyourbeer://en/verify-email/{id}/{hash}?expires={expires}&signature={signature}`
- HTTP/HTTPS: `{base_url}/{locale}/verify-email/{id}/{hash}?expires={expires}&signature={signature}`

#### 2. Public Email Verification Endpoints
**Files**: `routes/api.php`, `routes/web.php`

- Moved email verification routes out of authentication middleware
- API route: `/api/v1/email/verify/{id}/{hash}` - now public (removed `auth:sanctum`)
- Web route: `/{locale}/verify-email/{id}/{hash}` - now public (removed `auth` middleware)
- Added `throttle:6,1` middleware to prevent abuse

#### 3. Manual Signature Verification
**Files**: 
- `app/Http/Controllers/Api/Auth/EmailVerificationController.php`
- `app/Http/Controllers/Auth/VerifyEmailController.php`

- Implemented manual signature verification since routes are now public
- Signature calculation matches Laravel's `URL::temporarySignedRoute()` format:
  ```php
  $urlWithExpires = $routeUrl . '?expires=' . $expires;
  $expectedSignature = hash_hmac('sha256', $urlWithExpires, config('app.key'));
  ```
- Added expiration check: `time() > (int) $expires`
- Changed `User::findOrFail($id)` to `User::find($id)` with graceful 404 JSON response
- Added fallback manual update for `email_verified_at` if `markEmailAsVerified()` returns false

#### 4. Route Name Correction
- Corrected route name from `api.v1.verification.verify` to `v1.verification.verify` to match actual route definition

## Deep Link Integration Guide

此章節說明如何在 Flutter App 中整合 HoldYourBeer 的 Deep Link 功能,以處理郵件驗證和密碼重設流程。

### 概述

HoldYourBeer 使用 **Universal Links (iOS) / App Links (Android)** 策略:
- 郵件中的連結指向 Web 頁面
- 當用戶已安裝 App 時,系統會自動開啟 App
- 當用戶未安裝 App 時,則開啟 Web 頁面

**注意**: 在本地開發環境中,系統會使用 Custom URL Scheme (`holdyourbeer://`) 來替代 Universal Links,因為 Universal Links 需要 HTTPS 和已註冊的域名。

### Deep Link URL 格式

#### 1. Email Verification (郵件驗證)

**URL 格式:**
```
https://holdyourbeers.com/{locale}/verify-email/{id}/{hash}?expires={timestamp}&signature={signature}
```

**Custom URL Scheme (本地開發):**
```
holdyourbeer://{locale}/verify-email/{id}/{hash}?expires={timestamp}&signature={signature}
```

**範例:**
```
https://holdyourbeers.com/en/verify-email/1/a3f5d8e9b2c4?expires=1733400000&signature=abc123...
https://holdyourbeers.com/zh-TW/verify-email/2/b4e6d9f0c3a5?expires=1733400000&signature=def456...
holdyourbeer://en/verify-email/372/65fa9b8a14c3ad09de85da0dd259bbeac5b0be64?expires=1766045396&signature=fd540019255414816bb493d85b09da930a38cc06581c888a5e1b444b7b0d55da
```

**參數說明:**
- `{locale}`: 語系 (`en` 或 `zh-TW`)
- `{id}`: 用戶 ID
- `{hash}`: 郵件地址的 SHA1 hash
- `expires`: 連結過期時間戳 (UNIX timestamp)
- `signature`: Laravel Signed URL 簽名

**過期時間:** 60 分鐘

#### 2. Password Reset (密碼重設)

**URL 格式:**
```
https://holdyourbeers.com/{locale}/reset-password/{token}?email={email}
```

**範例:**
```
https://holdyourbeers.com/en/reset-password/abc123def456?email=user@example.com
https://holdyourbeers.com/zh-TW/reset-password/xyz789uvw012?email=user@example.com
```

**參數說明:**
- `{locale}`: 語系 (`en` 或 `zh-TW`)
- `{token}`: 密碼重設 token
- `email`: 用戶郵件地址

**過期時間:** 60 分鐘 (可在 `config/auth.php` 中設定)

### Flutter App 整合步驟

#### Step 1: 設定 Universal Links / App Links

##### iOS (Universal Links)

1. 在 Apple Developer Portal 啟用 Associated Domains
2. 在 `ios/Runner/Runner.entitlements` 加入:
```xml
<key>com.apple.developer.associated-domains</key>
<array>
    <string>applinks:holdyourbeers.com</string>
</array>
```

3. 在伺服器根目錄提供 `/.well-known/apple-app-site-association` 檔案

##### iOS (Custom URL Scheme - 本地開發)

在 `ios/Runner/Info.plist` 中加入:
```xml
<key>CFBundleURLTypes</key>
<array>
    <dict>
        <key>CFBundleTypeRole</key>
        <string>Editor</string>
        <key>CFBundleURLName</key>
        <string>com.holdyourbeer.app</string>
        <key>CFBundleURLSchemes</key>
        <array>
            <string>holdyourbeer</string>
        </array>
    </dict>
</array>
```

##### Android (App Links)

1. 在 `android/app/src/main/AndroidManifest.xml` 加入:
```xml
<intent-filter android:autoVerify="true">
    <action android:name="android.intent.action.VIEW" />
    <category android:name="android.intent.category.DEFAULT" />
    <category android:name="android.intent.category.BROWSABLE" />
    <data
        android:scheme="https"
        android:host="holdyourbeers.com" />
</intent-filter>
```

2. 在伺服器根目錄提供 `/.well-known/assetlinks.json` 檔案

#### Step 2: 在 Flutter 中處理 Deep Link

使用 `app_links` 套件處理 Deep Link (實際實作請參考 `lib/main.dart` 和 `lib/core/navigation/deep_link_handler.dart`):

```dart
import 'package:app_links/app_links.dart';

// 監聽 Deep Link
void initDeepLinks() {
  _deepLinkHandler.initialize(
    onLinkReceived: (Uri uri) {
      // Email Verification
      if (DeepLinkHandler.isEmailVerificationLink(uri)) {
        final params = DeepLinkHandler.parseEmailVerificationLink(uri);
        if (params != null) {
          // 導航到登入頁，並將驗證參數作為 query parameters 傳遞
          router.go('/login?verify_id=${params.id}&verify_hash=${params.hash}...');
        }
      }

      // Password Reset
      if (DeepLinkHandler.isPasswordResetLink(uri)) {
        final params = DeepLinkHandler.parsePasswordResetLink(uri);
        if (params != null) {
          router.go('/${params.locale}/password/reset/${params.token}?email=${params.email}');
        }
      }
    },
  );
}
```

#### Step 3: 呼叫 API 完成驗證

##### Email Verification API

**Endpoint:**
```
GET /api/v1/email/verify/{id}/{hash}?expires={expires}&signature={signature}
```

**注意**: 此端點為公開端點,不需要 Authorization header。

**Response (Success):**
```json
{
  "message": "Email verified successfully.",
  "verified": true
}
```

**Response (Already Verified):**
```json
{
  "message": "Email already verified.",
  "verified": true
}
```

**Response (Error):**
```json
{
  "message": "Invalid verification link.",
  "verified": false
}
```

##### Password Reset API

**Step 1: 發送重設連結**
```
POST /api/v1/forgot-password
Content-Type: application/json

{
  "email": "user@example.com"
}
```

**Step 2: 重設密碼**
```
POST /api/v1/reset-password
Content-Type: application/json

{
  "token": "abc123def456",
  "email": "user@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response (Success):**
```json
{
  "message": "Your password has been reset."
}
```

### 測試流程

#### 測試 Email Verification

1. 在 App 中註冊新帳號
2. 檢查郵件收件匣
3. 點擊郵件中的驗證連結
4. 確認 App 自動開啟並在登入頁顯示驗證 dialog
5. 驗證完成後,確認 dialog 顯示成功訊息

#### 測試 Password Reset

1. 在 App 中點擊「忘記密碼」
2. 輸入郵件地址
3. 檢查郵件收件匣
4. 點擊郵件中的重設連結
5. 確認 App 自動開啟重設密碼頁面
6. 輸入新密碼並提交

### 多語系支援

所有郵件內容和頁面文字都支援以下語系:
- **英文 (en)**
- **繁體中文 (zh-TW)**

URL 中的 `{locale}` 參數會根據用戶當前選擇的語系自動設定。

### 注意事項

1. **連結過期:** 所有連結都有 60 分鐘的有效期限
2. **Signed URL:** Email Verification 使用 Laravel Signed URL,需要驗證 signature
3. **一次性 Token:** Password Reset Token 使用後會失效
4. **網路要求:** API 呼叫需要網路連線
5. **錯誤處理:** 請妥善處理過期、無效或已使用的連結
6. **本地開發:** 本地環境預設使用 Custom URL Scheme,生產環境使用 Universal Links/App Links
7. **公開端點:** Email Verification API 端點為公開端點,不需要認證

### 相關文件

- [Laravel Email Verification](https://laravel.com/docs/11.x/verification)
- [Laravel Password Reset](https://laravel.com/docs/11.x/passwords)
- [iOS Universal Links](https://developer.apple.com/ios/universal-links/)
- [Android App Links](https://developer.android.com/training/app-links)
- Flutter Deep Link 實作: `HoldYourBeer-Flutter/lib/core/navigation/deep_link_handler.dart`
- Flutter Deep Link 初始化: `HoldYourBeer-Flutter/lib/main.dart`

## Files Modified
- `app/Notifications/VerifyEmailNotification.php` - Dynamic URL generation
- `app/Http/Controllers/Api/Auth/EmailVerificationController.php` - Public endpoint, manual signature verification
- `app/Http/Controllers/Auth/VerifyEmailController.php` - Public endpoint, manual signature verification, locale parameter
- `routes/api.php` - Public email verification route
- `routes/web.php` - Public email verification route, removed `signed` middleware
- `config/app.php` - Added mobile link configuration keys
