# Session: 密碼驗證規則強化 - 必須包含英文與數字

**Date**: 2025-12-16
**Status**: ✅ Completed
**Duration**: 2 hours (Backend 1h + Flutter 1h)
**Contributors**: @kiddchan, Claude AI

**Tags**: #product #security #validation

**Categories**: Authentication, User Registration, Security Enhancement

---

## 📋 Overview

### Goal
強化密碼驗證規則，要求密碼必須同時包含英文字母與數字，但不強制要求大小寫混合，以提升帳號安全性同時維持使用者友善性。同時完成 Flutter 前端的同步更新，確保前後端密碼提示文字一致，並修復英文版 UI 破版問題。

### Related Documents
- **Feature Spec**: `spec/features/user-registration.feature`
- **API Spec**: `spec/api/api.yaml`

---

## 🎯 Context

### Problem
原有的密碼驗證規則僅使用 Laravel 預設的 `Password::defaults()`，通常只要求最少 8 個字元，但不強制要求密碼複雜度，這可能導致使用者設定過於簡單的密碼（例如純英文 "password"），增加帳號被破解的風險。

### User Story
> As a product owner, I want to require users to create passwords with both letters and numbers, so that user accounts are more secure while remaining easy to create.

### Current State
- Laravel 專案使用預設的 `Password::defaults()` 規則
- 測試檔案使用 `'password'` 作為測試密碼
- UserFactory 預設密碼為 `'password'`

**Gap**: 缺乏對密碼複雜度的基本要求

---

## 💡 Planning

### Design Decisions

#### D1: 密碼規則設計
- **Options**:
  - A: 要求英文 + 數字 + 大小寫混合 + 特殊符號
  - B: 要求英文 + 數字 + 大小寫混合
  - C: 要求英文 + 數字（不限大小寫）
- **Chosen**: C - 要求英文 + 數字（不限大小寫）
- **Reason**:
  - 提供基本的安全性保護（相較於純英文密碼）
  - 使用者友善，不會因為規則過於複雜而造成註冊困難
  - 符合一般消費性應用的密碼要求水準
- **Trade-offs**:
  - 放棄了大小寫混合的要求，但換來更好的使用者體驗
  - 對於高安全性需求的應用來說可能不夠嚴格，但對於啤酒追蹤應用已經足夠

#### D2: 實作位置
- **Chosen**: 在 `AppServiceProvider` 中設定 `Password::defaults()`
- **Reason**:
  - 集中管理密碼規則
  - 所有使用 `Password::defaults()` 的地方自動套用
  - 符合 Laravel 最佳實踐

---

## ✅ Implementation Checklist

### Phase 1: 後端實作 [✅ Completed]
- [x] 修改 `AppServiceProvider` 設定 `Password::defaults()`
- [x] 更新 `UserFactory` 預設密碼為 `password123`
- [x] 更新所有測試檔案的測試密碼
  - [x] `tests/Feature/RegistrationTest.php`
  - [x] `tests/Feature/Auth/RegistrationTest.php`
  - [x] `tests/Feature/Api/V1/AuthControllerTest.php`
  - [x] `tests/Feature/Auth/AuthenticationTest.php`
  - [x] `tests/Feature/Auth/PasswordConfirmationTest.php`
  - [x] `tests/Feature/Auth/PasswordUpdateTest.php`
  - [x] `tests/Feature/ProfileTest.php`
  - [x] `tests/Feature/SocialLoginTest.php`
  - [x] `tests/Feature/OAuthLinkUnlinkTest.php`
- [x] 執行所有測試確認通過 (199 passed)

### Phase 2: 文件更新 [✅ Completed]
- [x] 檢查 spec 檔案密碼範例（已使用 `a_secure_password_123`，符合規則）
- [x] 建立 session 文件記錄修改

### Phase 3: Flutter 前端同步 [✅ Completed - 2025-12-16]
- [x] 更新密碼提示文字以符合驗證規則
  - [x] `authPasswordHint`: "Please enter your password" → "Enter your password"
  - [x] `authPasswordStrength`: 修正為 "At least 8 characters with letters and numbers"
  - [x] `authConfirmPasswordHint`: "Please re-enter your password" → "Re-enter your password"
  - [x] `authNameHint`: "Please enter your name" → "Enter your name"
