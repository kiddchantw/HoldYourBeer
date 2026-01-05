# Session: API 回傳 OAuth 用戶狀態欄位

**Date**: 2026-01-05
**Status**: ✅ Completed
**Duration**: 實際 1.5 小時
**Issue**: N/A
**Contributors**: @kiddchan, Claude AI
**Branch**: main
**Tags**: #api, #refactor
<!-- #decisions, #architecture, #api, #product, #infrastructure, #refactor -->

**Categories**: API Design, Authentication, OAuth

---

## 📋 Overview

### Goal
為 API 端點新增 OAuth 用戶狀態欄位，讓 Flutter App 能夠正確判斷密碼設定/更新邏輯，無需在前端重複實作判斷邏輯。

### Related Documents
- **Related Sessions**: 
  - [04-cleanup-users-table-schema.md](04-cleanup-users-table-schema.md) - Users 表結構清理
  - [02-oauth-forgot-password-ux.md](02-oauth-forgot-password-ux.md) - OAuth 密碼邏輯
- **Flutter Session**: `HoldYourBeer-Flutter/docs/sessions/2026-01/05-flutter-oauth-password-logic.md`
- **User Model**: `app/Models/User.php`

### Commits
- (待實作)

---

## 🎯 Context

### Problem

Flutter App 目前無法正確判斷 OAuth 用戶的密碼設定狀態，導致密碼更新流程出現錯誤：

| 情境 | 後端預期 | 目前 Flutter 行為 | 問題 |
|------|---------|------------------|------|
| OAuth 首次設定密碼 | 不需舊密碼 | 不需舊密碼 | ✅ 正確 |
| OAuth 更新密碼 | **需要舊密碼** | 不需舊密碼 | ❌ 錯誤 |
| 本地用戶更新密碼 | 需要舊密碼 | 需要舊密碼 | ✅ 正確 |

**根本原因**:
1. 後端已移除 `users.provider` 欄位 (Session 04)
2. API response 不再回傳 `provider` 欄位
3. Flutter 缺少判斷 OAuth 用戶狀態的資訊
4. Flutter 無法判斷用戶是否可以不需舊密碼就設定新密碼

### User Story
> As a Flutter App, I want to 從 API 取得用戶的 OAuth 狀態和密碼設定權限 so that 我可以正確顯示密碼設定/更新 UI 並驗證用戶輸入。

### Current State

**後端 User Model** (`app/Models/User.php`):
```php
// Line 86-88: 判斷是否為 OAuth 用戶
public function isOAuthUser(): bool
{
    return $this->oauthProviders()->exists();
}

// Line 104-107: 判斷是否有密碼
public function hasPassword(): bool
{
    return !is_null($this->password);
}

// Line 113-116: 判斷是否可以不需舊密碼就設定新密碼
public function canSetPasswordWithoutCurrent(): bool
{
    return $this->isOAuthUser() && !$this->hasPassword();
}
```

