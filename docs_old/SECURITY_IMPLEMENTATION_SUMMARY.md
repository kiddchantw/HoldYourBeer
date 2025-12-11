# 安全改進實作總結

**實作日期**：2025-11-12
**實作內容**：三個高優先級安全改進措施

---

## 目錄

1. [實作 1：Policy 授權層](#實作-1policy-授權層)
2. [實作 2：統一 API 錯誤處理](#實作-2統一-api-錯誤處理)
3. [實作 3：CSRF 保護文檔](#實作-3csrf-保護文檔)
4. [測試建議](#測試建議)
5. [部署檢查清單](#部署檢查清單)

---

## 實作 1：Policy 授權層

### 問題背景
專案原本只有認證檢查（`auth:sanctum`），缺少細粒度的授權檢查。例如：任何登入用戶都可以查看或修改任何啤酒的品嚐記錄。

### 實作內容

#### 1.1 BeerPolicy (`app/Policies/BeerPolicy.php`)

```php
class BeerPolicy
{
    // 查看啤酒列表（所有認證用戶）
    public function viewAny(User $user): bool;

    // 查看單一啤酒（所有認證用戶）
    public function view(User $user, Beer $beer): bool;

    // 更新啤酒計數（只有擁有者）
    public function update(User $user, Beer $beer): bool;

    // 查看品嚐記錄（只有擁有者）
    public function viewTastingLogs(User $user, Beer $beer): bool;
}
```

**授權邏輯**：
- 檢查 `user_beer_counts` 表中是否存在 `user_id` 和 `beer_id` 的對應記錄
- 只有追蹤該啤酒的用戶才能修改計數或查看記錄

#### 1.2 TastingLogPolicy (`app/Policies/TastingLogPolicy.php`)

```php
class TastingLogPolicy
{
    // 建立品嚐記錄（只有追蹤該啤酒的用戶）
    public function create(User $user, Beer $beer): bool;
}
```

#### 1.3 更新 BeerController

**修改前**：
```php
public function countAction(CountActionRequest $request, int $id)
{
    // 沒有授權檢查，任何認證用戶都可以操作
    $userBeerCount = $this->tastingService->incrementCount(Auth::id(), $id, $note);
}
```

**修改後**：
```php
public function countAction(CountActionRequest $request, int $id)
{
    // 授權檢查：只有追蹤該啤酒的用戶可以更新計數
    $beer = Beer::findOrFail($id);
    $this->authorize('update', $beer);

    $userBeerCount = $this->tastingService->incrementCount(Auth::id(), $id, $note);
}
```

### 安全效益

✅ **防止水平權限提升**：用戶無法操作其他用戶的啤酒記錄
✅ **細粒度授權**：不同操作有不同的權限檢查
✅ **符合最小權限原則**：用戶只能存取自己的資源

### 測試檔案
- `tests/Feature/Api/V1/BeerAuthorizationTest.php`

---

## 實作 2：統一 API 錯誤處理

### 問題背景
專案中多處直接使用 `$e->getMessage()` 返回錯誤訊息，可能洩漏：
- 資料庫結構資訊
- 檔案路徑
- 內部類別名稱
- SQL 查詢細節

### 實作內容

#### 2.1 自訂 Exception：BusinessLogicException

**檔案**：`app/Exceptions/BusinessLogicException.php`

```php
class BusinessLogicException extends Exception
{
    protected string $errorCode;
    protected int $statusCode;

    public function __construct(
        string $message,
        string $errorCode = 'BIZ_000',
        int $statusCode = 400
    ) {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
    }

    public function render()
    {
        return response()->json([
            'error_code' => $this->errorCode,
            'message' => $this->getMessage(),
        ], $this->statusCode);
    }
}
```

**使用範例**（TastingService）：
```php
if ($userBeerCount->count <= 0) {
    throw new BusinessLogicException(
        'Cannot decrement count below zero.',
        'BIZ_001',
        400
    );
}
```

#### 2.2 統一 Exception Handler

**檔案**：`app/Exceptions/Handler.php`

**核心功能**：
1. **只處理 API 請求**（`$request->is('api/*')`）
2. **區分不同 Exception 類型**：
   - `ValidationException` → 422, error_code: `VAL_001`
   - `AuthenticationException` → 401, error_code: `AUTH_001`
   - `AuthorizationException` → 403, error_code: `AUTH_002`
   - `ModelNotFoundException` → 404, error_code: `RES_001`
   - `BusinessLogicException` → 自訂狀態碼和錯誤碼
3. **生產環境隱藏敏感資訊**：
   ```php
   if (config('app.debug')) {
       // 開發環境：顯示詳細錯誤
   } else {
       // 生產環境：通用錯誤訊息
       return response()->json([
           'error_code' => 'SYS_001',
           'message' => 'An internal server error occurred.',
       ], 500);
   }
   ```

#### 2.3 更新 Controller

**修改前**：
```php
try {
    $userBeerCount = $this->tastingService->decrementCount(Auth::id(), $id, $note);
} catch (\Exception $e) {
    return response()->json([
        'error_code' => 'BIZ_001',
        'message' => $e->getMessage() // 可能洩漏敏感資訊
    ], 400);
}
```

**修改後**：
```php
// 讓 Exception 向上拋出，由全域 Handler 統一處理
$userBeerCount = $this->tastingService->decrementCount(Auth::id(), $id, $note);
```

### 錯誤碼對照表

| 錯誤碼 | HTTP 狀態 | 說明 |
|--------|-----------|------|
| `VAL_001` | 422 | 驗證錯誤 |
| `AUTH_001` | 401 | 未認證 |
| `AUTH_002` | 403 | 未授權（無權限） |
| `RES_001` | 404 | 資源不存在 |
| `BIZ_001` | 400 | 業務邏輯錯誤（如計數不能為負） |
| `SYS_001` | 500 | 系統內部錯誤 |

### 安全效益

✅ **防止資訊洩漏**：生產環境隱藏內部錯誤細節
✅ **統一錯誤格式**：所有 API 錯誤回應格式一致
✅ **錯誤碼追蹤**：每種錯誤有唯一識別碼，方便追蹤和監控
✅ **開發友善**：開發環境顯示詳細錯誤，協助除錯

### 測試檔案
- `tests/Feature/Api/V1/ExceptionHandlingTest.php`

---

## 實作 3：CSRF 保護文檔

### 實作內容

**檔案**：`docs/CSRF-Protection.md`

### 文檔結構

1. **Sanctum CSRF 保護機制**
   - Cookie-Based Authentication（SPA）
   - Token-Based Authentication（Mobile App）

2. **SPA 應用設定**
   - 前端 Axios 配置
   - CSRF Cookie 獲取流程
   - `withCredentials: true` 設定

3. **Mobile App 設定**
   - Flutter Bearer Token 認證
   - Secure Storage 使用
   - Authorization Header 配置

4. **Laravel 後端配置**
   - `config/sanctum.php` 設定
   - `config/cors.php` CORS 設定
   - 環境變數配置

5. **CSRF 保護驗證方法**
   - 測試 CSRF 保護
   - cURL 測試範例

6. **常見問題排解**
   - 419 CSRF Token Mismatch
   - Mobile App 認證問題
   - CORS 錯誤

7. **安全最佳實踐**
   - 生產環境 HTTPS
   - Token 過期設定
   - Secure Storage 使用

### 安全效益

✅ **明確的安全指引**：開發者知道如何正確設定 CSRF 保護
✅ **區分 SPA 和 Mobile**：不同客戶端有不同的最佳實踐
✅ **故障排除指南**：常見問題的解決方案
✅ **最佳實踐清單**：避免常見的安全錯誤

---

## 測試建議

### 1. 執行所有測試

在 Laradock workspace 容器中執行：

```bash
# 進入容器
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace bash

# 執行所有測試
php artisan test

# 執行特定測試套件
php artisan test --filter=BeerAuthorizationTest
php artisan test --filter=ExceptionHandlingTest

# 產生覆蓋率報告
php artisan test --coverage
```

### 2. 測試授權機制

**測試案例**：
```bash
# 測試用戶無法操作其他用戶的啤酒
php artisan test --filter=user_cannot_update_count_for_beer_they_are_not_tracking

# 測試用戶可以操作自己的啤酒
php artisan test --filter=user_can_update_count_for_beer_they_are_tracking

# 測試未認證用戶無法存取
php artisan test --filter=unauthenticated_user_cannot_access_beer_endpoints
```

### 3. 測試錯誤處理

**測試案例**：
```bash
# 測試驗證錯誤格式
php artisan test --filter=validation_exception_returns_consistent_format

# 測試不洩漏敏感資訊
php artisan test --filter=error_response_does_not_leak_sensitive_information

# 測試業務邏輯錯誤
php artisan test --filter=business_logic_exception_returns_custom_error_code
```

### 4. 手動測試

使用 Postman 或 cURL 測試：

```bash
# 1. 未認證請求 (應返回 401)
curl -X GET http://holdyourbeer.test/api/v1/beers

# 2. 嘗試操作其他用戶的啤酒 (應返回 403)
curl -X POST http://holdyourbeer.test/api/v1/beers/1/count_action \
  -H "Authorization: Bearer {valid-token}" \
  -H "Content-Type: application/json" \
  -d '{"action": "increment"}'

# 3. 業務邏輯錯誤 (應返回 400 with BIZ_001)
curl -X POST http://holdyourbeer.test/api/v1/beers/1/count_action \
  -H "Authorization: Bearer {valid-token}" \
  -H "Content-Type: application/json" \
  -d '{"action": "decrement"}'  # 當 count = 0 時
```

---

## 部署檢查清單

### 部署前檢查

- [ ] **執行完整測試套件**
  ```bash
  php artisan test
  ```

- [ ] **檢查測試覆蓋率** (目標 > 80%)
  ```bash
  php artisan test --coverage --min=80
  ```

- [ ] **執行靜態分析** (如果有設定 PHPStan/Larastan)
  ```bash
  ./vendor/bin/phpstan analyse
  ```

- [ ] **檢查程式碼格式** (如果有設定 Laravel Pint)
  ```bash
  ./vendor/bin/pint --test
  ```

### 環境變數檢查

確認 `.env` 包含以下設定：

```bash
# 生產環境必須設定
APP_ENV=production
APP_DEBUG=false
APP_URL=https://holdyourbeer.com

# Sanctum 設定
SANCTUM_STATEFUL_DOMAINS=app.holdyourbeer.com
SESSION_SECURE_COOKIE=true  # HTTPS 必須為 true

# Session 設定
SESSION_DRIVER=redis  # 或其他持久化 driver
SESSION_DOMAIN=.holdyourbeer.com
```

### 安全設定檢查

- [ ] **HTTPS 啟用**（生產環境必須）
- [ ] **CSRF 保護啟用**（不要在 `VerifyCsrfToken.php` 中排除 API 路由）
- [ ] **正確的 CORS 設定**（不要使用 `*` 在生產環境）
- [ ] **Rate Limiting 啟用**（API 防止 DDoS）
- [ ] **Token 過期時間設定**（Sanctum expiration）

### 監控和日誌

- [ ] **啟用錯誤日誌監控**（Sentry, Bugsnag 等）
- [ ] **設定授權失敗通知**（異常的 403 錯誤）
- [ ] **監控 API 錯誤率**（特別是 500 錯誤）

---

## 未來改進建議

### 1. 實作 Rate Limiting

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // API routes with 60 requests per minute limit
});
```

### 2. 實作 API 版本廢棄提醒

```php
// Middleware: ApiDeprecation (已存在)
// 在舊版 API 回應中加入 X-API-Deprecated header
```

### 3. 實作審計日誌

記錄所有修改操作：
- 誰修改了什麼資源
- 何時修改
- 從哪個 IP 修改

### 4. 實作兩階段驗證 (2FA)

為敏感操作（如刪除帳號）增加額外驗證層。

### 5. 實作 API Key 管理

為第三方整合提供 API Key 認證機制。

---

## 相關檔案清單

### 新增檔案
- `app/Policies/BeerPolicy.php`
- `app/Policies/TastingLogPolicy.php`
- `app/Exceptions/Handler.php`
- `app/Exceptions/BusinessLogicException.php`
- `docs/CSRF-Protection.md`
- `docs/SECURITY_IMPLEMENTATION_SUMMARY.md`
- `tests/Feature/Api/V1/BeerAuthorizationTest.php`
- `tests/Feature/Api/V1/ExceptionHandlingTest.php`

### 修改檔案
- `app/Http/Controllers/Api/V1/BeerController.php`
- `app/Services/TastingService.php`
- `bootstrap/app.php`

---

## 聯絡資訊

如有問題或建議，請參考：
- Laravel Policy 文檔：https://laravel.com/docs/11.x/authorization
- Laravel Sanctum 文檔：https://laravel.com/docs/11.x/sanctum
- OWASP API 安全指南：https://owasp.org/www-project-api-security/

---

**實作完成日期**：2025-11-12
**實作者**：Claude Code Agent
**審核狀態**：待測試和部署