- [x] 修復英文版 UI 破版問題
  - [x] 登入頁面：「記住我」與「忘記密碼」的 Row 布局使用 Flexible
  - [x] 註冊頁面：密碼 hint 文字縮短避免截斷
- [x] 修復多語言硬編碼問題
  - [x] Google 註冊按鈕：使用 `localizations.authGoogleSignUp`
  - [x] 分隔線「或」文字：使用 `localizations.authOrDivider`
- [x] 重新生成多語言檔案（`flutter gen-l10n`）
- [x] 執行 Flutter analyze 確認無錯誤

---

## 📊 Outcome

### What Was Built
1. **密碼驗證規則**: 在 `AppServiceProvider` 中設定全域密碼規則
   - 最少 8 個字元
   - 必須包含英文字母（不限大小寫）
   - 必須包含數字

2. **測試環境更新**: 更新所有測試檔案以符合新的密碼規則

3. **Flutter 前端同步**: 更新 Flutter 專案的密碼提示文字與 UI 修復
   - 修正密碼強度說明文字（移除不正確的大小寫要求）
   - 縮短所有 hint 文字以避免英文版 UI 破版
   - 修復多語言硬編碼問題
   - 改善登入/註冊頁面的響應式布局

### Files Created/Modified

#### Laravel Backend
```
app/
├── Providers/AppServiceProvider.php (modified)
database/
├── factories/UserFactory.php (modified)
tests/
├── Feature/
│   ├── RegistrationTest.php (modified)
│   ├── ProfileTest.php (modified)
│   ├── SocialLoginTest.php (modified)
│   ├── OAuthLinkUnlinkTest.php (modified)
│   └── Auth/
│       ├── RegistrationTest.php (modified)
│       ├── AuthenticationTest.php (modified)
│       ├── PasswordConfirmationTest.php (modified)
│       └── PasswordUpdateTest.php (modified)
│   └── Api/V1/
│       └── AuthControllerTest.php (modified)
docs/
└── sessions/2025-12/
    └── 16-password-validation-enhancement.md (new)
```

#### Flutter Frontend
```
HoldYourBeer-Flutter/
├── lib/
│   ├── l10n/
│   │   ├── app_en.arb (modified)
│   │   ├── app_localizations_en.dart (regenerated)
│   │   └── app_localizations_zh.dart (regenerated)
│   └── features/auth/
│       └── screens/
│           ├── login_screen.dart (modified)
│           └── register_screen.dart (modified)
```

### Metrics

#### Laravel Backend
- **Tests Passed**: 199/199 (9 skipped)
- **Test Files Modified**: 9 files
- **密碼範例更新**:
  - `password` → `password123`
  - `new-password` → `newpass123`
  - `wrong-password` → `wrongpass123`

#### Flutter Frontend
- **Files Modified**: 5 files (2 screens + 3 localization files)
- **Localization Keys Updated**: 5 keys (hint & strength messages)
- **UI Issues Fixed**:
  - Login screen overflow (100 pixels)
  - Register screen text truncation
  - Hardcoded Chinese text (2 instances)

---

## 🎓 Lessons Learned

### 1. 集中管理驗證規則的重要性

**Learning**: 使用 `Password::defaults()` 在 `AppServiceProvider` 中設定全域密碼規則，可以確保所有使用該規則的地方自動套用，避免規則不一致。

**Solution/Pattern**:
```php
// AppServiceProvider.php
Password::defaults(function () {
    return Password::min(8)
        ->letters()     // 必須包含英文字母（不限大小寫）
        ->numbers();    // 必須包含數字
});
```

**Future Application**: 未來如需調整密碼規則，只需修改一處即可全域生效。

### 2. UserFactory 預設值的影響範圍

**Learning**: `UserFactory` 的預設密碼會被大量測試使用，修改 factory 預設值比逐一修改每個測試更有效率。

**Solution/Pattern**:
```php
// UserFactory.php
'password' => static::$password ??= Hash::make('password123'),
```

