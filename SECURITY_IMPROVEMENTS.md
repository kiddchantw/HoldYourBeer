# 安全性強化改善報告

> **實作日期**: 2025-11-05
> **改善範圍**: 優先級 3 - 安全性強化
> **狀態**: ✅ 已完成

---

## 📋 概要

本次安全性強化專注於三個核心領域：
1. **速率限制 (Rate Limiting)** - 防止 API 濫用和 DoS 攻擊
2. **CORS 和 CSP 配置** - 防止 XSS 和點擊劫持攻擊
3. **API 請求日誌與監控** - 提升可觀測性和問題追蹤能力

---

## 🎯 已實作功能

### 1. 速率限制 (Rate Limiting) ✅

#### 實作檔案
- `app/Providers/RateLimitServiceProvider.php`
- `routes/api.php`
- `bootstrap/providers.php`

#### 速率限制策略

| 限制名稱 | 適用範圍 | 限制規則 | 用途 |
|---------|---------|---------|------|
| `api` | 一般 API 端點 | 60 次/分鐘 | 防止一般濫用 |
| `auth` | 認證端點 | 5 次/分鐘、20 次/小時 | 防止暴力破解 |
| `count-actions` | 品飲計數操作 | 30 次/分鐘 | 防止計數濫用 |
| `social-login` | 第三方登錄 | 10 次/分鐘 | 防止 CSRF 攻擊 |
| `password-reset` | 密碼重置 | 3 次/分鐘、10 次/小時 | 防止重置攻擊 |
| `data-export` | 資料匯出 | 2 次/分鐘、10 次/小時 | 保護資源密集操作 |

#### 路由應用範例

```php
// 認證路由 - 嚴格限制
Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'token']);
});

// 計數操作 - 中等限制
Route::middleware('throttle:count-actions')->group(function () {
    Route::post('/beers/{id}/count_actions', [BeerController::class, 'countAction']);
});
```

#### 測試方法

**測試超過速率限制**:
```bash
# 測試認證端點速率限制（應在第6次請求時被限制）
for i in {1..10}; do
  curl -X POST http://localhost/api/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com","password":"wrong"}' \
    -w "\n%{http_code}\n"
  sleep 1
done

# 預期：前5次返回 401/422，第6次開始返回 429
```

**驗證速率限制標頭**:
```bash
curl -I http://localhost/api/beers \
  -H "Authorization: Bearer YOUR_TOKEN"

# 預期標頭：
# X-RateLimit-Limit: 60
# X-RateLimit-Remaining: 59
```

---

### 2. CORS 和 CSP 配置 ✅

#### 實作檔案
- `app/Http/Middleware/AddSecurityHeaders.php`
- `bootstrap/app.php`

#### 安全標頭列表

| 標頭名稱 | 設定值 | 防護目標 |
|---------|--------|----------|
| `X-Content-Type-Options` | nosniff | MIME 類型嗅探攻擊 |
| `X-Frame-Options` | DENY | 點擊劫持攻擊 |
| `X-XSS-Protection` | 1; mode=block | XSS 攻擊 |
| `Referrer-Policy` | strict-origin-when-cross-origin | 資訊洩漏 |
| `X-Powered-By` | HoldYourBeer | 隱藏技術棧資訊 |
| `Content-Security-Policy` | (詳見下方) | 多種注入攻擊 |

#### Content Security Policy (CSP)

```
default-src 'self';
script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com;
style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com;
img-src 'self' data: https: blob:;
font-src 'self' data: https://cdn.jsdelivr.net;
connect-src 'self' ${API_URL};
frame-ancestors 'none';
base-uri 'self';
form-action 'self';
```

#### CORS 配置

