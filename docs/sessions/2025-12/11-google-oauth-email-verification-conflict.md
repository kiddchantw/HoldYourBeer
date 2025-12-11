# Session: Google OAuth 與 Email 驗證機制衝突分析

**Date**: 2025-12-11
**Status**: ✅ 完成
**Duration**: 1 hour
**Contributors**: @kiddchan, Claude AI

**Tags**: #architecture #decisions #authentication

**Categories**: Authentication, OAuth, Email Verification

**Commits**: 待建立 commit

---

## 📋 Overview

### Goal
分析並解決 Google OAuth 登入與傳統 Email 驗證機制之間的潛在衝突。

### Related Documents
- **Auth Routes**: `routes/auth.php`, `routes/web.php`
- **Controller**: `app/Http/Controllers/SocialLoginController.php`
- **Model**: `app/Models/User.php`
- **Migration**: `database/migrations/0001_01_01_000000_create_users_table.php`

---

## 🎯 Context

### Problem
專案同時實作了兩種註冊登入方式：
1. **傳統 Email 註冊**：需要信箱驗證（`MustVerifyEmail` interface）
2. **Google OAuth 登入**：Google 已驗證信箱，理論上不需再驗證

這兩種機制在帳號合併時會產生衝突。

### User Story
> 身為使用者，我希望可以用 Email 註冊後，之後也能用 Google 登入同一帳號，不需要重複驗證信箱。

### Current State

#### 現有實作
1. **User Model** (`app/Models/User.php:11`)
   - 實作 `MustVerifyEmail` interface
   - 所有使用者都被強制要求驗證信箱

2. **SocialLoginController** (`app/Http/Controllers/SocialLoginController.php`)
   ```php
   // Line 53-68
   $user = User::where('email', $socialUser->getEmail())->first();

   if ($user) {
       // 已存在使用者 - 直接登入（❌ 沒有更新驗證狀態）
       Auth::login($user, true);
   } else {
       // 新使用者 - 建立並設定 email_verified_at
       $user = User::create([
           'email_verified_at' => now(), // ✅ 新用戶有設定
       ]);
   }
   ```

3. **Routes 中介軟體** (`routes/web.php:29`, `web.php:113`)
   ```php
   Route::get('/dashboard', [DashboardController::class, 'index'])
       ->middleware(['auth', 'verified']); // ← 會擋住未驗證的使用者
   ```

4. **Database Schema**
   - ❌ 缺少 `provider` 欄位（無法區分登入方式）
   - ❌ 缺少 `provider_id` 欄位（無法儲存 OAuth user ID）

**Gap**: 無法處理「Email 註冊但未驗證 → 之後用 Google 登入」的情境

---

## ⚠️ 衝突點分析

### 衝突 1: 已存在帳號的驗證狀態不更新
**檔案**: `app/Http/Controllers/SocialLoginController.php:53-58`

**情境**:
```
1. 使用者用 test@example.com 註冊（未驗證信箱）
   → users table: email_verified_at = NULL

2. 使用者用 Google 登入（同一 email）
   → SocialLoginController 找到已存在使用者
   → 直接 Auth::login($user)
   → email_verified_at 仍是 NULL ❌

3. 使用者嘗試進入 /dashboard
   → middleware(['auth', 'verified']) 檢查
   → email_verified_at 是 NULL
   → 被導向驗證信箱頁面 ❌
```

**影響**: Google 登入的使用者無法使用需要驗證的功能

---

### 衝突 2: 缺少登入方式識別
**檔案**: `database/migrations/0001_01_01_000000_create_users_table.php`

**問題**:
- 無 `provider` 欄位（'local', 'google', 'facebook' 等）
- 無 `provider_id` 欄位（OAuth 提供者的 user ID）

**影響**:
- 無法判斷使用者是透過哪種方式註冊
- 無法處理「同一使用者多個登入方式」的情境
- 無法追蹤 OAuth 帳號的原始 ID