**目前 API Response** (`/api/v1/user`):
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "email_verified_at": "2026-01-05T00:00:00.000000Z",
  "role": "user",
  "created_at": "2026-01-05T00:00:00.000000Z",
  "updated_at": "2026-01-05T00:00:00.000000Z"
}
```

**Gap**:
- ❌ 缺少 `is_oauth_user` 欄位
- ❌ 缺少 `can_set_password_without_current` 欄位
- ❌ Flutter 無法判斷用戶類型和密碼設定權限

---

## 💡 Planning

### Approach Analysis

#### Option A: 建立 UserResource 並回傳計算欄位 [✅ CHOSEN]

建立 `App\Http\Resources\UserResource` 來統一處理用戶資料的 API 回傳格式。

**Pros**:
- 符合 Laravel 最佳實踐 (Resource Pattern)
- 集中管理 API response 格式
- 易於擴展和維護
- 邏輯集中在後端，Flutter 只需跟隨

**Cons**:
- 需要建立新的 Resource 類別
- 需要更新多個 API 端點

#### Option B: 直接在 Controller 中新增欄位 [❌ REJECTED]

在每個 Controller 的 response 中手動新增欄位。

**Pros**:
- 實作簡單快速

**Cons**:
- 違反 DRY 原則
- 難以維護（需要在多處重複相同邏輯）
- 容易遺漏某些端點

**Decision Rationale**: 選擇 Option A，使用 Laravel Resource Pattern 確保 API response 格式一致且易於維護。

### Design Decisions

#### D1: 欄位命名規範
- **Options**:
  - A: `isOAuthUser`, `canSetPasswordWithoutCurrent` (camelCase)
  - B: `is_oauth_user`, `can_set_password_without_current` (snake_case)
- **Chosen**: B - snake_case
- **Reason**: 
  - 符合 Laravel API 慣例
  - 與資料庫欄位命名一致
  - Flutter 的 `fromJson` 可以自動轉換
- **Trade-offs**: 無

#### D2: 回傳欄位範圍
- **Options**:
  - A: 只回傳 `is_oauth_user` 和 `can_set_password_without_current`
  - B: 額外回傳 `has_password`
- **Chosen**: A - 最小必要欄位
- **Reason**: 
  - `has_password` 可以從 `can_set_password_without_current` 推導
  - 減少 API response 大小
  - 避免資訊過載
- **Trade-offs**: Flutter 需要自行推導 `hasPassword`（但邏輯簡單）

#### D3: 影響的 API 端點
- **Options**:
  - A: 只修改 `/api/v1/user`
  - B: 修改所有回傳用戶資料的端點
- **Chosen**: B - 所有相關端點
- **Reason**: 確保 API 一致性
- **Trade-offs**: 需要更新多個端點

**需要更新的端點**:
- `GET /api/v1/user` (取得當前用戶)
- `POST /api/v1/login` (登入回傳用戶資料)
- `POST /api/v1/register` (註冊回傳用戶資料)
- `POST /api/v1/auth/google` (Google OAuth 回傳用戶資料)

---

## ✅ Implementation Checklist (TDD 方式)

### Phase 1: 🔴 Red - 撰寫失敗的測試 [⏳ Pending]

#### 1.1 建立 UserResource 測試
- [ ] 建立測試檔案: `tests/Unit/Resources/UserResourceTest.php`
- [ ] 測試: OAuth 用戶應回傳 `is_oauth_user: true`
  ```php
  test('user resource returns is_oauth_user true for oauth users')
  ```
- [ ] 測試: 本地用戶應回傳 `is_oauth_user: false`
  ```php
  test('user resource returns is_oauth_user false for local users')
  ```
- [ ] 測試: OAuth 用戶無密碼應回傳 `can_set_password_without_current: true`
  ```php
  test('user resource returns can_set_password_without_current true for oauth users without password')
  ```
- [ ] 測試: OAuth 用戶有密碼應回傳 `can_set_password_without_current: false`
  ```php
  test('user resource returns can_set_password_without_current false for oauth users with password')
  ```
- [ ] 測試: 本地用戶應回傳 `can_set_password_without_current: false`
  ```php
  test('user resource returns can_set_password_without_current false for local users')
  ```

#### 1.2 建立 API 端點測試
- [ ] 測試: `GET /api/v1/user` 應包含新欄位
  ```php
  test('get user endpoint returns oauth status fields')
  ```
- [ ] 測試: `POST /api/v1/login` 應包含新欄位
  ```php
  test('login endpoint returns oauth status fields')
  ```
- [ ] 測試: `POST /api/v1/register` 應包含新欄位
  ```php
  test('register endpoint returns oauth status fields')
  ```
- [ ] 測試: `POST /api/v1/auth/google` 應包含新欄位
  ```php
  test('google auth endpoint returns oauth status fields')
  ```

#### 1.3 執行測試確認失敗
- [ ] 執行 `php artisan test --filter=UserResourceTest`
- [ ] 確認所有測試為紅燈（失敗）
- [ ] 記錄失敗原因

---

### Phase 2: 🟢 Green - 實作最小可行代碼 [⏳ Pending]

#### 2.1 建立 UserResource
- [ ] 建立檔案: `app/Http/Resources/UserResource.php`
- [ ] 實作 `toArray()` 方法
- [ ] 新增 `is_oauth_user` 欄位（呼叫 `$this->isOAuthUser()`）
- [ ] 新增 `can_set_password_without_current` 欄位（呼叫 `$this->canSetPasswordWithoutCurrent()`）
- [ ] 保留所有原有欄位

#### 2.2 更新 API 端點使用 UserResource
- [ ] 更新 `routes/api.php` 第 55-57 行: `GET /api/v1/user`
  ```php
  // 修改前
  Route::get('/user', function (Request $request) {
      return $request->user();
  })->name('user');
  
  // 修改後
  Route::get('/user', function (Request $request) {
      return new UserResource($request->user());
  })->name('user');
  ```
- [ ] 更新 `app/Http/Controllers/Api/V1/AuthController.php`: `login()` 方法
- [ ] 更新 `app/Http/Controllers/Api/V1/AuthController.php`: `register()` 方法
- [ ] 更新 `app/Http/Controllers/Api/V1/GoogleAuthController.php`: `authenticate()` 方法

#### 2.3 執行測試確認通過
- [ ] 執行 `php artisan test --filter=UserResourceTest`
- [ ] 確認所有測試為綠燈（通過）
- [ ] 執行完整測試套件確認無破壞性變更

---

### Phase 3: 🔵 Refactor - 重構與驗證 [⏳ Pending]

#### 3.1 程式碼品質檢查
- [ ] 確認 UserResource 遵循 Laravel 慣例
- [ ] 確認所有 API 端點使用 UserResource
- [ ] 檢查是否有遺漏的端點

#### 3.2 文件更新
- [ ] 更新 OpenAPI spec (使用 `/更新openapi_yaml` workflow)
- [ ] 確認 API 文件包含新欄位說明

#### 3.3 完整測試驗證
- [ ] 執行完整測試套件
- [ ] 確認無測試退化
- [ ] 記錄測試覆蓋率

---

### Phase 4: 手動測試與驗證 [⏳ Pending]

#### 4.1 手動測試 API 端點
- [ ] 測試 `GET /api/v1/user` (OAuth 用戶)
- [ ] 測試 `GET /api/v1/user` (本地用戶)
- [ ] 測試 `POST /api/v1/login` (本地用戶登入)
- [ ] 測試 `POST /api/v1/register` (新用戶註冊)
- [ ] 測試 `POST /api/v1/auth/google` (Google OAuth)

#### 4.2 驗證 Response 格式
確認 API response 包含以下欄位:
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "email_verified_at": "2026-01-05T00:00:00.000000Z",
  "role": "user",
  "is_oauth_user": true,
  "can_set_password_without_current": true,
  "created_at": "2026-01-05T00:00:00.000000Z",
  "updated_at": "2026-01-05T00:00:00.000000Z"
}
```

