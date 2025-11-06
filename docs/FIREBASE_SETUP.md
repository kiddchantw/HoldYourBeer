# Firebase Console 設定完整指南

本指南將帶你完成 Firebase 專案的完整設定，包含 Google Sign-In、Apple Sign-In、FCM 推播通知和 Analytics。

## 目錄

1. [建立 Firebase 專案](#1-建立-firebase-專案)
2. [新增應用程式](#2-新增應用程式)
3. [設定 Google Sign-In](#3-設定-google-sign-in)
4. [設定 Apple Sign-In](#4-設定-apple-sign-in)
5. [設定 FCM 推播通知](#5-設定-fcm-推播通知)
6. [設定 Analytics](#6-設定-analytics)
7. [產生 Service Account Key](#7-產生-service-account-key-給-laravel)
8. [環境變數設定](#8-環境變數設定)

---

## 1. 建立 Firebase 專案

### 步驟：

1. 前往 [Firebase Console](https://console.firebase.google.com/)
2. 點擊「新增專案」(Add project)
3. 輸入專案名稱：`HoldYourBeer` 或自訂名稱
4. 選擇是否啟用 Google Analytics（建議啟用）
5. 選擇或建立 Analytics 帳戶
6. 點擊「建立專案」

⏱️ 等待專案建立完成（約 30 秒）

---

## 2. 新增應用程式

### 2.1 新增 Android 應用程式

1. 在 Firebase Console 專案首頁，點擊 Android 圖示
2. 填寫資訊：
   ```
   Android 套件名稱: com.example.holdyourbeer
   應用程式暱稱: HoldYourBeer Android
   SHA-1 憑證指紋: (可選，用於 Google Sign-In)
   ```

3. 取得 SHA-1 憑證：
   ```bash
   # Debug 憑證
   keytool -list -v -keystore ~/.android/debug.keystore -alias androiddebugkey -storepass android -keypass android

   # Release 憑證
   keytool -list -v -keystore your-release-key.keystore -alias your-key-alias
   ```

4. 點擊「註冊應用程式」
5. 下載 `google-services.json`
6. 將檔案放到 Flutter 專案：
   ```
   android/app/google-services.json
   ```

### 2.2 新增 iOS 應用程式

1. 點擊 iOS 圖示
2. 填寫資訊：
   ```
   iOS 套件 ID: com.example.holdyourbeer
   應用程式暱稱: HoldYourBeer iOS
   App Store ID: (可選)
   ```

3. 點擊「註冊應用程式」
4. 下載 `GoogleService-Info.plist`
5. 將檔案放到 Flutter 專案：
   ```
   ios/Runner/GoogleService-Info.plist
   ```

6. 使用 Xcode 打開專案，將 `GoogleService-Info.plist` 加入到 Runner target

---

## 3. 設定 Google Sign-In

### 步驟：

1. 在 Firebase Console 左側選單，點擊「Authentication」
2. 點擊「開始使用」(Get started)
3. 點擊「登入方式」(Sign-in method) 分頁
4. 在「提供者」列表中找到「Google」
5. 點擊「啟用」開關
6. 填寫資訊：
   ```
   專案的公開名稱: HoldYourBeer
   專案支援電子郵件: your-email@example.com
   ```
7. 點擊「儲存」

### 取得 Web Client ID（給 Flutter 使用）：

1. 在「Google」提供者設定中，展開「Web SDK 設定」
2. 複製「Web 用戶端 ID」
3. 在 Flutter 專案中使用：

   ```dart
   GoogleSignIn(
     scopes: ['email'],
     // 可選：指定 clientId
     // clientId: 'YOUR-WEB-CLIENT-ID.apps.googleusercontent.com',
   );
   ```

---

## 4. 設定 Apple Sign-In

### 前置需求：

- Apple Developer 帳號
- 已註冊的 App ID
- 已設定 Sign in with Apple Capability

### 步驟：

#### 4.1 Apple Developer Portal 設定

1. 前往 [Apple Developer Portal](https://developer.apple.com/)
2. 選擇「Certificates, Identifiers & Profiles」
3. 點擊「Identifiers」
4. 選擇你的 App ID
5. 確保「Sign In with Apple」已啟用
6. 如果需要，點擊「Configure」設定網域

#### 4.2 Firebase Console 設定

1. 在 Firebase Console 的「Authentication」→「登入方式」
2. 找到「Apple」提供者
3. 點擊「啟用」開關
4. 不需要填寫額外資訊（iOS 原生整合）
5. 點擊「儲存」

#### 4.3 Xcode 設定

1. 打開 `ios/Runner.xcworkspace`
2. 選擇 Runner target
3. 點擊「Signing & Capabilities」
4. 點擊「+ Capability」
5. 新增「Sign In with Apple」

---

## 5. 設定 FCM 推播通知

### 5.1 Android 設定

Firebase Cloud Messaging 在 Android 上已自動啟用，無需額外設定。

### 5.2 iOS 設定

#### 步驟 1：上傳 APNs 認證金鑰

1. 前往 [Apple Developer Portal](https://developer.apple.com/)
2. 選擇「Certificates, Identifiers & Profiles」
3. 點擊「Keys」→「+」建立新金鑰
4. 勾選「Apple Push Notifications service (APNs)」
5. 下載金鑰檔案（.p8）
6. **重要：記錄金鑰 ID 和 Team ID**

#### 步驟 2：上傳到 Firebase

1. 在 Firebase Console，點擊專案設定 ⚙️
2. 選擇「雲端通訊」(Cloud Messaging) 分頁
3. 在「Apple 應用程式設定」區塊：
   - 點擊「上傳」
   - 選擇剛才下載的 .p8 檔案
   - 輸入金鑰 ID
   - 輸入 Team ID
4. 點擊「上傳」

#### 步驟 3：Xcode 設定

1. 打開 `ios/Runner.xcworkspace`
2. 選擇 Runner target
3. 點擊「Signing & Capabilities」
4. 點擊「+ Capability」
5. 新增「Push Notifications」
6. 新增「Background Modes」並勾選：
   - Remote notifications

---

## 6. 設定 Analytics

Analytics 在建立專案時如果已啟用，會自動收集基本事件。

### 查看 Analytics 資料：

1. 在 Firebase Console 左側選單，點擊「Analytics」
2. 查看「資訊主頁」、「事件」、「轉換」等報表

### 自訂事件（Flutter）：

```dart
import 'package:firebase_analytics/firebase_analytics.dart';

final analytics = FirebaseAnalytics.instance;

// 記錄自訂事件
await analytics.logEvent(
  name: 'beer_tasted',
  parameters: {
    'beer_id': '123',
    'beer_name': 'Guinness Draught',
  },
);
```

---

## 7. 產生 Service Account Key (給 Laravel)

Laravel 後端需要 Service Account Key 來驗證 Firebase ID Token。

### 步驟：

1. 在 Firebase Console，點擊專案設定 ⚙️
2. 選擇「服務帳戶」(Service accounts) 分頁
3. 點擊「產生新的私密金鑰」
4. 確認並下載 JSON 檔案
5. **重要：妥善保管此檔案，不要提交到版本控制**

### Laravel 設定：

#### 方法 1：環境變數（推薦）

1. 將 JSON 檔案內容轉換為單行字串：
   ```bash
   cat service-account.json | jq -c
   ```

2. 在 `.env` 中設定：
   ```env
   FIREBASE_CREDENTIALS='{"type":"service_account","project_id":"..."}'
   ```

#### 方法 2：檔案路徑

1. 將 JSON 檔案放到安全位置：
   ```
   storage/app/firebase/service-account.json
   ```

2. 在 `.env` 中設定：
   ```env
   FIREBASE_CREDENTIALS=/path/to/storage/app/firebase/service-account.json
   ```

3. 確保 `.gitignore` 包含：
   ```
   storage/app/firebase/
   ```

---

## 8. 環境變數設定

### Laravel `.env` 完整設定：

```env
# Firebase Configuration
FIREBASE_CREDENTIALS=/path/to/service-account.json
# 或使用 JSON 字串
# FIREBASE_CREDENTIALS='{"type":"service_account",...}'

FIREBASE_PROJECT_ID=holdyourbeer-xxxxx
FIREBASE_DATABASE_URL=https://holdyourbeer-xxxxx.firebaseio.com
FIREBASE_STORAGE_DEFAULT_BUCKET=holdyourbeer-xxxxx.appspot.com
```

### 取得這些值：

1. **FIREBASE_PROJECT_ID**：
   - Firebase Console → 專案設定 → 一般
   - 複製「專案 ID」

2. **FIREBASE_DATABASE_URL**：
   - Firebase Console → Realtime Database
   - 複製資料庫 URL
   - （如果沒使用 Realtime Database 可忽略）

3. **FIREBASE_STORAGE_DEFAULT_BUCKET**：
   - Firebase Console → Storage
   - 複製儲存空間網址
   - （如果沒使用 Storage 可忽略）

---

## 測試設定

### 1. 測試 Laravel 連線：

```bash
php artisan tinker
```

```php
$auth = app('firebase.auth');
echo $auth->getUser('some-uid')->uid;
// 如果沒有錯誤，表示設定成功
```

### 2. 測試 Flutter 整合：

```dart
// 測試 Google Sign In
final userCredential = await firebaseAuth.signInWithGoogle();
print('User: ${userCredential?.user?.email}');

// 測試 API 呼叫
final idToken = await FirebaseAuth.instance.currentUser?.getIdToken();
final response = await http.post(
  Uri.parse('https://your-api.com/api/v1/auth/firebase/login'),
  body: {'id_token': idToken},
);
print('API Response: ${response.body}');
```

---

## 常見問題排查

### Q1: Google Sign In 失敗

**解決方案：**
- 確認 SHA-1 憑證已加入 Firebase Console
- 確認 `google-services.json` 是最新版本
- 重新下載並替換配置檔

### Q2: Apple Sign In 無法使用

**解決方案：**
- 確認 App ID 已啟用 Sign In with Apple
- 確認 Xcode 專案已加入 Sign In with Apple capability
- 確認裝置系統版本 >= iOS 13

### Q3: FCM 推播收不到

**解決方案：**
- iOS：確認 APNs 金鑰已正確上傳
- iOS：確認 Xcode 已啟用 Push Notifications 和 Background Modes
- Android：確認 `google-services.json` 已正確放置
- 確認 FCM Token 已成功傳送到後端

### Q4: Laravel 無法驗證 Token

**解決方案：**
- 確認 Service Account Key JSON 檔案路徑正確
- 確認 JSON 內容完整且有效
- 執行 `php artisan config:clear` 清除快取
- 檢查 Laravel logs: `storage/logs/laravel.log`

---

## 安全性最佳實踐

### ✅ 應該做：

- 將 Service Account Key 存放在安全位置
- 使用環境變數管理敏感資訊
- 定期更換 API 金鑰
- 在 Firebase Console 設定安全規則

### ❌ 不應該做：

- 不要將 Service Account Key 提交到 Git
- 不要在前端程式碼中硬編碼敏感資訊
- 不要使用 Admin SDK 在客戶端
- 不要分享或公開金鑰檔案

---

## 下一步

完成 Firebase Console 設定後：

1. 參考 `FLUTTER_INTEGRATION.md` 完成 Flutter 整合
2. 測試 Google/Apple 登入流程
3. 測試 FCM 推播功能
4. 檢視 Analytics 資料

---

## 參考資源

- [Firebase Console](https://console.firebase.google.com/)
- [Firebase Auth 文件](https://firebase.google.com/docs/auth)
- [FCM 文件](https://firebase.google.com/docs/cloud-messaging)
- [Apple Developer Portal](https://developer.apple.com/)
- [Google Cloud Console](https://console.cloud.google.com/)