---

### 衝突 3: MustVerifyEmail 對所有使用者生效
**檔案**: `app/Models/User.php:11`

```php
class User extends Authenticatable implements MustVerifyEmail
```

**問題**:
- OAuth 使用者理論上不需驗證（Google 已驗證過）
- 但此設定對所有使用者生效

**影響**:
- 邏輯上不一致
- 可能造成混淆（OAuth 使用者為何要驗證信箱？）

---

## 💡 Planning

### Approach Analysis

#### Option A: 完整的多 Provider 架構 [⏳ 推薦但複雜]

**實作**:
1. 新增 Migration 加入 `provider` 和 `provider_id` 欄位
2. 修改 SocialLoginController 的帳號合併邏輯
3. 建立自訂 Middleware 處理不同 provider 的驗證需求
4. 更新 User Model 的 fillable 欄位

**Pros**:
- ✅ 完整支援多種登入方式
- ✅ 資料結構清晰，易於擴充（未來可加 Facebook, Apple 登入）
- ✅ 符合業界標準實務
- ✅ 可追蹤每個使用者的登入方式

**Cons**:
- ❌ 需要建立新 Migration
- ❌ 需要修改多個檔案
- ❌ 需要測試多種情境組合
- ❌ 實作時間較長

---

#### Option B: 最小修改 - 只修正合併邏輯 [✅ CHOSEN]

**實作**:
```php
// SocialLoginController.php
$user = User::where('email', $socialUser->getEmail())->first();

if ($user) {
    // 更新驗證狀態（OAuth 使用者視為已驗證）
    if (!$user->email_verified_at) {
        $user->email_verified_at = now();
        $user->save();
    }
    Auth::login($user, true);
} else {
    // 建立新使用者（同現有邏輯）
    $user = User::create([...]);
}
```

**Pros**:
- ✅ 最小化修改
- ✅ 快速解決當前問題
- ✅ 不需要 Migration
- ✅ 向下相容（不影響現有資料）

**Cons**:
- ❌ 無法區分登入方式
- ❌ 無法追蹤 OAuth provider ID
- ❌ 未來擴充受限

**Decision Rationale**:
根據開發哲學「**增量修改優於重構**」，先採用 Option B 快速修復當前問題，未來若需支援多種 OAuth 提供者再升級為 Option A。

---

## ✅ Implementation Checklist

### Phase 1: 緊急修復 [✅ Completed]
- [x] 修改 `SocialLoginController::handleProviderCallback()`
- [x] 加入自動更新 `email_verified_at` 邏輯
- [x] 撰寫測試案例驗證修復

### Phase 2: 測試驗證 [✅ Completed]
- [x] 測試情境 1: Email 註冊未驗證 → Google 登入
- [x] 測試情境 2: Email 註冊已驗證 → Google 登入
- [x] 測試情境 3: Google 登入 → 嘗試用 Email 登入（已存在測試）
- [x] 測試情境 4: 純 Google 新用戶註冊（已存在測試）

### Phase 3: 文件更新 [✅ Completed]
- [x] 更新 Session 文件記錄實作結果
- [x] 記錄已知限制（無法追蹤 provider）
- [x] 標記未來改進方向（Option A）

---

## 🚧 Blockers & Solutions

### Blocker 1: 是否需要通知使用者帳號已合併？ [✅ RESOLVED]
- **Issue**: 使用者用 Google 登入時，系統自動將未驗證的帳號升級為已驗證，使用者不知情
- **Impact**: 可能造成安全疑慮（使用者：「我沒驗證信箱怎麼可以登入？」）
- **解決方案選項**:
  1. **靜默合併**（當前方案）- 不通知，直接更新
  2. **通知但不阻擋** - 登入後顯示提示訊息
  3. **要求確認** - 需要使用者確認才合併
- **決定**: 採用方案 1（靜默合併），因為 Google 驗證可信度高於 Email 驗證
- **Resolved**: 2025-12-11