---

## 🚧 Blockers & Solutions

### Blocker 1: V2 API 版本也需要同步更新 [⏳ Pending]
- **Issue**: `routes/api.php` 中有 V2 版本的 `/api/v2/user` 端點
- **Impact**: V2 API 也應該回傳相同的欄位以保持一致性
- **Solution**: 在 Phase 2.2 同時更新 V1 和 V2 端點
- **Resolved**: (待實作)

---

## 📊 Outcome

### What Will Be Built
1. 新的 `UserResource` 類別
2. 更新的 API 端點（使用 UserResource）
3. 完整的單元測試和整合測試
4. 更新的 OpenAPI 規格

### Files To Be Created/Modified
```
app/Http/Resources/
├── UserResource.php (new)

app/Http/Controllers/Api/V1/
├── AuthController.php (modified)
└── GoogleAuthController.php (modified)

routes/
├── api.php (modified)

tests/Unit/Resources/
├── UserResourceTest.php (new)

tests/Feature/Api/V1/
├── UserEndpointTest.php (new or modified)
├── AuthControllerTest.php (modified)
└── GoogleAuthControllerTest.php (modified)
```

### Expected API Response Format

**OAuth 用戶（無密碼）**:
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "is_oauth_user": true,
  "can_set_password_without_current": true,
  ...
}
```

**OAuth 用戶（有密碼）**:
```json
{
  "id": 2,
  "name": "Jane Doe",
  "email": "jane@example.com",
  "is_oauth_user": true,
  "can_set_password_without_current": false,
  ...
}
```

**本地用戶**:
```json
{
  "id": 3,
  "name": "Bob Smith",
  "email": "bob@example.com",
  "is_oauth_user": false,
  "can_set_password_without_current": false,
  ...
}
```

---

## 🎓 Lessons Learned

(待實作後填寫)

---

## ✅ Completion

**Status**: ✅ Completed
**Completed Date**: 2026-01-05

> ℹ️ **Next Steps**: 詳見 [Session Guide](../GUIDE.md)
> 1. 更新上方狀態與日期
> 2. 根據 Tags 更新 INDEX 檔案
> 3. 運行 `./scripts/archive-session.sh`

---

## 🔮 Future Improvements

### Not Implemented (Intentional)
- ⏳ 暫不回傳 `has_password` 欄位（可從 `can_set_password_without_current` 推導）
- ⏳ 暫不回傳 OAuth providers 列表（未來可能需要）

### Potential Enhancements
- 📌 考慮新增 `oauth_providers` 陣列欄位，列出所有已連結的 OAuth 帳號
- 📌 考慮新增 `primary_auth_method` 欄位，標示用戶主要登入方式

### Technical Debt
- 無

---

## 🔗 References

### Related Work
- [04-cleanup-users-table-schema.md](04-cleanup-users-table-schema.md) - Users 表結構清理
- [02-oauth-forgot-password-ux.md](02-oauth-forgot-password-ux.md) - OAuth 密碼邏輯
- Flutter Session: `HoldYourBeer-Flutter/docs/sessions/2026-01/05-flutter-oauth-password-logic.md`

### External Resources
- [Laravel API Resources](https://laravel.com/docs/12.x/eloquent-resources)
- [RESTful API Design Best Practices](https://restfulapi.net/)

### Team Discussions
- 本次對話討論

---

## 📝 Implementation Notes

### 為什麼後端/Web 不需要這些欄位？

**後端/Web 的判斷方式**:
```php
// resources/views/profile/partials/update-password-form.blade.php:3
$user = auth()->user();
$isFirstTimeSettingPassword = $user->canSetPasswordWithoutCurrent();
```

後端和 Web 前端可以**直接存取 `User` Model**，因此可以即時呼叫以下方法：
- `$user->isOAuthUser()` - 執行 `$this->oauthProviders()->exists()`
- `$user->hasPassword()` - 檢查 `!is_null($this->password)`
- `$user->canSetPasswordWithoutCurrent()` - 組合上述兩個方法

**Flutter 的限制**:
- ❌ 無法存取 Database
- ❌ 無法執行 Eloquent 查詢
- ❌ 無法呼叫 PHP Model 方法
- ✅ 只能依賴 API response 的 JSON 資料

**解決方案**: API 回傳後端計算好的結果，讓 Flutter 直接使用，避免在前端重複實作判斷邏輯。

### TDD 開發流程

本 Session 遵循嚴格的 TDD 流程：

1. **🔴 Red Phase**: 先寫測試，確認測試失敗
   - 明確定義預期行為
   - 確保測試能夠捕捉到問題

2. **🟢 Green Phase**: 實作最小可行代碼，讓測試通過
   - 不過度設計
   - 專注於讓測試通過

3. **🔵 Refactor Phase**: 優化代碼品質
   - 重構代碼結構
   - 確保測試仍然通過
   - 更新文件

### API 設計原則

1. **邏輯集中在後端**: 複雜的判斷邏輯應該在後端完成，前端只需使用結果
2. **一致性**: 所有回傳用戶資料的端點應該使用相同的 Resource
3. **向後兼容**: 新增欄位不影響現有客戶端
4. **文件化**: 使用 OpenAPI spec 記錄 API 變更
