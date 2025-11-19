# CSRF 保護機制說明

## 概述

HoldYourBeer 使用 **Laravel Sanctum** 提供 API 認證和 CSRF 保護。本文檔說明 CSRF 保護的工作原理以及如何在不同客戶端應用中正確配置。

---

## Sanctum 的 CSRF 保護機制

Laravel Sanctum 提供兩種認證方式：

### 1. Cookie-Based Authentication（用於 SPA）
- 適用於同域或子域的 SPA（Single Page Application）
- 使用 HTTP-only cookies 儲存 session
- **自動提供 CSRF 保護**，無需額外配置

### 2. Token-Based Authentication（用於 Mobile App）
- 適用於 Flutter、React Native 等 Mobile App
- 使用 Bearer Token 認證
- **不需要 CSRF 保護**（stateless）

---

## SPA 應用的 CSRF 保護設定

### 前端設定（JavaScript/TypeScript）

#### 步驟 1：獲取 CSRF Cookie

在發送任何需要認證的請求之前，先呼叫 `/sanctum/csrf-cookie` 端點：

```javascript
// 初始化 CSRF 保護
await axios.get('/sanctum/csrf-cookie');
```

#### 步驟 2：配置 Axios

```javascript
import axios from 'axios';

// 設定 Axios 全域配置
axios.defaults.withCredentials = true;
axios.defaults.baseURL = 'http://holdyourbeer.test';

// 範例：登入請求
async function login(email, password) {
  // 先獲取 CSRF cookie
  await axios.get('/sanctum/csrf-cookie');

  // 發送登入請求
  const response = await axios.post('/api/v1/login', {
    email,
    password
  });

  return response.data;
}
```

#### 步驟 3：後續請求

CSRF cookie 會自動包含在後續請求中：

```javascript
// 獲取啤酒列表（自動包含 CSRF token）
const beers = await axios.get('/api/v1/beers');
```

---

## Mobile App 的 Token 認證設定

### Flutter 應用設定

Mobile App 使用 **Token-Based Authentication**，不需要 CSRF 保護。

#### 步驟 1：登入並儲存 Token

```dart
// API Client 設定
class ApiClient {
  final String baseUrl = 'http://holdyourbeer.test';
  String? _token;

  Future<void> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/v1/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      _token = data['token'];
      // 儲存 token 到 secure storage
      await _secureStorage.write(key: 'auth_token', value: _token);
    }
  }
}
```

#### 步驟 2：使用 Bearer Token

```dart
Future<List<Beer>> getBeers() async {
  final response = await http.get(
    Uri.parse('$baseUrl/api/v1/beers'),
    headers: {
      'Authorization': 'Bearer $_token',
      'Accept': 'application/json',
    },
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    return (data['data'] as List)
        .map((json) => Beer.fromJson(json))
        .toList();
  }

  throw Exception('Failed to load beers');
}
```

---

## Laravel 後端配置

### 1. Sanctum 設定檔 (`config/sanctum.php`)

```php
<?php

return [
    // Stateful domains for SPA (允許 CSRF 保護的域名)
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    // Middleware for web guard
    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];
```

### 2. CORS 設定 (`config/cors.php`)

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://holdyourbeer.test',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // 必須為 true（SPA 使用 cookies）
];
```

### 3. 環境變數 (`.env`)

```bash
# SPA 域名設定
SANCTUM_STATEFUL_DOMAINS=localhost:3000,holdyourbeer.test

# Session 設定
SESSION_DRIVER=cookie
SESSION_DOMAIN=.holdyourbeer.test
SESSION_SECURE_COOKIE=false  # 生產環境設為 true（使用 HTTPS）
```

---

## CSRF 保護驗證方法

### 測試 CSRF 保護是否生效

#### 測試 1：沒有 CSRF Token 的請求應該被拒絕

```bash
curl -X POST http://holdyourbeer.test/api/v1/beers/1/count_action \
  -H "Content-Type: application/json" \
  -d '{"action": "increment"}'

