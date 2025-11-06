# Firebase Authentication 整合實作文件

## 專案概述

本文件記錄 HoldYourBeer 專案整合 Firebase Authentication 的完整實作過程，提供 Google Sign-In 和 Apple Sign-In 功能給 Flutter 移動應用程式使用。

### 實作目標

- ✅ 整合 Firebase Auth 到 Laravel 後端
- ✅ 支援 Google Sign-In 和 Apple Sign-In
- ✅ 保留現有 email/password 認證系統（混合認證）
- ✅ 支援帳號綁定（同 email 自動連結）
- ✅ 整合 Firebase Cloud Messaging (FCM) 推播通知
- ✅ 提供完整的 Flutter 整合文件

### 技術棧

| 層級 | 技術 |
|------|------|
| 前端 | Flutter + Firebase Auth SDK |
| 後端 | Laravel 12 + kreait/laravel-firebase |
| 認證 | Firebase Authentication (Google, Apple) |
| 推播 | Firebase Cloud Messaging (FCM) |
| 資料庫 | MySQL (用戶資料) |

---

## 架構設計

### 整體架構圖

```
┌─────────────────────────────────────────────────────────┐
│                    Flutter App                          │
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Google       │  │ Apple        │  │ Firebase     │  │
│  │ Sign-In      │  │ Sign-In      │  │ Auth SDK     │  │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘  │
│         │                 │                 │           │
│         └─────────────────┴─────────────────┘           │
│                           │                              │
└───────────────────────────┼──────────────────────────────┘
                            │ ID Token
                            ▼
┌─────────────────────────────────────────────────────────┐
│                   Firebase Services                     │
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Auth         │  │ FCM          │  │ Analytics    │  │
│  │ (驗證)       │  │ (推播)       │  │ (分析)       │  │
│  └──────┬───────┘  └──────────────┘  └──────────────┘  │
└─────────┼────────────────────────────────────────────────┘
          │ Token Verification
          ▼
┌─────────────────────────────────────────────────────────┐
│              Laravel API Backend                        │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │ FirebaseAuthMiddleware                           │   │
│  │ - 驗證 Firebase ID Token                         │   │
│  │ - 解析用戶資訊                                    │   │
│  └──────────────┬───────────────────────────────────┘   │
│                 ▼                                        │
│  ┌──────────────────────────────────────────────────┐   │
│  │ FirebaseAuthService                              │   │
│  │ - verifyIdToken()                                │   │
│  │ - findOrCreateUser()                             │   │
│  │ - updateFcmToken()                               │   │
│  └──────────────┬───────────────────────────────────┘   │
│                 ▼                                        │
│  ┌──────────────────────────────────────────────────┐   │
│  │ FirebaseAuthController                           │   │
│  │ - login()                                        │   │
│  │ - me()                                           │   │
│  │ - updateFcmToken()                               │   │
│  │ - logout()                                       │   │
│  └──────────────┬───────────────────────────────────┘   │
└─────────────────┼────────────────────────────────────────┘
                  ▼
┌─────────────────────────────────────────────────────────┐
│                    MySQL Database                       │
│                                                          │
│  users table:                                           │
│  - id                                                    │
│  - name                                                  │
│  - email                                                 │
│  - password (nullable for Firebase users)               │
│  - firebase_uid (unique)                                │
│  - provider (google/apple/email)                        │
│  - fcm_token (for push notifications)                   │
└─────────────────────────────────────────────────────────┘
```

### 認證流程

#### 1. Google/Apple Sign-In 流程

```
1. 用戶點擊「使用 Google 登入」
   ↓
2. Flutter 調用 Google Sign-In SDK
   ↓
3. 用戶在 Google 授權頁面登入
   ↓
4. Google 回傳用戶資訊和授權碼
   ↓
5. Flutter 使用 Google 憑證登入 Firebase
   ↓
6. Firebase 回傳 Firebase User 和 ID Token
   ↓
7. Flutter 發送 ID Token 到 Laravel
   POST /api/v1/auth/firebase/login
   Body: { "id_token": "...", "fcm_token": "..." }
   ↓
8. Laravel 使用 Firebase Admin SDK 驗證 Token
   ↓
9. Laravel 檢查用戶是否存在：
   - 如果 firebase_uid 存在 → 返回該用戶
   - 如果 email 存在 → 綁定 Firebase UID 到現有帳號
   - 否則 → 建立新用戶
   ↓
10. Laravel 儲存/更新 FCM Token
    ↓
11. Laravel 回傳用戶資訊
    ↓
12. Flutter 儲存認證狀態，導航到主畫面
```