**Future Application**: 對於會被廣泛使用的 factory 屬性，應優先在 factory 中設定符合驗證規則的預設值。

### 3. 密碼規則與使用者體驗的平衡

**Learning**: 密碼規則不是越複雜越好，需要在安全性與使用者體驗之間取得平衡。

**Decision Rationale**:
- ✅ 採用「英文 + 數字」：提供基本安全性，使用者容易記憶
- ❌ 不採用「大小寫混合」：避免使用者在註冊時因為規則過於複雜而放棄
- ❌ 不採用「特殊符號」：對於一般消費性應用來說過於嚴格

**Future Application**: 對於不同類型的應用，應根據其安全性需求調整密碼規則的複雜度。

### 4. 前後端密碼提示一致性的重要性

**Learning**: Flutter 前端的密碼提示文字必須與後端驗證規則保持一致，否則會誤導使用者。

**Problem Identified**:
- 前端文字：「必須包含大寫、小寫和數字」
- 後端驗證：只要求「至少一個字母（不限大小寫）+ 數字」
- 結果：使用者可能設定符合規則的密碼（如 `password123`），但被前端提示誤導以為需要大小寫混合

**Solution**:
```dart
// app_en.arb - 修正前
"authPasswordStrength": "Password must be at least 8 characters with uppercase, lowercase, and numbers"

// app_en.arb - 修正後
"authPasswordStrength": "At least 8 characters with letters and numbers"
```

**Future Application**: 在修改後端驗證規則時，必須同步檢查並更新前端的提示文字。

### 5. 響應式 UI 與多語言文字長度考量

**Learning**: 英文文字通常比中文長，在設計 UI 時必須考慮文字長度變化可能導致的破版問題。

**Problem Identified**:
- 登入頁面：「Remember me」+ 「Forgot your password?」在同一行，英文版溢出 100 pixels
- 註冊頁面：「Please enter your password」太長，導致 hint 文字被截斷

**Solution**:
```dart
// 方案 1: 使用 Flexible 讓元件彈性調整
Row(
  children: [
    Flexible(child: _buildRememberMeCheckbox()),
    Flexible(child: _buildForgotPasswordLink()),
  ],
)

// 方案 2: 縮短文字
"Please enter your password" → "Enter your password"
```

**Future Application**:
- 在設計 UI 布局時，預留足夠的空間給較長的語言文字
- 使用 `Flexible` 或 `Expanded` 讓文字區域能夠自適應
- 對於 hint 文字，盡量簡潔明瞭，避免不必要的冗長

---

## ✅ Completion

**Status**: ✅ Completed
**Completed Date**: 2025-12-16
**Session Duration**:
- Backend Implementation: 1 hour (2025-12-16 上午)
- Flutter Frontend Sync: 1 hour (2025-12-16 下午)
- Total: 2 hours

---

## 🔮 Future Improvements

### Not Implemented (Intentional)
- ⏳ **密碼強度指示器**: 前端可以加入密碼強度視覺化指示器，幫助使用者了解密碼安全性
- ⏳ **自訂錯誤訊息**: 目前使用 Laravel 預設錯誤訊息，未來可以自訂更友善的中文錯誤訊息

### Potential Enhancements
- 📌 **密碼歷史記錄**: 防止使用者重複使用最近N次的密碼
- 📌 **常見密碼黑名單**: 阻止使用者使用常見的弱密碼（如 "password123", "12345678"）
- 📌 **密碼過期機制**: 對於高安全性需求的場景，可以要求定期更換密碼

### Technical Debt
- ✅ **Flutter 端驗證**: ~~目前僅更新後端驗證，Flutter 端尚未同步更新~~（已完成 - 2025-12-16）
- 🔧 **API 文件更新**: OpenAPI 規格檔案尚未更新密碼要求說明（待後續處理）

---

## 🔗 References

