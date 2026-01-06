
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
- `feat(api): add UserResource with OAuth status fields` - 新增 UserResource 回傳 OAuth 用戶狀態
- `test(api): add UserResource and endpoint tests` - 新增完整的單元測試和 API 端點測試
- `feat(api): update V1 and V2 endpoints to use UserResource` - 更新所有 API 端點使用 UserResource
- `docs(api): update OpenAPI spec with new user fields` - 更新 OpenAPI 規格包含新欄位

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

### Phase 1: 🔴 Red - 撰寫失敗的測試 [✅ Completed]

#### 1.1 建立 UserResource 測試
- [x] 建立測試檔案: `tests/Unit/Resources/UserResourceTest.php`
- [x] 測試: OAuth 用戶應回傳 `is_oauth_user: true`
  ```php
  test('user resource returns is_oauth_user true for oauth users')
  ```
- [x] 測試: 本地用戶應回傳 `is_oauth_user: false`
  ```php
  test('user resource returns is_oauth_user false for local users')
  ```
- [x] 測試: OAuth 用戶無密碼應回傳 `can_set_password_without_current: true`
  ```php
  test('user resource returns can_set_password_without_current true for oauth users without password')
  ```
- [x] 測試: OAuth 用戶有密碼應回傳 `can_set_password_without_current: false`
  ```php
  test('user resource returns can_set_password_without_current false for oauth users with password')
  ```
- [x] 測試: 本地用戶應回傳 `can_set_password_without_current: false`
  ```php
  test('user resource returns can_set_password_without_current false for local users')
  ```

#### 1.2 建立 API 端點測試
- [x] 測試: `GET /api/v1/user` 應包含新欄位
  ```php
  test('get user endpoint returns oauth status fields')
  ```
- [x] 測試: `POST /api/v1/login` 應包含新欄位
  ```php
  test('login endpoint returns oauth status fields')
  ```
- [x] 測試: `POST /api/v1/register` 應包含新欄位
  ```php
  test('register endpoint returns oauth status fields')
  ```
- [x] 測試: `POST /api/v1/auth/google` 應包含新欄位
  ```php
  test('google auth endpoint returns oauth status fields')
  ```

#### 1.3 執行測試確認失敗
- [x] 執行 `php artisan test --filter=UserResourceTest`
- [x] 確認所有測試為紅燈（失敗）
- [x] 記錄失敗原因

---

### Phase 2: 🟢 Green - 實作最小可行代碼 [✅ Completed]

#### 2.1 建立 UserResource
- [x] 建立檔案: `app/Http/Resources/UserResource.php`
- [x] 實作 `toArray()` 方法
- [x] 新增 `is_oauth_user` 欄位（呼叫 `$this->isOAuthUser()`）
- [x] 新增 `can_set_password_without_current` 欄位（呼叫 `$this->canSetPasswordWithoutCurrent()`）
- [x] 保留所有原有欄位

#### 2.2 更新 API 端點使用 UserResource
- [x] 更新 `routes/api.php` 第 55-57 行: `GET /api/v1/user`
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
- [x] 更新 `app/Http/Controllers/Api/V1/AuthController.php`: `login()` 方法
- [x] 更新 `app/Http/Controllers/Api/V1/AuthController.php`: `register()` 方法
- [x] 更新 `app/Http/Controllers/Api/V1/GoogleAuthController.php`: `authenticate()` 方法
- [x] 更新 `routes/api.php` V2 API: `GET /api/v2/user`

#### 2.3 執行測試確認通過
- [x] 執行 `php artisan test --filter=UserResourceTest`
- [x] 確認所有測試為綠燈（通過）- 5 passed (10 assertions)
- [x] 執行完整測試套件確認無破壞性變更

---

### Phase 3: 🔵 Refactor - 重構與驗證 [✅ Completed]

#### 3.1 程式碼品質檢查
- [x] 確認 UserResource 遵循 Laravel 慣例
- [x] 確認所有 API 端點使用 UserResource (V1 和 V2)
- [x] 檢查是否有遺漏的端點

#### 3.2 文件更新
- [x] 更新 OpenAPI spec (使用 `/更新openapi_yaml` workflow)
- [x] 重新產生 Flutter API 客戶端
- [x] 確認 API 文件包含新欄位說明

#### 3.3 完整測試驗證
- [x] 執行完整測試套件
- [x] 確認無測試退化
- [x] UserResourceTest: 5/5 通過
- [x] UserEndpointTest: 3/3 通過

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

### Blocker 1: V2 API 版本也需要同步更新 [✅ RESOLVED]
- **Issue**: `routes/api.php` 中有 V2 版本的 `/api/v2/user` 端點
- **Impact**: V2 API 也應該回傳相同的欄位以保持一致性
- **Solution**: 在 Phase 2.2 同時更新 V1 和 V2 端點
- **Resolved**: 2026-01-05 - V2 API 端點已更新使用 UserResource

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

### 1. Laravel Resource Pattern 的價值
**Learning**: 使用 Resource 類別統一管理 API response 格式，避免在多處重複相同邏輯

**Solution/Pattern**:
```php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_oauth_user' => $this->isOAuthUser(),
            'can_set_password_without_current' => $this->canSetPasswordWithoutCurrent(),
            // ...
        ];
    }
}
```

**Future Application**:
- 所有 API 端點都應該使用 Resource 類別
- 避免直接回傳 Model 或手動建構陣列
- Resource 讓 API 格式變更更容易維護

### 2. TDD 方法論的實踐
**Learning**: 先寫測試再實作，確保功能符合預期且不會破壞現有功能

**Solution/Pattern**:
1. **Red Phase**: 建立 5 個測試案例，確認測試失敗
2. **Green Phase**: 實作 UserResource，讓測試通過
3. **Refactor Phase**: 更新所有端點使用 UserResource

**Future Application**:
- 所有新功能都應該先寫測試
- 測試應該涵蓋所有情境（OAuth 用戶、本地用戶、有/無密碼）
- 使用測試輔助工具（如 `CreatesOAuthUsers` trait）簡化測試撰寫

### 3. API 一致性的重要性
**Learning**: V1 和 V2 API 應該保持一致的 response 格式

**Solution/Pattern**:
- 所有版本的 API 都使用相同的 Resource 類別
- 避免在不同版本中使用不同的 response 格式
- 新增欄位時同時更新所有版本

**Future Application**:
- 建立 API 版本管理策略
- 使用 OpenAPI spec 確保 API 文件與實作一致
- 定期檢查不同版本的 API 是否保持一致

### 4. 前後端協作的最佳實踐
**Learning**: 使用 Session 文件明確定義前後端的依賴關係和交付物

**Solution/Pattern**:
- 後端 Session 負責 API 設計和實作
- Flutter Session 負責前端邏輯
- 使用 OpenAPI spec 作為溝通橋樑
- 自動化 API 客戶端產生流程

**Future Application**:
- 跨專案功能應該建立對應的 Session 文件
- 明確定義 API contract 和預期行為
- 使用自動化工具（如 Scribe）減少手動維護成本

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