#### 2. 混合認證模式

本系統支援兩種認證方式並存：

| 認證方式 | Middleware | Token 類型 | 適用場景 |
|---------|-----------|-----------|---------|
| **Sanctum** | `auth:sanctum` | Sanctum Token | 傳統 email/password 登入 |
| **Firebase** | `firebase.auth` | Firebase ID Token | Google/Apple Sign-In |

**帳號綁定策略：**

```php
// 1. 優先查找 firebase_uid
$user = User::where('firebase_uid', $firebaseUid)->first();

// 2. 如果沒找到，查找相同 email
if (!$user) {
    $user = User::where('email', $email)->first();
    if ($user) {
        // 綁定現有帳號到 Firebase
        $user->update(['firebase_uid' => $firebaseUid]);
    }
}

// 3. 建立新用戶
if (!$user) {
    $user = User::create([...]);
}
```

---

## 實作內容

### 1. 後端實作 (Laravel)

#### 1.1 安裝依賴套件

```bash
composer require kreait/laravel-firebase
```

**套件版本：**
- `kreait/laravel-firebase`: ^6.1
- `kreait/firebase-php`: ^7.23
- `google/auth`: ^1.48

#### 1.2 資料庫變更

**Migration 檔案：** `database/migrations/2025_11_05_181541_add_firebase_fields_to_users_table.php`

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('firebase_uid')->nullable()->unique()->after('email');
    $table->text('fcm_token')->nullable()->after('firebase_uid');
});
```

**欄位說明：**

| 欄位 | 類型 | 說明 |
|------|------|------|
| `firebase_uid` | string, unique | Firebase 用戶唯一識別碼 |
| `fcm_token` | text, nullable | Firebase Cloud Messaging token |

**更新 User Model：**

```php
protected $fillable = [
    // ... existing fields
    'firebase_uid',
    'fcm_token',
];
```

#### 1.3 核心類別

##### FirebaseAuthService.php

**位置：** `app/Services/FirebaseAuthService.php`

**職責：**
- 驗證 Firebase ID Token
- 建立或查找用戶
- 管理 FCM Token
- 提取 Provider 資訊

**主要方法：**

```php
// 驗證 Firebase ID Token
public function verifyIdToken(string $idToken): Token

// 建立或查找用戶（支援帳號綁定）
public function findOrCreateUser($verifiedToken): User

// 更新 FCM Token
public function updateFcmToken(User $user, ?string $fcmToken): void