### Laravel 文件
- [Password Validation](https://laravel.com/docs/11.x/validation#validating-passwords)
- [Password Validation Rules](https://laravel.com/docs/11.x/validation#rule-password)

### 密碼安全最佳實踐
- [NIST Password Guidelines](https://pages.nist.gov/800-63-3/sp800-63b.html)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)

---

## 📝 Implementation Details

### 密碼驗證規則配置

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Validation\Rules\Password;

public function boot(): void
{
    // Set default password validation rules
    // 設定預設密碼驗證規則：至少 8 個字元，必須包含英文字母與數字
    Password::defaults(function () {
        return Password::min(8)
            ->letters()     // 必須包含英文字母（不限大小寫）
            ->numbers();    // 必須包含數字
    });

    // ... 其他設定
}
```

### 測試密碼更新策略

1. **UserFactory 預設密碼**: `password` → `password123`
2. **註冊測試密碼**: `password` → `password123`
3. **密碼更新測試**: `new-password` → `newpass123`
4. **錯誤密碼測試**: `wrong-password` → `wrongpass123`

### 驗證規則說明

| 規則 | 說明 | 範例 |
|------|------|------|
| `min(8)` | 最少 8 個字元 | ✅ `pass123` (7字元) ❌<br>✅ `pass1234` (8字元) ✅ |
| `letters()` | 必須包含英文字母 | ✅ `12345678` ❌<br>✅ `pass1234` ✅ |
| `numbers()` | 必須包含數字 | ✅ `password` ❌<br>✅ `password1` ✅ |

### Flutter 前端實作細節

#### 1. 密碼驗證邏輯

```dart
// lib/features/auth/screens/register_screen.dart
validator: (value) {
  if (value == null || value.isEmpty) {
    return localizations.authPasswordRequired;
  }
  if (value.length < 8) {
    return localizations.authPasswordStrength;
  }
  // 至少包含一個英文字母（大寫或小寫）
  if (!RegExp(r'[A-Za-z]').hasMatch(value)) {
    return localizations.authPasswordStrength;
  }
  // 至少包含一個數字
  if (!RegExp(r'[0-9]').hasMatch(value)) {
    return localizations.authPasswordStrength;
  }
  return null;
}
```

#### 2. 多語言文字更新

```dart
// lib/l10n/app_en.arb
{
  // 縮短 hint 文字避免 UI 破版
  "authNameHint": "Enter your name",           // 原: "Please enter your name"
  "authEmailHint": "Enter your email",         // 原: "Please enter your email"
  "authPasswordHint": "Enter your password",   // 原: "Please enter your password"
  "authConfirmPasswordHint": "Re-enter your password",  // 原: "Please re-enter your password"

  // 修正密碼強度說明（移除不正確的大小寫要求）
  "authPasswordStrength": "At least 8 characters with letters and numbers",
  // 原: "Password must be at least 8 characters with uppercase, lowercase, and numbers"
}
```

#### 3. UI 響應式布局修復

```dart
// lib/features/auth/screens/login_screen.dart
// 修復「記住我 + 忘記密碼」溢出問題
Row(
  mainAxisAlignment: MainAxisAlignment.spaceBetween,
  children: [
    Flexible(child: _buildRememberMeCheckbox()),  // 添加 Flexible
    Flexible(child: _buildForgotPasswordLink()),  // 添加 Flexible
  ],
)

// 「忘記密碼」按鈕文字處理
Text(
  localizations.authForgotPassword,
  textAlign: TextAlign.right,
  maxLines: 2,                      // 允許最多兩行
  overflow: TextOverflow.fade,       // 溢出時使用淡出效果
)
```

#### 4. 修復硬編碼文字

```dart
// lib/features/auth/screens/register_screen.dart
// Google 註冊按鈕
GoogleSignInButton(
  text: localizations.authGoogleSignUp,  // ✅ 使用多語言
  // 原: text: '使用 Google 帳號註冊',    ❌ 硬編碼中文
)

// 分隔線「或」文字
Text(
  localizations.authOrDivider,           // ✅ 使用多語言
  // 原: '或',                            ❌ 硬編碼中文
)
```

---

## 📌 Next Steps

1. ✅ 後端實作完成
2. ⏳ 更新 API 文件（OpenAPI spec）
3. ✅ 實作 Flutter 端密碼驗證（已完成 - 2025-12-16）
4. ✅ 更新 session 文件記錄 Flutter 端修改（已完成 - 2025-12-16）

