# Flutter + Firebase Auth 整合指南

本指南將說明如何在 Flutter App 中整合 Firebase Auth，並與 HoldYourBeer Laravel API 進行認證。

## 目錄

1. [環境需求](#環境需求)
2. [Firebase 專案設定](#firebase-專案設定)
3. [Flutter 專案設定](#flutter-專案設定)
4. [實作步驟](#實作步驟)
5. [完整範例程式碼](#完整範例程式碼)
6. [常見問題](#常見問題)

---

## 環境需求

- Flutter SDK 3.0+
- Firebase CLI
- Android Studio / Xcode
- Firebase 專案（請參考 FIREBASE_SETUP.md）

---

## Firebase 專案設定

請先完成 `FIREBASE_SETUP.md` 中的 Firebase Console 設定，確保：

- ✅ 已啟用 Google Sign-In
- ✅ 已啟用 Apple Sign-In（iOS）
- ✅ 已下載 `google-services.json` (Android)
- ✅ 已下載 `GoogleService-Info.plist` (iOS)

---

## Flutter 專案設定

### 1. 安裝依賴套件

在 `pubspec.yaml` 加入以下套件：

```yaml
dependencies:
  flutter:
    sdk: flutter

  # Firebase Core
  firebase_core: ^3.8.1
  firebase_auth: ^5.3.3

  # Google Sign In
  google_sign_in: ^6.2.2

  # Apple Sign In
  sign_in_with_apple: ^6.1.3

  # Firebase Cloud Messaging (推播通知)
  firebase_messaging: ^15.1.5

  # HTTP 請求
  http: ^1.2.2

  # 狀態管理 (可選)
  provider: ^6.1.2
```

執行：
```bash
flutter pub get
```

### 2. Android 設定

#### `android/app/build.gradle`

```gradle
android {
    defaultConfig {
        // 確保 minSdkVersion >= 21
        minSdkVersion 21
    }
}

dependencies {
    // Firebase
    implementation platform('com.google.firebase:firebase-bom:33.1.0')
}
```

#### 將 `google-services.json` 放到：
```
android/app/google-services.json
```

### 3. iOS 設定

#### 將 `GoogleService-Info.plist` 放到：
```
ios/Runner/GoogleService-Info.plist
```

#### 更新 `ios/Runner/Info.plist`：

```xml
<key>CFBundleURLTypes</key>
<array>
    <dict>
        <key>CFBundleTypeRole</key>
        <string>Editor</string>
        <key>CFBundleURLSchemes</key>
        <array>
            <!-- 從 GoogleService-Info.plist 複製 REVERSED_CLIENT_ID -->
            <string>com.googleusercontent.apps.YOUR-CLIENT-ID</string>
        </array>
    </dict>
</array>
```

---

## 實作步驟

### 1. 初始化 Firebase

`lib/main.dart`

```dart
import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/material.dart';
import 'firebase_options.dart'; // 使用 FlutterFire CLI 生成

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );

  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'HoldYourBeer',
      home: AuthScreen(),
    );
  }
}
```

### 2. 建立 Firebase Auth Service

`lib/services/firebase_auth_service.dart`

```dart
import 'package:firebase_auth/firebase_auth.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:sign_in_with_apple/sign_in_with_apple.dart';

class FirebaseAuthService {
  final FirebaseAuth _auth = FirebaseAuth.instance;
  final GoogleSignIn _googleSignIn = GoogleSignIn();

  // 取得當前用戶
  User? get currentUser => _auth.currentUser;

  // 監聽認證狀態
  Stream<User?> get authStateChanges => _auth.authStateChanges();

  // Google 登入
  Future<UserCredential?> signInWithGoogle() async {
    try {
      // 觸發 Google 登入流程
      final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();

      if (googleUser == null) {
        return null; // 用戶取消登入
      }

      // 取得認證憑證
      final GoogleSignInAuthentication googleAuth =
          await googleUser.authentication;

      // 建立 Firebase 憑證
      final credential = GoogleAuthProvider.credential(
        accessToken: googleAuth.accessToken,
        idToken: googleAuth.idToken,
      );

      // 使用憑證登入 Firebase
      return await _auth.signInWithCredential(credential);
    } catch (e) {
      print('Google Sign In Error: $e');
      return null;
    }
  }

  // Apple 登入
  Future<UserCredential?> signInWithApple() async {
    try {
      final appleCredential = await SignInWithApple.getAppleIDCredential(
        scopes: [
          AppleIDAuthorizationScopes.email,
          AppleIDAuthorizationScopes.fullName,
        ],
      );

      final oAuthProvider = OAuthProvider('apple.com');
      final credential = oAuthProvider.credential(
        idToken: appleCredential.identityToken,
        accessToken: appleCredential.authorizationCode,
      );

      return await _auth.signInWithCredential(credential);
    } catch (e) {
      print('Apple Sign In Error: $e');
      return null;
    }
  }

  // 取得 Firebase ID Token
  Future<String?> getIdToken() async {
    final user = currentUser;
    if (user == null) return null;

    return await user.getIdToken();
  }

  // 登出
  Future<void> signOut() async {
    await _googleSignIn.signOut();
    await _auth.signOut();
  }
}
```

### 3. 建立 Laravel API Service

`lib/services/laravel_api_service.dart`

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class LaravelApiService {
  static const String baseUrl = 'YOUR_LARAVEL_API_URL'; // 例如：https://api.holdyourbeer.com

  // Firebase 登入到 Laravel
  Future<Map<String, dynamic>?> loginWithFirebase(
    String idToken, {
    String? fcmToken,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/auth/firebase/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'id_token': idToken,
          'fcm_token': fcmToken,
        }),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        print('Login failed: ${response.body}');
        return null;
      }
    } catch (e) {
      print('API Error: $e');
      return null;
    }
  }

  // 取得用戶資訊
  Future<Map<String, dynamic>?> getUserInfo(String idToken) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/auth/firebase/me'),
        headers: {
          'Authorization': 'Bearer $idToken',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
      return null;
    } catch (e) {
      print('API Error: $e');
      return null;
    }
  }

  // 更新 FCM Token
  Future<bool> updateFcmToken(String idToken, String fcmToken) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/auth/firebase/fcm-token'),
        headers: {
          'Authorization': 'Bearer $idToken',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({'fcm_token': fcmToken}),
      );

      return response.statusCode == 200;
    } catch (e) {
      print('API Error: $e');
      return false;
    }
  }

  // 登出
  Future<bool> logout(String idToken) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/auth/firebase/logout'),
        headers: {
          'Authorization': 'Bearer $idToken',
          'Accept': 'application/json',
        },
      );

      return response.statusCode == 200;
    } catch (e) {
      print('API Error: $e');
      return false;
    }
  }

  // 取得啤酒列表（範例 API 呼叫）
  Future<List<dynamic>?> getBeers(String idToken) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/beers'),
        headers: {
          'Authorization': 'Bearer $idToken',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['data'] as List<dynamic>;
      }
      return null;
    } catch (e) {
      print('API Error: $e');
      return null;
    }
  }
}
```

### 4. 建立登入畫面

`lib/screens/auth_screen.dart`

```dart
import 'package:flutter/material.dart';
import '../services/firebase_auth_service.dart';
import '../services/laravel_api_service.dart';
import 'home_screen.dart';