// 清除 FCM Token（登出）
public function clearFcmToken(User $user): void
```

**Provider 對應表：**

| Firebase Provider | 儲存值 |
|------------------|--------|
| `google.com` | `google` |
| `apple.com` | `apple` |
| `password` | `email` |
| 其他 | `firebase` |

##### FirebaseAuthMiddleware.php

**位置：** `app/Http/Middleware/FirebaseAuthMiddleware.php`

**職責：**
- 攔截 API 請求
- 從 Bearer Token 提取 Firebase ID Token
- 驗證 Token 有效性
- 設定 authenticated user

**處理流程：**

```php
1. 提取 Bearer Token
2. 驗證 Token
3. 查找或建立用戶
4. 設定 auth()->user()
5. 繼續請求
```

**錯誤處理：**

```php
- 無 Token → 401 "No authentication token provided"
- 無效 Token → 401 "Invalid or expired token"
- 其他錯誤 → 500 "Authentication failed"
```

##### FirebaseAuthController.php

**位置：** `app/Http/Controllers/Api/FirebaseAuthController.php`

**端點實作：**

| 方法 | 端點 | 說明 |
|------|------|------|
| `login()` | POST /auth/firebase/login | Firebase 登入 |
| `me()` | GET /auth/firebase/me | 取得用戶資訊 |
| `updateFcmToken()` | POST /auth/firebase/fcm-token | 更新 FCM Token |
| `logout()` | POST /auth/firebase/logout | 登出（清除 FCM Token） |

#### 1.4 API 路由

**位置：** `routes/api.php`

```php
Route::prefix('v1')->name('v1.')->group(function () {
    // 公開端點
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/auth/firebase/login', [FirebaseAuthController::class, 'login']);
    });

    // 需要認證的端點
    Route::middleware('firebase.auth')->group(function () {
        Route::get('/auth/firebase/me', [FirebaseAuthController::class, 'me']);
        Route::post('/auth/firebase/fcm-token', [FirebaseAuthController::class, 'updateFcmToken']);
        Route::post('/auth/firebase/logout', [FirebaseAuthController::class, 'logout']);
    });
});
```

#### 1.5 環境設定

**`.env` 配置：**

```env
# Firebase Configuration
FIREBASE_CREDENTIALS=/path/to/storage/app/firebase/service-account.json
FIREBASE_PROJECT_ID=holdyourbeer-xxxxx
FIREBASE_DATABASE_URL=https://holdyourbeer-xxxxx.firebaseio.com
FIREBASE_STORAGE_DEFAULT_BUCKET=holdyourbeer-xxxxx.appspot.com
```

**Service Account Key 取得方式：**

1. Firebase Console → 專案設定 → 服務帳戶
2. 點擊「產生新的私密金鑰」
3. 下載 JSON 檔案
4. 放置到安全位置（不要提交到 Git）

---

### 2. API 端點說明

#### 2.1 POST `/api/v1/auth/firebase/login`

**功能：** 使用 Firebase ID Token 登入

**請求：**

```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIs...",
  "fcm_token": "fGcm:token:example..." // 可選
}
```

**成功回應 (200)：**

```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "provider": "google",
    "firebase_uid": "firebase-uid-123"
  }
}
```

**錯誤回應：**

- `401`: Invalid or expired Firebase token
- `500`: Authentication failed

#### 2.2 GET `/api/v1/auth/firebase/me`

**功能：** 取得已認證用戶資訊

**Headers：**

```
Authorization: Bearer <firebase_id_token>
```

**成功回應 (200)：**

```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "provider": "google",
    "firebase_uid": "firebase-uid-123",
    "email_verified_at": "2025-11-05T10:30:45+00:00",
    "created_at": "2025-11-05T10:30:45+00:00"
  }
}
```

#### 2.3 POST `/api/v1/auth/firebase/fcm-token`

**功能：** 更新 FCM Token（用於推播通知）

**Headers：**

```
Authorization: Bearer <firebase_id_token>
```

**請求：**

```json
{
  "fcm_token": "fGcm:token:example..."
}
```

**成功回應 (200)：**

```json
{
  "message": "FCM token updated successfully"
}
```

#### 2.4 POST `/api/v1/auth/firebase/logout`

**功能：** 登出（清除 FCM Token）

**Headers：**

```
Authorization: Bearer <firebase_id_token>
```

**成功回應 (200)：**

```json
{
  "message": "Logout successful"
}
```

**注意：** 前端也需要調用 `FirebaseAuth.instance.signOut()`

---

### 3. 前端實作 (Flutter)

#### 3.1 依賴套件

**pubspec.yaml：**

```yaml
dependencies:
  firebase_core: ^3.8.1
  firebase_auth: ^5.3.3
  google_sign_in: ^6.2.2
  sign_in_with_apple: ^6.1.3
  firebase_messaging: ^15.1.5
  http: ^1.2.2
```

#### 3.2 核心實作

**Firebase Auth Service：**

```dart
// lib/services/firebase_auth_service.dart
class FirebaseAuthService {
  final FirebaseAuth _auth = FirebaseAuth.instance;
  final GoogleSignIn _googleSignIn = GoogleSignIn();

  // Google Sign-In
  Future<UserCredential?> signInWithGoogle() async { ... }

  // Apple Sign-In
  Future<UserCredential?> signInWithApple() async { ... }

  // Get ID Token
  Future<String?> getIdToken() async {
    return await _auth.currentUser?.getIdToken();
  }

  // Sign Out
  Future<void> signOut() async {
    await _googleSignIn.signOut();
    await _auth.signOut();
  }
}
```

**Laravel API Service：**

```dart
// lib/services/laravel_api_service.dart
class LaravelApiService {
  static const baseUrl = 'https://api.holdyourbeer.com';

  Future<Map<String, dynamic>?> loginWithFirebase(
    String idToken, {
    String? fcmToken,
  }) async { ... }