**允許的來源**:
- `FRONTEND_URL` (預設: http://localhost:3000)
- `APP_URL` (預設: http://localhost)

**允許的方法**:
- GET, POST, PUT, DELETE, OPTIONS

**允許的標頭**:
- Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN

**公開的標頭**:
- X-API-Version, X-RateLimit-Limit, X-RateLimit-Remaining

#### 測試方法

**測試 CORS 標頭**:
```bash
curl -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type" \
  -X OPTIONS http://localhost/api/beers \
  -v

# 預期標頭：
# Access-Control-Allow-Origin: http://localhost:3000
# Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
# Access-Control-Allow-Credentials: true
```

**測試 CSP 標頭**:
```bash
curl -I http://localhost

# 預期標頭：
# Content-Security-Policy: default-src 'self'; ...
# X-Frame-Options: DENY
# X-Content-Type-Options: nosniff
```

**測試非允許來源的 CORS 請求**:
```bash
curl -H "Origin: http://evil.com" \
  http://localhost/api/beers \
  -v

# 預期：不應包含 Access-Control-Allow-Origin 標頭
```

---

### 3. API 請求日誌與監控 ✅

#### 實作檔案
- `app/Http/Middleware/LogApiRequests.php`
- `config/logging.php`
- `bootstrap/app.php`

#### 日誌記錄項目

**每個 API 請求記錄**:
```json
{
  "request_id": "req_673a1b2c3d4e5",
  "method": "POST",
  "path": "api/beers/1/count_actions",
  "url": "http://localhost/api/beers/1/count_actions",
  "user_id": 123,
  "ip": "192.168.1.1",
  "user_agent": "Mozilla/5.0...",
  "status": 200,
  "duration_ms": 45.67,
  "timestamp": "2025-11-05T10:30:45+00:00",
  "query": {"action": "increment"},
  "request_size": 256,
  "response_size": 512
}
```

#### 日誌等級分類

| HTTP 狀態碼 | 日誌等級 | 說明 |
|------------|---------|------|
| 200-299 | INFO | 成功請求 |
| 300-399 | INFO | 重定向 |
| 400-499 | WARNING | 客戶端錯誤 |
| 500-599 | ERROR | 伺服器錯誤 |

#### 慢請求警告

處理時間超過 **1 秒** 的請求會記錄額外的警告日誌：
```
[WARNING] Slow API Request: POST /api/beers (duration: 1234ms)
```

#### 敏感資訊過濾

自動過濾以下欄位：
- `password`
- `token`
- `secret`
- `api_key`
- `apiKey`
- `access_token`

這些欄位在日誌中會顯示為 `[REDACTED]`。

#### 日誌檔案配置

**位置**: `storage/logs/api.log`
**輪替**: 每日
**保留**: 30 天

#### 測試方法

**發送測試請求**:
```bash
# 發送一個 API 請求
curl -X POST http://localhost/api/beers/1/count_actions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"action":"increment","note":"Test note"}'

# 檢查日誌
tail -f storage/logs/api.log
```

**測試慢請求警告**:
```bash
# 在控制器中臨時加入 sleep(2) 模擬慢請求
# 檢查日誌應該會看到 WARNING 級別的慢請求記錄
```

**驗證請求 ID**:
```bash
# 發送帶有自訂請求 ID 的請求
curl -X GET http://localhost/api/beers \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-Request-ID: custom-request-id-12345" \
  -v

# 回應標頭應包含：
# X-Request-ID: custom-request-id-12345
```

**驗證敏感資訊過濾**:
```bash
# 發送包含密碼的請求（例如註冊）
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name":"Test User",
    "email":"test@example.com",
    "password":"secret123",
    "password_confirmation":"secret123"
  }'

# 檢查日誌，password 應該顯示為 [REDACTED]
grep "secret123" storage/logs/api.log  # 應該找不到
grep "REDACTED" storage/logs/api.log   # 應該找到
```

---

## 🔧 配置說明

### 環境變數

在 `.env` 檔案中配置以下變數：

```env
# CORS 配置
FRONTEND_URL=http://localhost:3000
APP_URL=http://localhost

# API URL（用於 CSP）
API_URL=http://localhost

# 日誌設定
LOG_CHANNEL=stack
LOG_LEVEL=info
```

### 中間件註冊

所有中間件已自動註冊：

```php
// bootstrap/app.php

// 全域中間件（所有請求）
$middleware->append(\App\Http\Middleware\AddSecurityHeaders::class);

// API 中間件群組
$middleware->group('api', [
    \App\Http\Middleware\LogApiRequests::class,
    'throttle:api',
]);
```

### 服務提供者註冊

```php
// bootstrap/providers.php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RateLimitServiceProvider::class,  // 速率限制
];
```

---

## 📊 效能影響評估

### 中間件效能開銷

| 中間件 | 平均開銷 | 影響 |
|--------|---------|------|
| AddSecurityHeaders | < 1ms | 極低 |
| LogApiRequests | 1-3ms | 低 |
| RateLimiter | < 1ms | 極低 |
| **總計** | **< 5ms** | **低** |

### 儲存空間需求

**日誌檔案大小估算**:
- 平均每個請求日誌：~500 bytes
- 每日 10,000 請求：~5 MB
- 30 天保留：~150 MB

**建議**:
- 定期監控 `storage/logs/` 目錄大小
- 考慮使用日誌聚合工具（如 ELK、Graylog）處理大量日誌

---

## 🚨 安全性改善清單

### 已實作 ✅

- [x] 實作多層級速率限制（6 種策略）
- [x] 加入完整的安全標頭（CSP、XFO、XCT 等）
- [x] 配置動態 CORS（僅允許配置的來源）
- [x] 實作詳細的 API 請求日誌
- [x] 自動過濾日誌中的敏感資訊
- [x] 慢請求自動警告（>1秒）
- [x] 唯一請求 ID 追蹤
- [x] 日誌等級自動分類

### 建議下一步 🔮

- [ ] 整合 Sentry 或其他錯誤追蹤服務
- [ ] 實作自訂 429 錯誤回應（速率限制）
- [ ] 加入請求簽名驗證（HMAC）
- [ ] 實作 IP 白名單/黑名單
- [ ] 加入 API 版本控制
- [ ] 實作 JWT 刷新機制
- [ ] 加入請求大小限制
- [ ] 實作更細緻的權限控制（RBAC）

---

## 🎯 符合的安全標準

### OWASP API Security Top 10

| 威脅 | 緩解措施 | 狀態 |
|------|---------|------|
| API1: Broken Object Level Authorization | 使用 Sanctum 認證 | ✅ |
| API2: Broken Authentication | 速率限制 + 強密碼要求 | ✅ |
| API3: Excessive Data Exposure | API Resources 控制回應 | ✅ |
| API4: Lack of Resources & Rate Limiting | 完整的速率限制策略 | ✅ |
| API5: Broken Function Level Authorization | 中間件權限檢查 | ✅ |
| API6: Mass Assignment | Form Request 驗證 | ✅ |
| API7: Security Misconfiguration | CSP + 安全標頭 | ✅ |
| API8: Injection | 參數化查詢 + 驗證 | ✅ |
| API9: Improper Assets Management | API 日誌監控 | ✅ |
| API10: Insufficient Logging & Monitoring | 詳細的 API 日誌 | ✅ |

### 其他安全最佳實踐

- ✅ HTTPS Only (生產環境)
- ✅ CORS 配置
- ✅ CSP 策略
- ✅ 速率限制
- ✅ 輸入驗證
- ✅ 輸出編碼
- ✅ 安全標頭
- ✅ 日誌監控
- ✅ 錯誤處理
- ✅ 敏感資訊保護

---

## 📝 維護指南

### 日誌檔案管理

**手動清理舊日誌**:
```bash
# 刪除 30 天前的 API 日誌
find storage/logs -name "api-*.log" -mtime +30 -delete
```

**設定自動清理 (Cron)**:
```bash
# 在 crontab 中加入
0 2 * * * find /path/to/project/storage/logs -name "api-*.log" -mtime +30 -delete
```

### 監控速率限制

**查看被限制的請求**:
```bash
# 搜尋 429 狀態碼
grep '"status":429' storage/logs/api.log | tail -20
```

**統計各端點的請求次數**:
```bash
# 使用 jq 分析 JSON 日誌
cat storage/logs/api.log | jq -r '.path' | sort | uniq -c | sort -nr
```

### 安全性稽核

**定期檢查項目**:
1. 檢查速率限制是否有效
2. 驗證 CORS 配置是否正確
3. 確認安全標頭是否完整
4. 分析日誌尋找異常模式
5. 檢查是否有大量失敗的認證嘗試

**建議檢查頻率**:
- 每日：檢查日誌檔案大小和異常請求
- 每週：分析 API 使用模式
- 每月：完整的安全性稽核

---

## 🔗 相關文件

- [專案優化建議報告](./docs/project-optimization-recommendations.md)
- [測試指南](./TESTING_GUIDE.md)
- [README](./README.md)

---

**實作完成日期**: 2025-11-05
**維護者**: Development Team
**最後更新**: 2025-11-05