class AuthScreen extends StatefulWidget {
  @override
  _AuthScreenState createState() => _AuthScreenState();
}

class _AuthScreenState extends State<AuthScreen> {
  final FirebaseAuthService _authService = FirebaseAuthService();
  final LaravelApiService _apiService = LaravelApiService();
  bool _isLoading = false;

  Future<void> _handleGoogleSignIn() async {
    setState(() => _isLoading = true);

    try {
      // 1. Firebase Google 登入
      final userCredential = await _authService.signInWithGoogle();

      if (userCredential == null) {
        setState(() => _isLoading = false);
        return;
      }

      // 2. 取得 Firebase ID Token
      final idToken = await _authService.getIdToken();

      if (idToken == null) {
        throw Exception('無法取得 ID Token');
      }

      // 3. 發送到 Laravel 後端
      final result = await _apiService.loginWithFirebase(idToken);

      if (result != null) {
        // 登入成功，導航到主頁面
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => HomeScreen()),
        );
      } else {
        throw Exception('後端認證失敗');
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('登入失敗: $e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _handleAppleSignIn() async {
    setState(() => _isLoading = true);

    try {
      final userCredential = await _authService.signInWithApple();

      if (userCredential == null) {
        setState(() => _isLoading = false);
        return;
      }

      final idToken = await _authService.getIdToken();

      if (idToken == null) {
        throw Exception('無法取得 ID Token');
      }

      final result = await _apiService.loginWithFirebase(idToken);

      if (result != null) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => HomeScreen()),
        );
      } else {
        throw Exception('後端認證失敗');
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('登入失敗: $e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: _isLoading
            ? CircularProgressIndicator()
            : Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    'HoldYourBeer',
                    style: TextStyle(
                      fontSize: 32,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  SizedBox(height: 48),

                  // Google 登入按鈕
                  ElevatedButton.icon(
                    onPressed: _handleGoogleSignIn,
                    icon: Icon(Icons.g_mobiledata),
                    label: Text('使用 Google 登入'),
                    style: ElevatedButton.styleFrom(
                      minimumSize: Size(250, 50),
                    ),
                  ),

                  SizedBox(height: 16),

                  // Apple 登入按鈕（僅 iOS）
                  if (Theme.of(context).platform == TargetPlatform.iOS)
                    ElevatedButton.icon(
                      onPressed: _handleAppleSignIn,
                      icon: Icon(Icons.apple),
                      label: Text('使用 Apple 登入'),
                      style: ElevatedButton.styleFrom(
                        minimumSize: Size(250, 50),
                        backgroundColor: Colors.black,
                        foregroundColor: Colors.white,
                      ),
                    ),
                ],
              ),
      ),
    );
  }
}
```

### 5. 設定 FCM 推播通知（選用）

`lib/services/fcm_service.dart`

```dart
import 'package:firebase_messaging/firebase_messaging.dart';