  Future<Map<String, dynamic>?> getUserInfo(String idToken) async { ... }

  Future<bool> updateFcmToken(String idToken, String fcmToken) async { ... }

  Future<bool> logout(String idToken) async { ... }
}
```

#### 3.3 完整登入流程實作

```dart
Future<void> _handleGoogleSignIn() async {
  // 1. Google Sign-In
  final userCredential = await firebaseAuthService.signInWithGoogle();

  // 2. Get Firebase ID Token
  final idToken = await firebaseAuthService.getIdToken();

  // 3. Get FCM Token (optional)
  final fcmToken = await FirebaseMessaging.instance.getToken();

  // 4. Login to Laravel API
  final result = await laravelApiService.loginWithFirebase(
    idToken!,
    fcmToken: fcmToken,
  );

  // 5. Navigate to home screen
  if (result != null) {
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (_) => HomeScreen()),
    );
  }
}
```

---

## 文件結構

### 已建立的文件

| 文件 | 位置 | 說明 |
|------|------|------|
| **Firebase 設定指南** | `docs/FIREBASE_SETUP.md` | Firebase Console 完整設定步驟 |
| **Flutter 整合指南** | `docs/FLUTTER_INTEGRATION.md` | Flutter 端完整實作指南 |
| **實作規劃文件** | `docs/FIREBASE_AUTH_IMPLEMENTATION.md` | 本文件 |
| **OpenAPI 規格** | `spec/api/api.yaml` | API 端點定義 |

### 文件內容摘要

#### FIREBASE_SETUP.md

包含：
1. 建立 Firebase 專案步驟
2. 新增 Android/iOS 應用程式
3. 設定 Google Sign-In
4. 設定 Apple Sign-In
5. 設定 FCM 推播通知
6. 產生 Service Account Key
7. 環境變數配置
8. 常見問題排查

#### FLUTTER_INTEGRATION.md

包含：
1. Flutter 專案設定（Android/iOS）
2. Firebase SDK 初始化
3. Firebase Auth Service 實作
4. Laravel API Service 實作
5. 登入畫面實作
6. FCM 推播通知設定
7. 完整認證流程說明
8. Token 刷新處理
9. 常見問題解答

---

## 安全性考量

### 1. Token 安全

**Firebase ID Token：**
- ✅ 每小時自動過期
- ✅ 由 Firebase SDK 自動刷新
- ✅ 使用 HTTPS 傳輸
- ✅ 後端使用 Admin SDK 驗證

**Service Account Key：**
- ⚠️ **絕不提交到版本控制**
- ✅ 使用環境變數或安全儲存
- ✅ 限制檔案權限（600）
- ✅ 定期更換金鑰

### 2. 資料保護

```php
// User Model - 隱藏敏感欄位
protected $hidden = [
    'password',
    'remember_token',
    'fcm_token',  // 不回傳給前端
];
```

### 3. API 安全

**Rate Limiting：**

```php
Route::middleware('throttle:auth')->group(function () {
    Route::post('/auth/firebase/login', ...);
});
```

**CORS 設定：**

```php
// config/cors.php
'allowed_origins' => [env('FRONTEND_URL')],
```

### 4. 帳號安全

**密碼處理：**
- Firebase 用戶建立時使用隨機密碼
- 防止 email/password 認證洩漏

**Email 驗證：**
- Firebase 用戶自動標記為已驗證
- `email_verified_at` 自動設定為當前時間

---

## 測試指南

### 1. 後端測試

#### 測試 Firebase 連線

```bash
php artisan tinker
```

```php
$auth = app('firebase.auth');
$auth->getUser('test-uid');
// 無錯誤表示設定成功
```

#### 測試 Token 驗證

```php
use App\Services\FirebaseAuthService;

$service = app(FirebaseAuthService::class);
$token = 'eyJhbGciOiJSUzI1NiIs...';
$verified = $service->verifyIdToken($token);
dd($verified->claims()->all());
```

### 2. API 測試

#### 使用 Postman

```
POST https://api.holdyourbeer.com/api/v1/auth/firebase/login
Content-Type: application/json

{
  "id_token": "YOUR_FIREBASE_ID_TOKEN",
  "fcm_token": "YOUR_FCM_TOKEN"
}
```

#### 使用 cURL

```bash
curl -X POST https://api.holdyourbeer.com/api/v1/auth/firebase/login \
  -H "Content-Type: application/json" \
  -d '{"id_token":"YOUR_TOKEN"}'
