# HoldYourBeer Deep Link 整合指南

此文件說明如何在 Flutter App 中整合 HoldYourBeer 的 Deep Link 功能,以處理郵件驗證和密碼重設流程。

## 概述

HoldYourBeer 使用 **Universal Links (iOS) / App Links (Android)** 策略:
- 郵件中的連結指向 Web 頁面
- 當用戶已安裝 App 時,系統會自動開啟 App
- 當用戶未安裝 App 時,則開啟 Web 頁面

## Deep Link URL 格式

### 1. Email Verification (郵件驗證)

**URL 格式:**
```
https://holdyourbeers.com/{locale}/verify-email/{id}/{hash}?expires={timestamp}&signature={signature}
```

**範例:**
```
https://holdyourbeers.com/en/verify-email/1/a3f5d8e9b2c4?expires=1733400000&signature=abc123...
https://holdyourbeers.com/zh-TW/verify-email/2/b4e6d9f0c3a5?expires=1733400000&signature=def456...
```

**參數說明:**
- `{locale}`: 語系 (`en` 或 `zh-TW`)
- `{id}`: 用戶 ID
- `{hash}`: 郵件地址的 SHA1 hash
- `expires`: 連結過期時間戳 (UNIX timestamp)
- `signature`: Laravel Signed URL 簽名

**過期時間:** 60 分鐘

---

### 2. Password Reset (密碼重設)

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

---

## Flutter App 整合步驟

### Step 1: 設定 Universal Links / App Links

#### iOS (Universal Links)

1. 在 Apple Developer Portal 啟用 Associated Domains
2. 在 `ios/Runner/Runner.entitlements` 加入:
```xml
<key>com.apple.developer.associated-domains</key>
<array>
    <string>applinks:holdyourbeers.com</string>
</array>
```

3. 在伺服器根目錄提供 `/.well-known/apple-app-site-association` 檔案

#### Android (App Links)

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

---

### Step 2: 在 Flutter 中處理 Deep Link

使用 `uni_links` 或 `go_router` 套件處理 Deep Link:

```dart
import 'package:uni_links/uni_links.dart';

// 監聽 Deep Link
StreamSubscription? _sub;

void initDeepLinks() {
  _sub = linkStream.listen((String? link) {
    if (link != null) {
      handleDeepLink(link);
    }
  });
}

void handleDeepLink(String link) {
  final uri = Uri.parse(link);

  // Email Verification
  if (uri.path.contains('/verify-email/')) {
    final id = uri.pathSegments[2];
    final hash = uri.pathSegments[3];
    final expires = uri.queryParameters['expires'];
    final signature = uri.queryParameters['signature'];

    // 呼叫 API 驗證
    verifyEmail(id, hash, expires, signature);
  }

  // Password Reset
  if (uri.path.contains('/reset-password/')) {
    final token = uri.pathSegments[2];
    final email = uri.queryParameters['email'];

    // 導航到重設密碼頁面
    navigateToResetPassword(token, email);
  }
}
```

---

### Step 3: 呼叫 API 完成驗證

#### Email Verification API

**Endpoint:**
```
GET /api/v1/email/verify/{id}/{hash}
```

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (Success):**
```json
{
  "message": "電子郵件驗證成功",
  "verified": true
}
```

**Response (Already Verified):**
```json
{
  "message": "電子郵件已經驗證過了",
  "verified": true
}
```

---

#### Password Reset API

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

---

## 測試流程

### 測試 Email Verification

1. 在 App 中註冊新帳號
2. 檢查郵件收件匣
3. 點擊郵件中的驗證連結
4. 確認 App 自動開啟並完成驗證

### 測試 Password Reset

1. 在 App 中點擊「忘記密碼」
2. 輸入郵件地址
3. 檢查郵件收件匣
4. 點擊郵件中的重設連結
5. 確認 App 自動開啟重設密碼頁面
6. 輸入新密碼並提交

---

## 多語系支援

所有郵件內容和頁面文字都支援以下語系:
- **英文 (en)**
- **繁體中文 (zh-TW)**

URL 中的 `{locale}` 參數會根據用戶當前選擇的語系自動設定。

---

## 注意事項

1. **連結過期:** 所有連結都有 60 分鐘的有效期限
2. **Signed URL:** Email Verification 使用 Laravel Signed URL,需要驗證 signature
3. **一次性 Token:** Password Reset Token 使用後會失效
4. **網路要求:** API 呼叫需要網路連線
5. **錯誤處理:** 請妥善處理過期、無效或已使用的連結

---

## 相關文件

- [Laravel Email Verification](https://laravel.com/docs/11.x/verification)
- [Laravel Password Reset](https://laravel.com/docs/11.x/passwords)
- [iOS Universal Links](https://developer.apple.com/ios/universal-links/)
- [Android App Links](https://developer.android.com/training/app-links)

---

## 聯絡資訊

如有任何問題,請聯繫後端開發團隊。

**Last Updated:** 2025-12-05