# 預期結果：419 CSRF Token Mismatch
```

#### 測試 2：使用正確的 CSRF Token

```bash
# 1. 獲取 CSRF cookie
curl -X GET http://holdyourbeer.test/sanctum/csrf-cookie \
  -c cookies.txt

# 2. 使用 cookie 發送請求
curl -X POST http://holdyourbeer.test/api/v1/beers/1/count_action \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{"action": "increment"}'

# 預期結果：200 OK
```

#### 測試 3：使用 Bearer Token（Mobile App）

```bash
curl -X POST http://holdyourbeer.test/api/v1/beers/1/count_action \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {your-token-here}" \
  -d '{"action": "increment"}'

# 預期結果：200 OK（不需要 CSRF token）
```

---

## 常見問題排解

### 問題 1：419 CSRF Token Mismatch

**原因**：
- 前端沒有先呼叫 `/sanctum/csrf-cookie`
- `axios.defaults.withCredentials` 沒有設為 `true`
- CORS 設定錯誤

**解決方法**：
1. 確認 `SANCTUM_STATEFUL_DOMAINS` 包含前端域名
2. 確認 CORS 設定的 `supports_credentials` 為 `true`
3. 前端確實有呼叫 `/sanctum/csrf-cookie`

### 問題 2：Mobile App 無法認證

**原因**：
- 使用了 SPA 的 cookie 認證方式
- Token 沒有正確儲存或傳送

**解決方法**：
1. 確認使用 `Authorization: Bearer {token}` header
2. 確認 token 正確儲存在 secure storage
3. 確認每個請求都包含 Authorization header

### 問題 3：CORS 錯誤

**原因**：
- `allowed_origins` 沒有包含前端域名
- `supports_credentials` 設定錯誤

**解決方法**：
1. 在 `config/cors.php` 中加入前端域名
2. 確認 `supports_credentials` 為 `true`

---

## 相關檔案位置

- **Sanctum 設定**：`config/sanctum.php`
- **CORS 設定**：`config/cors.php`
- **CSRF Middleware**：`app/Http/Middleware/VerifyCsrfToken.php`
- **環境變數**：`.env`
- **API Routes**：`routes/api.php`

---

## 安全最佳實踐

### ✅ 推薦做法

1. **生產環境使用 HTTPS**
   ```bash
   SESSION_SECURE_COOKIE=true
   ```

2. **限制 Stateful Domains**
   ```bash
   SANCTUM_STATEFUL_DOMAINS=app.holdyourbeer.com
   ```

3. **Token 過期時間**
   ```php
   // config/sanctum.php
   'expiration' => 60 * 24, // 24 小時
   ```

4. **使用 Secure Storage 儲存 Token**
   ```dart
   // Flutter: 使用 flutter_secure_storage
   final storage = FlutterSecureStorage();
   await storage.write(key: 'auth_token', value: token);
   ```

### ❌ 避免做法

1. **不要在 URL 中傳遞 Token**
   ```
   ❌ http://api.example.com/beers?token=abc123
   ✅ Authorization: Bearer abc123
   ```

2. **不要將 Token 儲存在 LocalStorage（Web）**
   - 容易受到 XSS 攻擊
   - 應使用 HTTP-only cookies（SPA）或 Secure Storage（Mobile）

3. **不要在生產環境禁用 CSRF 保護**
   ```php
   // ❌ 不要這樣做
   protected $except = ['*'];
   ```

---

## 參考資源

- [Laravel Sanctum 官方文檔](https://laravel.com/docs/11.x/sanctum)
- [CSRF 保護原理](https://laravel.com/docs/11.x/csrf)
- [CORS 設定指南](https://laravel.com/docs/11.x/routing#cors)
- [Flutter HTTP Package](https://pub.dev/packages/http)
- [Flutter Secure Storage](https://pub.dev/packages/flutter_secure_storage)

---

## 版本資訊

- Laravel: 12.x
- Sanctum: 4.x
- Flutter: 3.x
- 最後更新：2025-11-12