```

### 3. Flutter 測試

```dart
// 測試 Google Sign-In
testWidgets('Google Sign-In flow', (WidgetTester tester) async {
  await tester.pumpWidget(MyApp());
  await tester.tap(find.text('使用 Google 登入'));
  await tester.pumpAndSettle();

  expect(find.byType(HomeScreen), findsOneWidget);
});
```

---

## 部署清單

### 開發環境

- [ ] 執行 `composer install`
- [ ] 執行 `php artisan migrate`
- [ ] 設定 `.env` 環境變數
- [ ] 放置 Service Account Key JSON
- [ ] 測試 Firebase 連線
- [ ] 測試 API 端點

### Firebase Console

- [ ] 建立 Firebase 專案
- [ ] 新增 Android 應用程式
- [ ] 新增 iOS 應用程式
- [ ] 啟用 Google Sign-In
- [ ] 啟用 Apple Sign-In
- [ ] 設定 FCM（上傳 APNs 金鑰）
- [ ] 產生 Service Account Key

### Flutter 專案

- [ ] 安裝依賴套件
- [ ] 放置 `google-services.json`
- [ ] 放置 `GoogleService-Info.plist`
- [ ] 實作 Firebase Auth Service
- [ ] 實作 Laravel API Service
- [ ] 實作登入畫面
- [ ] 測試 Google Sign-In
- [ ] 測試 Apple Sign-In（iOS）

### 生產環境

- [ ] 設定環境變數（不使用 .env 檔案）
- [ ] 使用 Secrets Manager 儲存 Service Account Key
- [ ] 設定 HTTPS
- [ ] 設定 CORS 允許清單
- [ ] 啟用 Rate Limiting
- [ ] 監控 Firebase Quota
- [ ] 設定錯誤日誌

---

## 故障排除

### 常見問題

#### 1. Token 驗證失敗

**症狀：** 401 "Invalid or expired token"

**檢查項目：**
- [ ] Service Account Key 路徑正確
- [ ] Firebase Project ID 正確
- [ ] Token 未過期
- [ ] 時鐘同步正確

**解決方案：**

```bash
# 清除 Laravel 快取
php artisan config:clear
php artisan cache:clear

# 檢查 Firebase 設定
php artisan tinker
>>> config('firebase.projects.app.credentials')
```

#### 2. Google Sign-In 無反應

**症狀：** 點擊按鈕沒有開啟 Google 登入頁面

**檢查項目：**
- [ ] SHA-1 憑證已加入 Firebase Console
- [ ] `google-services.json` 是最新版本
- [ ] Web Client ID 正確

**解決方案：**

```bash
# 重新取得 SHA-1
keytool -list -v -keystore ~/.android/debug.keystore \
  -alias androiddebugkey \
  -storepass android -keypass android

# 重新下載 google-services.json
# 清除快取並重新編譯
flutter clean
flutter pub get
```

#### 3. FCM 推播收不到

**症狀：** 推播通知無法送達

**檢查項目：**
- [ ] iOS: APNs 金鑰已上傳
- [ ] iOS: Xcode 已啟用 Push Notifications
- [ ] Android: `google-services.json` 正確
- [ ] FCM Token 已成功傳送到後端

**解決方案：**

```dart
// 檢查 FCM Token
final token = await FirebaseMessaging.instance.getToken();
print('FCM Token: $token');

// 測試前景通知
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  print('收到通知: ${message.notification?.title}');
});
```

#### 4. Apple Sign-In 失敗

**症狀：** Apple Sign-In 按鈕無反應或錯誤

**檢查項目：**
- [ ] App ID 已啟用 Sign In with Apple
- [ ] Xcode 已加入 Sign In with Apple capability
- [ ] iOS 系統版本 >= 13
- [ ] Firebase Console 已啟用 Apple 提供者

---

## 效能優化

### 1. Token 快取

Firebase ID Token 驗證需要網路請求，建議實作快取：

```php
// 在 FirebaseAuthService 中加入快取
use Illuminate\Support\Facades\Cache;