---

## 📊 Outcome

### What Was Built
1. **修復 SocialLoginController** - 加入自動驗證邏輯
2. **新增測試案例** - 確保修復有效且不破壞既有功能
3. **完整文件記錄** - 包含問題分析、決策過程、實作細節

### Files Created/Modified
```
app/
├── Http/
│   └── Controllers/
│       └── SocialLoginController.php (modified - Line 55-62)
tests/
├── Feature/
│   └── SocialLoginTest.php (modified - 新增 2 個測試案例)
docs/
├── sessions/
│   └── 2025-12/
│       └── 11-google-oauth-email-verification-conflict.md (new)
```

### 核心修改

#### 1. SocialLoginController.php (Line 55-62)
```php
if ($user) {
    // Existing user - update verification status if needed
    // OAuth providers (like Google) have already verified the email
    if (!$user->email_verified_at) {
        $user->email_verified_at = now();
        $user->save();
    }
    Auth::login($user, true);
}
```

#### 2. SocialLoginTest.php - 新增測試
- `existing_unverified_user_gets_verified_when_login_with_google()`
- `existing_verified_user_keeps_verification_when_login_with_google()`
- 修正 `mockSocialiteUser()` 加入 `stateless()` mock

---

## 🎓 Lessons Learned

### 1. OAuth 與傳統驗證的相容性設計
**Learning**:
在設計認證系統時，應該在初期就考慮多種登入方式的共存，而非後期補強。

**Solution/Pattern**:
- 資料表設計時預留 `provider` 和 `provider_id` 欄位
- 使用 `email_verified_at` 作為「帳號可信度」的統一指標
- OAuth 登入視為「更高信任度的驗證方式」

**Future Application**:
未來若新增 Facebook、Apple 登入，應採用 Option A 的完整架構。

---

### 2. 中介軟體的驗證邏輯應考慮多元登入
**Learning**:
`verified` middleware 假設所有使用者都需驗證信箱，但 OAuth 使用者本質上已被第三方驗證。

**Solution/Pattern**:
兩種解決方式：
1. OAuth 登入時自動設定 `email_verified_at`（採用）
2. 自訂 middleware 跳過 OAuth 使用者的驗證檢查（需 provider 欄位）

---

## 🔮 Future Improvements

### Not Implemented (Intentional)
- ⏳ **多 Provider 支援** - 當前只有 Google，未來若需 Facebook/Apple 再實作
- ⏳ **Provider ID 追蹤** - 目前不追蹤 OAuth 的原始 ID（Google user ID）
- ⏳ **帳號合併通知** - 靜默合併，不發送通知信

### Potential Enhancements
- 📌 **支援多個 OAuth Provider 綁定同一帳號**
  - 例如：同一使用者可用 Google、Facebook、Apple 登入
  - 需要獨立的 `user_providers` 資料表

- 📌 **OAuth 登入後要求補充資料**
  - Google 可能只提供 email 和 name
  - 可在首次登入後導向「完善資料」頁面

- 📌 **安全審計日誌**
  - 記錄使用者透過哪種方式登入
  - 追蹤「帳號自動合併」事件

### Technical Debt
- 🔧 **無法區分使用者的註冊來源**
  - 當前設計無法得知使用者是「Email 註冊」還是「Google 註冊」
  - 若未來需此資訊（例如：分析不同管道的使用者行為），需重構

- 🔧 **缺少防範 Email 劫持的機制**
  - 情境：惡意使用者用他人 email 註冊但不驗證
  - 真實擁有者用 Google 登入時，會合併到惡意帳號
  - 建議：檢查帳號建立時間，若太新則要求額外驗證

- 🔧 **OAuth 使用者無法設定密碼**
  - Google 註冊的使用者無法設定密碼作為備用登入方式
  - 因為隨機密碼未知，無法通過「修改密碼」的 current_password 驗證
  - 詳見下方「密碼設定問題」章節

---

## 🔗 References

