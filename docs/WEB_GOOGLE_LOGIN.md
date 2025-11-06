# Web Google 登入整合指南

本指南說明如何在 HoldYourBeer Web 應用程式中使用 Google 登入功能。

## 概述

Web 應用程式使用 **Laravel Socialite** 提供 Google OAuth 登入，與 Mobile App 的 Firebase Auth 是不同的實作方式。

### 架構差異

```
┌─────────────────────────────────────────────────────┐
│                  認證架構對比                         │
├─────────────────────────────────────────────────────┤
│                                                      │
│  Web Application:                                   │
│  ┌──────────────┐   ┌──────────────┐                │
│  │ Email/Pass   │   │ Google OAuth │                │
│  │ (Sanctum)    │   │ (Socialite)  │                │
│  └──────┬───────┘   └──────┬───────┘                │
│         └──────────────────┴─────────>              │
│                   Laravel Session                    │
│                                                      │
│  Mobile Application:                                │
│  ┌──────────────┐   ┌──────────────┐                │
│  │ Google Auth  │   │ Apple Auth   │                │
│  │ (Firebase)   │   │ (Firebase)   │                │
│  └──────┬───────┘   └──────┬───────┘                │
│         └──────────────────┴─────────>              │
│              Firebase ID Token → Laravel API         │
└─────────────────────────────────────────────────────┘
```

---

## 快速開始

### 1. Google Cloud Console 設定

#### 步驟 1：建立 OAuth 2.0 憑證