public function verifyIdToken(string $idToken)
{
    $cacheKey = 'firebase_token_' . hash('sha256', $idToken);

    return Cache::remember($cacheKey, 3600, function () use ($idToken) {
        return $this->auth->verifyIdToken($idToken);
    });
}
```

### 2. 資料庫索引

```sql
-- 確保關鍵欄位有索引
ALTER TABLE users ADD INDEX idx_firebase_uid (firebase_uid);
ALTER TABLE users ADD INDEX idx_email (email);
```

### 3. FCM Token 批次更新

```php
// 避免每次請求都更新 FCM Token
public function updateFcmToken(User $user, ?string $fcmToken): void
{
    if ($fcmToken && $user->fcm_token !== $fcmToken) {
        $user->update(['fcm_token' => $fcmToken]);
    }
}
```

---

## 監控與日誌

### 1. Laravel 日誌

```php
// 在 FirebaseAuthService 中加入日誌
use Illuminate\Support\Facades\Log;

try {
    $verified = $this->auth->verifyIdToken($idToken);
    Log::info('Firebase auth success', ['uid' => $verified->claims()->get('sub')]);
} catch (FailedToVerifyToken $e) {
    Log::error('Firebase auth failed', ['error' => $e->getMessage()]);
    throw $e;
}
```

### 2. Firebase Console 監控

- Authentication 使用量
- FCM 送達率
- Analytics 事件

### 3. Laravel Telescope (開發環境)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

---

## 擴展功能建議

### 1. 電話號碼認證

Firebase 支援 Phone Authentication：

```dart
// Flutter
await FirebaseAuth.instance.verifyPhoneNumber(
  phoneNumber: '+886912345678',
  verificationCompleted: (PhoneAuthCredential credential) {},
  verificationFailed: (FirebaseAuthException e) {},
  codeSent: (String verificationId, int? resendToken) {},
  codeAutoRetrievalTimeout: (String verificationId) {},
);
```

### 2. 社交媒體綁定

支援多個登入方式綁定到同一帳號：

```php
// 在 User Model 加入
public function linkedProviders(): array
{
    return array_filter([
        $this->google_id ? 'google' : null,
        $this->apple_id ? 'apple' : null,
        $this->password ? 'email' : null,
    ]);
}
```

### 3. 推播通知發送

```php
// 建立推播服務
use Kreait\Firebase\Contract\Messaging;

class PushNotificationService
{
    public function __construct(protected Messaging $messaging) {}

    public function sendToUser(User $user, string $title, string $body)
    {
        if (!$user->fcm_token) return;

        $message = [
            'token' => $user->fcm_token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ];

        $this->messaging->send($message);
    }
}
```

### 4. Analytics 事件追蹤

```dart
// Flutter
await FirebaseAnalytics.instance.logEvent(
  name: 'beer_tasted',
  parameters: {
    'beer_id': '123',
    'provider': 'google',
  },
);
```

---

## 版本歷史

| 版本 | 日期 | 變更內容 |
|------|------|---------|
| 1.0.0 | 2025-11-05 | 初版：整合 Firebase Auth，支援 Google 和 Apple Sign-In |

---

## 參考資源

### 官方文件

- [Firebase Auth 文件](https://firebase.google.com/docs/auth)
- [kreait/laravel-firebase 文件](https://firebase-php.readthedocs.io/)
- [Google Sign-In Flutter](https://pub.dev/packages/google_sign_in)
- [Sign in with Apple Flutter](https://pub.dev/packages/sign_in_with_apple)
- [Firebase Cloud Messaging](https://firebase.google.com/docs/cloud-messaging)

### 專案文件

- [FIREBASE_SETUP.md](./FIREBASE_SETUP.md) - Firebase Console 設定指南
- [FLUTTER_INTEGRATION.md](./FLUTTER_INTEGRATION.md) - Flutter 整合指南
- [README.md](../README.md) - 專案總覽

### 相關 API

- [OpenAPI 規格](../spec/api/api.yaml)
- Firebase Admin SDK PHP: https://firebase-php.readthedocs.io/

---

## 授權與貢獻

此實作遵循專案的整體授權條款。

**貢獻者：**
- Claude Code (AI Assistant) - 初始實作
- HoldYourBeer Team - 需求定義與測試

---

## 聯絡資訊

如有問題或建議，請：
- 建立 GitHub Issue
- 參考相關文件
- 查看 FAQ 章節

**最後更新：** 2025-11-05