### Related Work
- Laravel Socialite 官方文件: https://laravel.com/docs/11.x/socialite
- Laravel Email Verification: https://laravel.com/docs/11.x/verification

### External Resources
- [OAuth 2.0 最佳實務](https://oauth.net/2/)
- [Multi-Provider Authentication Patterns](https://auth0.com/docs/authenticate/identity-providers)

### 相關 Issues
- 需要建立 Issue 追蹤此修復

---

## 💭 Discussion Points

### Q1: 是否應該阻止「Email 未驗證的帳號」被 Google 登入合併？
**當前決策**: 否，Google 驗證可信度更高，應允許自動升級。

**理由**:
- Google OAuth 的驗證流程比 Email 驗證更安全
- 使用者體驗更好（不需要再去收信）
- 降低棄用率（不因驗證問題擋住使用者）

---

### Q2: Password 欄位對 OAuth 使用者是否多餘？
**當前狀態**: OAuth 使用者會被設定隨機密碼（`Hash::make(Str::random(16))`）

**問題**:
- 這些隨機密碼永遠不會被使用
- 使用者無法用 Email + Password 登入（因為不知道密碼）

**建議**:
- 短期：維持現狀，password 欄位 NOT NULL
- 長期：考慮 password 欄位改為 nullable，OAuth 使用者設為 NULL

---

### Metrics
- **Code Coverage**: 測試覆蓋率維持穩定
- **Lines Added**: ~10 (Controller + 註解)
- **Lines Modified in Tests**: ~20
- **Test Files**: 1 modified (SocialLoginTest.php)
- **Test Cases**: 7 passed (2 new + 5 existing)

---

## ✅ Completion

**Status**: ✅ 完成
**Completed Date**: 2025-12-11
**Session Duration**: 1 hour

### 測試結果
```
Tests:  7 passed (39 assertions)
Duration: 0.41s

✓ user can login with google
✓ user can login with apple
✓ existing user can login with google
✓ existing unverified user gets verified when login with google [NEW]
✓ existing verified user keeps verification when login with google [NEW]
✓ existing user can login with apple
✓ social login redirects to login on failure
```

### ✅ 完成項目（Phase 1-3: Email 驗證衝突修復）
1. ✅ 分析並找出衝突點
2. ✅ 實作最小修改方案
3. ✅ 撰寫 2 個新測試案例
4. ✅ 修正既有測試的 mock 設定
5. ✅ 所有測試通過（7 passed）

### ✅ 完成項目（Phase 4: Provider 欄位與密碼設定）
1. ✅ 完整評估三種解決方案（A, B, C）
2. ✅ 實作方案 B（加入 provider 欄位）
3. ✅ 建立 Migration 與更新 Model
4. ✅ 修改 3 個 Controllers
5. ✅ 撰寫 5 個新測試案例（OAuthPasswordSetTest）
6. ✅ 所有測試通過（12 passed, 60 assertions）
7. ✅ 更新 OpenAPI 規格
8. ✅ 完整文件記錄

### ⏳ 後續步驟
1. ⏳ **API Response 調整**: 考慮在 AuthController 的 user 物件中加入 `provider` 欄位
2. ⏳ **Flutter 端適配**:
   - 重新產生 API client（包含 provider 欄位）
   - 調整密碼設定畫面 UI（OAuth 使用者不顯示「目前密碼」欄位）
3. ⏳ 建立 Git commit
4. ⏳ 部署到生產環境
5. ⏳ 監控合併邏輯是否正常運作

---

**備註**: 此 Session 涵蓋完整的問題分析、三種方案評估、完整實作與測試驗證，並為 Flutter 端整合預留了清晰的接口。

---

## 🔄 後續發現：密碼設定問題

### 問題描述

在完成 Email 驗證衝突修復後，發現了另一個使用者體驗問題：

#### 情境 1: Google 使用者想設定密碼
```
使用者：「我想設定一個密碼，萬一 Google 帳號有問題還能用密碼登入」
系統：PasswordController 要求輸入 current_password
使用者：「我是 Google 註冊的，系統給我隨機密碼我根本不知道」
結果：❌ 無法設定密碼
```

#### 情境 2: Email 使用者可以雙向登入
```
使用者：用 email+password 註冊
→ 之後用 Google 登入（同一 email）✅
→ 可以修改密碼 ✅（知道原密碼）
→ 兩種方式都能用 ✅
```

#### 情境 3: Google 使用者被鎖定
```
使用者：用 Google 註冊
→ 密碼是隨機的：Hash::make(Str::random(16))
→ 想修改密碼 ❌（不知道 current_password）
→ 只能用 Google 登入，沒有備用方案
```

### 根本原因

**PasswordController.php:19**
```php
'current_password' => ['required', 'current_password'],
```

此驗證規則不區分「設定密碼」vs「修改密碼」：
- Email 使用者：有真實密碼，可以修改 ✅
- OAuth 使用者：隨機密碼未知，卡住 ❌

### 使用者行為對照表

| 情境 | Email 註冊者 | Google 註冊者 |
|------|-------------|--------------|
| **用 Email+密碼登入** | ✅ 可以 | ❌ 不行（不知道隨機密碼） |
| **用 Google 登入** | ✅ 可以（自動合併） | ✅ 可以 |
| **修改密碼** | ✅ 可以（需輸入舊密碼） | ❌ 不行（不知道隨機密碼） |
| **兩種方式互換使用** | ✅ 都能用 | ❌ 只能用 Google |

---

## 💡 解決方案評估

### 方案 A: 允許 OAuth 使用者「設定」密碼

**概念**：區分「首次設定密碼」與「修改密碼」

**實作草稿**：
```php
// PasswordController.php
public function update(Request $request): RedirectResponse
{
    $user = $request->user();

    // 嘗試判斷是否為 OAuth 使用者
    $isOAuthUser = $this->isRandomPassword($user->password);

    if ($isOAuthUser) {
        // OAuth 使用者：設定密碼（不需要 current_password）
        $validated = $request->validateWithBag('updatePassword', [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);
    } else {
        // 傳統使用者：修改密碼（需要 current_password）
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);
    }

    $user->update(['password' => Hash::make($validated['password'])]);
    return back()->with('status', 'password-updated');
}

private function isRandomPassword(string $hashedPassword): bool
{
    // ❌ 問題：無法可靠判斷密碼是否為隨機產生的
    // Hash 是單向的，無法還原或驗證
    return false;
}
```

**優點**：
- ✅ 邏輯清晰（設定 vs 修改）
- ✅ 最小化程式碼改動

**缺點**：
- ❌ **無法可靠判斷密碼是否為隨機**
  - Hash 是單向加密，無法驗證原始值
  - 即使比對長度/格式也不可靠（使用者可能設定類似格式的密碼）
- ❌ 需要猜測使用者來源（不精確）

**評價**: ❌ 不可行（技術上無法實作）

---

### 方案 B: 加入 `provider` 欄位 [✅ 推薦]

**概念**：明確記錄使用者的註冊來源

**Database Schema**：
```php
// Migration
Schema::table('users', function (Blueprint $table) {
    $table->string('provider')->nullable()->after('password');
    // 'local', 'google', 'apple', null (legacy)

    $table->string('provider_id')->nullable()->after('provider');
    // OAuth provider 的 user ID
});
```

**User Model**：
```php
protected $fillable = [
    'name', 'email', 'password', 'role', 'email_verified_at',
    'provider', 'provider_id', // 新增
];
```

**SocialLoginController**：
```php
// 新使用者
$user = User::create([
    'name' => $socialUser->getName(),
    'email' => $socialUser->getEmail(),
    'password' => Hash::make(Str::random(16)),
    'email_verified_at' => now(),
    'provider' => 'google', // 新增
    'provider_id' => $socialUser->getId(), // 新增
]);

// 既有使用者（首次用 OAuth 登入）
if ($user && !$user->provider) {
    $user->update([
        'provider' => 'google',
        'provider_id' => $socialUser->getId(),
        'email_verified_at' => $user->email_verified_at ?? now(),
    ]);
}
```

**PasswordController**：
```php
public function update(Request $request): RedirectResponse
{
    $user = $request->user();

    // 明確判斷：OAuth 使用者 vs Email 使用者
    if (in_array($user->provider, ['google', 'apple', 'facebook'])) {
        // OAuth 使用者：首次設定密碼
        $validated = $request->validateWithBag('updatePassword', [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        // 設定密碼後，可選：清除 provider（允許改用 local）
        // $user->provider = 'local';
    } else {
        // Local 使用者：修改密碼
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);
    }

    $user->update(['password' => Hash::make($validated['password'])]);
    return back()->with('status', 'password-updated');
}
```

**優點**：
- ✅ 精確判斷使用者來源
- ✅ 可追蹤 OAuth provider ID（防止帳號衝突）
- ✅ 可擴充支援多個 OAuth 提供者
- ✅ 符合業界標準實務

**缺點**：
- ❌ 需要建立 Migration（會影響現有資料）
- ❌ 需要處理 Legacy 資料（`provider = null` 的既有使用者）
- ❌ 需要更新測試案例

**評價**: ✅ **推薦**（最完整的解決方案）

---

### 方案 C: 密碼改為 `nullable`

**概念**：`password = null` 代表「沒有密碼」

**Database Schema**：
```php
// Migration
Schema::table('users', function (Blueprint $table) {
    $table->string('password')->nullable()->change();
});
```

**SocialLoginController**：
```php
$user = User::create([
    'name' => $socialUser->getName(),
    'email' => $socialUser->getEmail(),
    'password' => null, // 改為 null（而非隨機密碼）
    'email_verified_at' => now(),
]);
```

**PasswordController**：
```php
public function update(Request $request): RedirectResponse
{
    $user = $request->user();

    if ($user->password === null) {
        // 首次設定密碼
        $validated = $request->validateWithBag('updatePassword', [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);
    } else {
        // 修改密碼
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);
    }

    $user->update(['password' => Hash::make($validated['password'])]);
    return back()->with('status', 'password-updated');
}
```

**Auth 登入邏輯需調整**：
```php
// AuthenticatedSessionController 或 Middleware
if ($user->password === null) {
    // 無密碼的使用者不能用 Email+Password 登入
    throw ValidationException::withMessages([
        'email' => 'Please login with Google.',
    ]);
}
```

**優點**：
- ✅ 簡單直觀（`null` = 沒密碼）
- ✅ 不需要 `provider` 欄位也能運作
- ✅ 判斷邏輯清晰

**缺點**：
- ❌ 需要 Migration 修改 `password` 為 nullable
- ❌ 需要修改所有 Auth 流程（確保 null 密碼不能登入）
- ❌ 無法追蹤使用者是從哪個 OAuth provider 註冊
- ❌ 無法支援「同一使用者綁定多個 OAuth」

**評價**: ⚠️ **可行但不推薦**（解決了密碼問題，但失去了 provider 追蹤能力）

---

## 🎯 方案決策

### 選擇：方案 B（加入 provider 欄位）

**理由**：
1. **最完整** - 解決所有問題（密碼設定 + 來源追蹤 + 未來擴充）
2. **標準實務** - 符合業界 OAuth 整合的標準做法
3. **可擴充性** - 未來若需支援 Facebook、Apple 登入，架構已就緒
4. **資料完整性** - 可追蹤每個使用者的註冊來源與 OAuth ID

**實作計畫**：
1. ✅ 建立 Migration 加入 `provider` 和 `provider_id`
2. ✅ 修改 User Model 的 fillable
3. ✅ 更新 SocialLoginController 設定 provider
4. ✅ 修改 PasswordController 區分設定/修改密碼
5. ✅ 撰寫測試案例
6. ✅ 處理 Legacy 資料（既有使用者 `provider = null`）

**Trade-offs（取捨）**：
- 犧牲：需要 Migration（短期開發成本）
- 獲得：長期維護性、可擴充性、使用者體驗改善

---

## 📝 實作記錄

### Phase 4: Provider 欄位實作 [✅ 已完成]

#### 4.1 Database Schema 變更
- [x] 建立 Migration: `2025_12_11_154607_add_provider_fields_to_users_table.php`
  - 新增 `provider` 欄位 (nullable string)
  - 新增 `provider_id` 欄位 (nullable string)
  - 新增 composite index `['provider', 'provider_id']`

#### 4.2 Model 層修改
- [x] **User Model** (`app/Models/User.php`)
  - 新增 `provider`, `provider_id` 到 `$fillable`
  - 新增 helper method: `isOAuthUser()` - 判斷是否為 OAuth 使用者
  - 新增 helper method: `isLocalUser()` - 判斷是否為 local 使用者

#### 4.3 Controller 層修改
- [x] **SocialLoginController** (`app/Http/Controllers/SocialLoginController.php`)
  - 新使用者：設定 `provider` 和 `provider_id`
  - 既有使用者首次 OAuth 登入：更新 `provider` 和 `provider_id`
  - 保留原有驗證狀態更新邏輯

- [x] **PasswordController** (`app/Http/Controllers/Auth/PasswordController.php`)
  - OAuth 使用者：不需要 `current_password` 即可設定密碼
  - Local/Legacy 使用者：必須提供 `current_password` 才能修改密碼
  - 使用 `$user->isOAuthUser()` 判斷

- [x] **RegisteredUserController** (`app/Http/Controllers/Auth/RegisteredUserController.php`)
  - Email/Password 註冊：設定 `provider = 'local'`

#### 4.4 測試驗證
- [x] 更新 `SocialLoginTest.php`
  - 驗證 OAuth 使用者的 provider 欄位正確設定
  - 驗證既有 local 使用者 OAuth 登入後 provider 不變

- [x] 新增 `OAuthPasswordSetTest.php` (5 個測試案例)
  - ✅ OAuth 使用者可設定密碼（無需 current_password）
  - ✅ Local 使用者必須提供 current_password
  - ✅ Local 使用者提供正確 current_password 可修改
  - ✅ OAuth 使用者設定密碼後可雙向登入
  - ✅ Legacy 使用者（provider=null）視為 local 使用者

- [x] 執行測試結果：**12 passed (60 assertions)**

#### 4.5 OpenAPI 規格更新
- [x] 執行 `php artisan scribe:generate --force`
- [x] 產生規格檔案：`storage/app/private/scribe/openapi.yaml`

### 實作總結

**修改的檔案**：
```
database/migrations/
└── 2025_12_11_154607_add_provider_fields_to_users_table.php (new)

app/Models/
└── User.php (modified - 新增 fillable 與 helper methods)

app/Http/Controllers/
├── SocialLoginController.php (modified - 設定 provider)
├── Auth/
│   ├── PasswordController.php (modified - 區分 OAuth/Local)
│   └── RegisteredUserController.php (modified - 設定 local provider)

tests/Feature/
├── SocialLoginTest.php (modified - 新增 provider 驗證)
└── Auth/
    └── OAuthPasswordSetTest.php (new - 5 個測試案例)
```

**新增的功能**：
1. ✅ 追蹤使用者註冊來源（local, google, apple, facebook）
2. ✅ OAuth 使用者可設定密碼作為備用登入方式
3. ✅ 支援雙重登入（OAuth + Email/Password）
4. ✅ Legacy 使用者向下相容（provider=null 視為 local）

**測試覆蓋率**：
- Social Login: 7 個測試案例全過
- OAuth Password Set: 5 個測試案例全過
- 總計：12 passed (60 assertions)

---

## 🔗 Flutter 端影響分析

### 需要調整的部分

#### 1. API Client 重新產生
**原因**: User model 新增了 `provider` 和 `provider_id` 欄位

**步驟**:
```bash
cd HoldYourBeer-Flutter
./scripts/generate-api-client.sh
```

這會：
- 從 Laravel 複製最新的 `openapi.yaml`
- 重新產生 Dart API client
- 執行 `build_runner` 產生必要的 serialization code

#### 2. 密碼設定畫面 UI 調整
**影響檔案**: `lib/features/profile/screens/profile_screen.dart`（或相關密碼設定畫面）

**需求**:
```dart
// 檢查使用者的 provider 類型
final user = ref.watch(currentUserProvider);
final isOAuthUser = ['google', 'apple', 'facebook'].contains(user?.provider);

// UI 邏輯
if (isOAuthUser) {
  // OAuth 使用者：只顯示「新密碼」和「確認密碼」
  // 不需要「目前密碼」欄位
} else {
  // Local/Legacy 使用者：顯示完整的三個欄位
  // 「目前密碼」、「新密碼」、「確認密碼」
}
```

**建議的 UI 提示**:
- OAuth 使用者：「設定密碼作為備用登入方式」
- Local 使用者：「修改密碼」

#### 3. API Response 結構變更（待確認）
**當前狀況**: `AuthController` 回傳的 user 物件**尚未包含** `provider` 欄位

**選項 A - 後端加入 provider 到 API response**:
```php
// AuthController.php - register/login/googleAuth 方法
return response()->json([
    'user' => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'email_verified_at' => $user->email_verified_at,
        'provider' => $user->provider,  // 新增
        'created_at' => $user->created_at,
        'updated_at' => $user->updated_at,
    ],
    // ...
]);
```

**選項 B - Flutter 端從 /api/v1/user 取得**:
Flutter 登入後額外呼叫 `/api/v1/user` 取得完整 user 資料（包含 provider）

**建議**: 採用選項 A，減少 API 呼叫次數

#### 4. 可能需要的 Provider 更新
```dart
// lib/core/providers/auth_provider.dart

@riverpod
class AuthState extends _$AuthState {
  @override
  AuthUser? build() => null;

  Future<void> login(String email, String password) async {
    final response = await authApi.login(...);

    // 新增：儲存 provider 資訊
    state = AuthUser(
      id: response.user.id,
      name: response.user.name,
      email: response.user.email,
      provider: response.user.provider, // 新增
      // ...
    );
  }
}
```

### 建議的實作順序
1. ✅ **後端**: 完成 provider 欄位實作（已完成）
2. ⏳ **後端**: 將 `provider` 加入 API response
3. ⏳ **Flutter**: 重新產生 API client
4. ⏳ **Flutter**: 更新 AuthUser model 與 providers
5. ⏳ **Flutter**: 調整密碼設定畫面 UI
6. ⏳ **測試**: 驗證 OAuth 和 Local 使用者的密碼設定流程

### 測試重點
- [ ] OAuth 使用者登入後，密碼設定畫面只顯示兩個欄位
- [ ] Local 使用者登入後，密碼設定畫面顯示三個欄位
- [ ] OAuth 使用者設定密碼後，可用 Email+Password 登入
- [ ] Legacy 使用者（provider=null）視為 Local 使用者

---

## 📌 待辦事項

### 後端
- [ ] 將 `provider` 欄位加入 `AuthController` 的所有 user response
- [ ] 重新產生 OpenAPI 規格
- [ ] 建立 Git commit

### Flutter
- [ ] 建立 Flutter session 文件（`HoldYourBeer-Flutter/docs/sessions/2025-12/11-oauth-password-ui-adjustment.md`）
- [ ] 重新產生 API client
- [ ] 更新 AuthUser model
- [ ] 調整密碼設定畫面 UI
- [ ] 撰寫測試案例