1. 前往 [Google Cloud Console](https://console.cloud.google.com/)
2. 選擇或建立專案
3. 啟用 **Google+ API**
4. 前往「憑證」→「建立憑證」→「OAuth 用戶端 ID」
5. 選擇應用程式類型：**網頁應用程式**
6. 填寫資訊：
   ```
   名稱: HoldYourBeer Web
   已授權的 JavaScript 來源: http://localhost (開發環境)
   已授權的重新導向 URI: http://localhost/auth/google/callback
   ```
7. 點擊「建立」
8. 複製「用戶端 ID」和「用戶端密碼」

#### 步驟 2：生產環境設定

對於生產環境，需要加入實際網域：

```
已授權的 JavaScript 來源:
  - https://holdyourbeer.com
  - https://www.holdyourbeer.com

已授權的重新導向 URI:
  - https://holdyourbeer.com/auth/google/callback
  - https://www.holdyourbeer.com/auth/google/callback
```

---

### 2. Laravel 設定

#### 更新 `.env` 檔案

```env
# Google OAuth (for Web Login)
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

**注意：**
- `GOOGLE_CLIENT_ID` 和 `GOOGLE_CLIENT_SECRET` 從 Google Cloud Console 取得
- `GOOGLE_REDIRECT_URI` 必須與 Google Console 設定的一致

#### 驗證設定

config/services.php 已包含 Google 設定（無需修改）：

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
```

---

### 3. 在登入頁面加入 Google 登入按鈕

#### 使用 Blade 元件（推薦）

```blade
<!-- resources/views/auth/login.blade.php -->
<x-google-login-button>
    使用 Google 登入
</x-google-login-button>
```

#### 自訂樣式

```blade
<x-google-login-button class="w-full py-3 text-lg">
    繼續使用 Google
</x-google-login-button>
```

#### 直接使用連結

```blade
<a href="{{ route('social.redirect', ['provider' => 'google']) }}"
   class="btn btn-google">
    <svg><!-- Google Icon --></svg>
    Sign in with Google
</a>
```

---

## 認證流程

### 完整流程圖

```
1. 用戶點擊「使用 Google 登入」
   ↓
2. 重定向到 /auth/google/redirect
   ↓
3. Laravel Socialite 重定向到 Google OAuth 頁面
   ↓
4. 用戶在 Google 授權頁面登入並同意
   ↓
5. Google 重定向回 /auth/google/callback?code=...
   ↓
6. SocialLoginController 處理回調
   ↓
7. 使用授權碼向 Google 換取用戶資訊
   ↓
8. 檢查用戶是否存在：
   a. 已存在 google_id → 直接登入
   b. email 相同但無 google_id → 綁定並登入
   c. 不存在 → 建立新用戶並登入
   ↓
9. 設定 Laravel Session
   ↓
10. 重定向到 /dashboard
```

### 帳號綁定邏輯

```php
// SocialLoginController 的邏輯

// 1. 優先查找 google_id
$user = User::where('provider', 'google')
            ->where('provider_id', $googleId)
            ->first();

// 2. 如果沒找到，查找相同 email
if (!$user) {
    $user = User::where('email', $email)->first();

    if ($user) {
        // 綁定現有帳號到 Google
        $user->google_id = $googleId;
        $user->provider = 'google';
        $user->save();
    }
}

// 3. 建立新用戶
if (!$user) {
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'google_id' => $googleId,
        'provider' => 'google',
        'password' => Hash::make(str()->random(32)),
    ]);
}
```

---

## 使用範例

### 範例 1：基本登入頁面

```blade
<!-- resources/views/auth/login.blade.php -->
<x-guest-layout>
    <div class="max-w-md mx-auto">
        <h2 class="text-2xl font-bold text-center mb-6">登入</h2>

        <!-- Traditional Login Form -->
        <form method="POST" action="{{ route('login') }}" class="mb-6">
            @csrf

            <div class="mb-4">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="mb-4">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" class="btn btn-primary w-full">
                登入
            </button>
        </form>

        <!-- Divider -->
        <div class="relative mb-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">或</span>
            </div>
        </div>

        <!-- Google Login -->
        <x-google-login-button class="w-full">
            使用 Google 登入
        </x-google-login-button>

        <!-- Register Link -->
        <p class="text-center mt-4 text-sm">
            還沒有帳號？
            <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800">
                註冊
            </a>
        </p>
    </div>
</x-guest-layout>
```

### 範例 2：多語系登入按鈕

```blade
<x-google-login-button>
    @if(app()->getLocale() === 'zh-TW')
        使用 Google 登入
    @else
        Sign in with Google
    @endif
</x-google-login-button>
```

### 範例 3：自訂錯誤處理

```blade
@if(session('error'))
    <div class="alert alert-danger mb-4">
        {{ session('error') }}
    </div>
@endif

@if($errors->has('social_login'))
    <div class="alert alert-danger mb-4">
        {{ $errors->first('social_login') }}
    </div>
@endif

<x-google-login-button class="w-full">
    使用 Google 登入
</x-google-login-button>
```

---

## 路由說明

### 可用路由

| 路由名稱 | URL | 說明 |
|---------|-----|------|
| `social.redirect` | `/auth/google/redirect` | 重定向到 Google OAuth |
| `social.callback` | `/auth/google/callback` | Google OAuth 回調 |
| `localized.social.redirect` | `/{locale}/auth/google/redirect` | 多語系重定向 |
| `localized.social.callback` | `/{locale}/auth/google/callback` | 多語系回調 |

### 使用不同路由

```blade
<!-- 使用無語系前綴路由（推薦） -->
<a href="{{ route('social.redirect', ['provider' => 'google']) }}">
    Google Login
</a>

<!-- 使用多語系路由 -->
<a href="{{ route('localized.social.redirect', ['locale' => app()->getLocale(), 'provider' => 'google']) }}">
    Google Login
</a>
```

---

## 常見問題

### Q1: 如何測試 Google 登入？

**A:** 使用 localhost 測試：

1. Google Console 設定 `http://localhost` 為已授權來源
2. 設定回調 URL: `http://localhost/auth/google/callback`
3. 確保 `.env` 中 `APP_URL=http://localhost`
4. 訪問 `http://localhost/login` 點擊 Google 登入按鈕

### Q2: 遇到「redirect_uri_mismatch」錯誤

**A:** 這是最常見的錯誤，原因是回調 URL 不匹配。

**解決方案：**
1. 檢查 `.env` 中的 `GOOGLE_REDIRECT_URI`
2. 確保與 Google Console 設定的完全一致（包含 http/https、尾部斜線）
3. 清除 Laravel 快取：`php artisan config:clear`

```bash
# 正確設定範例
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback

# 錯誤範例（多了斜線）
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback/
```

### Q3: 如何處理已登入用戶點擊 Google 登入？

**A:** 目前實作會建立新 session。如果需要防止這種情況：

```blade
@guest
    <x-google-login-button>
        使用 Google 登入
    </x-google-login-button>
@else
    <p>您已登入</p>
@endguest
```

### Q4: 如何讓 Google 用戶也能設定密碼？

**A:** Google 登入的用戶預設沒有密碼（隨機生成）。要允許設定密碼：

1. 在個人設定頁面加入「設定密碼」功能
2. 發送密碼重設郵件給用戶
3. 用戶可通過密碼重設流程設定密碼

### Q5: Web 和 Mobile 的 Google 登入資料會衝突嗎？

**A:** 不會。雖然使用不同系統：
- **Web**: 使用 `google_id` 欄位（Laravel Socialite）
- **Mobile**: 使用 `firebase_uid` 欄位（Firebase Auth）

但兩者可以綁定到同一個 email 的用戶帳號。

---

## 安全性最佳實踐

### 1. 保護 Client Secret

```bash
# ✅ 正確：使用環境變數
GOOGLE_CLIENT_SECRET=your-secret

# ❌ 錯誤：不要寫死在程式碼中
'client_secret' => 'GOCSPX-xxxxxxxxxxxxx'
```

### 2. HTTPS 生產環境

```env
# 生產環境必須使用 HTTPS
APP_URL=https://holdyourbeer.com
GOOGLE_REDIRECT_URI=https://holdyourbeer.com/auth/google/callback
```

### 3. 驗證 Email

雖然 Google 用戶已經過 Google 驗證，但建議：

```php
// SocialLoginController 中
$user = User::create([
    // ...
    'email_verified_at' => now(), // Google users are verified
]);
```

### 4. 限制回調 URL

只允許特定網域的回調：

```php
// 在 middleware 中加入驗證
public function handle($request, Closure $next)
{
    $allowedHosts = ['holdyourbeer.com', 'www.holdyourbeer.com'];

    if (!in_array($request->getHost(), $allowedHosts)) {
        abort(403);
    }

    return $next($request);
}
```

---

## 進階設定

### 自訂重定向頁面

修改 `SocialLoginController`:

```php
public function handleProviderCallback($provider)
{
    // ... existing code ...

    Auth::login($user, true);

    // 自訂重定向邏輯
    if (session()->has('intended_url')) {
        return redirect(session('intended_url'));
    }

    return redirect()->route('dashboard');
}
```

### 請求額外的 Google 權限

```php
public function redirectToProvider($provider)
{
    return Socialite::driver($provider)
        ->scopes(['profile', 'email', 'openid'])
        ->redirect();
}
```

### 儲存 Google Avatar

```php
$user = User::create([
    'name' => $socialUser->getName(),
    'email' => $socialUser->getEmail(),
    'google_id' => $socialUser->getId(),
    'avatar' => $socialUser->getAvatar(), // 新增頭像
    // ...
]);
```

---

## 疑難排解

### 檢查清單

- [ ] Google Cloud Console 已建立 OAuth 2.0 憑證
- [ ] 已授權的重定向 URI 設定正確
- [ ] `.env` 中 GOOGLE_CLIENT_ID 和 GOOGLE_CLIENT_SECRET 已設定
- [ ] GOOGLE_REDIRECT_URI 與 Google Console 設定一致
- [ ] Laravel Socialite 已安裝（`composer.json` 中有 `laravel/socialite`）
- [ ] 路由已正確設定（`/auth/google/redirect` 和 `/auth/google/callback`）
- [ ] users 表有 `google_id` 欄位

### 除錯模式

在 `SocialLoginController` 啟用詳細日誌：

```php
use Illuminate\Support\Facades\Log;

public function handleProviderCallback($provider)
{
    try {
        $socialUser = Socialite::driver($provider)->user();

        Log::info('Google OAuth Success', [
            'user_id' => $socialUser->getId(),
            'email' => $socialUser->getEmail(),
            'name' => $socialUser->getName(),
        ]);

        // ... rest of code
    } catch (\Exception $e) {
        Log::error('Google OAuth Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // ...
    }
}
```

查看日誌：`storage/logs/laravel.log`

---

## 相關文件

- [Firebase Auth 實作文件](./FIREBASE_AUTH_IMPLEMENTATION.md) - Mobile App 認證
- [Flutter 整合指南](./FLUTTER_INTEGRATION.md) - Flutter App 開發
- [Firebase Console 設定](./FIREBASE_SETUP.md) - Firebase 設定步驟
- [Laravel Socialite 官方文件](https://laravel.com/docs/socialite)
- [Google OAuth 2.0 文件](https://developers.google.com/identity/protocols/oauth2)

---

**最後更新：** 2025-11-05