class FCMService {
  final FirebaseMessaging _messaging = FirebaseMessaging.instance;

  Future<void> initialize() async {
    // 請求推播權限
    NotificationSettings settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      print('用戶授予推播權限');

      // 取得 FCM Token
      String? token = await _messaging.getToken();
      print('FCM Token: $token');

      // 發送到後端
      // await laravelApi.updateFcmToken(idToken, token);
    }

    // 處理前景通知
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print('收到前景通知: ${message.notification?.title}');
    });

    // 處理背景通知點擊
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      print('用戶點擊通知: ${message.data}');
    });
  }

  Future<String?> getToken() async {
    return await _messaging.getToken();
  }
}
```

---

## 完整認證流程

```
1. 用戶點擊「使用 Google 登入」
   ↓
2. Flutter 觸發 Google Sign In SDK
   ↓
3. 用戶在 Google 授權頁面登入
   ↓
4. Google 回傳用戶資訊
   ↓
5. Flutter 使用 Google 憑證登入 Firebase
   ↓
6. Firebase 回傳 Firebase User 和 ID Token
   ↓
7. Flutter 發送 ID Token 到 Laravel API
   POST /api/v1/auth/firebase/login
   Body: { "id_token": "...", "fcm_token": "..." }
   ↓
8. Laravel 驗證 Firebase ID Token
   ↓
9. Laravel 建立或更新用戶資料
   ↓
10. Laravel 回傳用戶資訊
    ↓
11. Flutter 儲存認證狀態，導航到主畫面
```

---

## 常見問題

### Q1: 如何處理 Token 過期？

Firebase ID Token 每小時會過期。使用以下方法自動刷新：

```dart
Future<String?> _getValidToken() async {
  final user = FirebaseAuth.instance.currentUser;
  if (user == null) return null;

  // getIdToken(true) 會強制刷新 token
  return await user.getIdToken(true);
}
```

### Q2: 如何在 API 請求中使用 Token？

每次 API 請求都應包含最新的 ID Token：

```dart
Future<void> _callApi() async {
  final token = await _authService.getIdToken();

  final response = await http.get(
    Uri.parse('$baseUrl/api/v1/beers'),
    headers: {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    },
  );
}
```

### Q3: 如何處理已登入用戶？

在 `main.dart` 檢查認證狀態：

```dart
class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      home: StreamBuilder<User?>(
        stream: FirebaseAuth.instance.authStateChanges(),
        builder: (context, snapshot) {
          if (snapshot.hasData) {
            return HomeScreen(); // 已登入
          }
          return AuthScreen(); // 未登入
        },
      ),
    );
  }
}
```

### Q4: 如何測試？

1. 使用 Firebase Console 的 Test Lab
2. 使用 Firebase Auth Emulator (本地測試)
3. 使用真實裝置測試 Google/Apple Sign In

---

## 參考資源

- [Firebase Auth Flutter 文件](https://firebase.google.com/docs/auth/flutter/start)
- [Google Sign-In Flutter 套件](https://pub.dev/packages/google_sign_in)
- [Sign in with Apple Flutter 套件](https://pub.dev/packages/sign_in_with_apple)
- [Firebase Cloud Messaging](https://firebase.google.com/docs/cloud-messaging/flutter/client)

---

## 下一步

完成 Flutter 整合後，請參考：
- `FIREBASE_SETUP.md` - Firebase Console 完整設定步驟
- `API_DOCUMENTATION.md` - 完整 API 端點說明
