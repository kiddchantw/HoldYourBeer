# 測試指南 - 優先級5程式碼品質改善

> 本指南說明如何測試最新的程式碼品質改善（Service Layer、API Resources、Form Requests）

---

## 📋 改善概要

### 已實作項目 (2025-11-05)

1. **API Resources** ✅
   - `BeerResource.php` - 統一啤酒資料格式
   - `BrandResource.php` - 統一品牌資料格式
   - `TastingLogResource.php` - 統一品飲記錄格式

2. **Form Request Validation** ✅
   - `StoreBeerRequest.php` - 新增啤酒驗證
   - `CountActionRequest.php` - 計數操作驗證
   - `RegisterRequest.php` - 用戶註冊驗證

3. **Service Layer** ✅
   - `TastingService.php` - 品飲業務邏輯服務

4. **控制器重構** ✅
   - `BeerController.php` - 減少 32% 程式碼
   - `AuthController.php` - 簡化註冊邏輯
   - `BrandController.php` - 使用 Resource

---

## 🧪 執行測試

### 前置需求

確保您的 Laradock 環境已啟動：

```bash
cd laradock
docker-compose up -d nginx postgres workspace
```

### 1. 執行完整測試套件

```bash
# 從專案根目錄執行
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test

# 或使用 PHPUnit 直接執行
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace ./vendor/bin/phpunit
```

### 2. 執行特定測試類別

#### 測試 API 端點（包含新的 Resources）

```bash
# 測試啤酒 API 端點
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test --filter=BeerEndpointsTest

# 測試認證控制器
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test --filter=AuthControllerTest

# 測試品牌控制器
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test --filter=BrandControllerTest
```

#### 測試業務邏輯（Service Layer）

```bash
# 測試品飲功能（現在使用 TastingService）
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test --filter=TastingTest

# 測試啤酒創建（現在使用 StoreBeerRequest）
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test --filter=BeerCreationTest
```

### 3. 執行測試覆蓋率分析

```bash
# 使用 PHPUnit 產生覆蓋率報告（推薦）
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace ./vendor/bin/phpunit --coverage-text

# 或使用 artisan test（可能無法顯示詳細覆蓋率）
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test --coverage
```

---

## 🔍 手動測試 API 端點

### 測試環境設定

確保應用程式可訪問：
- Web: http://localhost
- API: http://localhost/api

### 1. 測試用戶註冊（使用 RegisterRequest）

**端點**: `POST /api/register`

```bash
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**預期回應** (使用標準化格式):
```json
{
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com",
    ...
  },
  "token": "..."
}
```

### 2. 測試獲取品牌列表（使用 BrandResource）

**端點**: `GET /api/brands`

```bash
curl -X GET http://localhost/api/brands \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**預期回應** (使用 BrandResource):
```json
[
  {
    "id": 1,
    "name": "Guinness"
  },
  {
    "id": 2,
    "name": "Heineken"
  }
]
```

### 3. 測試新增啤酒（使用 StoreBeerRequest 和 TastingService）

**端點**: `POST /api/beers`

```bash
curl -X POST http://localhost/api/beers \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Guinness Draught",
    "brand_id": 1,
    "style": "Stout"
  }'
```

**預期回應** (使用 BeerResource):
```json
{
  "id": 1,
  "name": "Guinness Draught",
  "style": "Stout",
  "brand": {
    "id": 1,
    "name": "Guinness"
  }
}
```

### 4. 測試計數操作（使用 CountActionRequest 和 TastingService）

**端點**: `POST /api/beers/{id}/count_actions`

```bash
# 增加計數
curl -X POST http://localhost/api/beers/1/count_actions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "increment",
    "note": "Very smooth!"
  }'

# 減少計數
curl -X POST http://localhost/api/beers/1/count_actions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "decrement"
  }'
```

**預期回應** (使用 BeerResource):
```json
{
  "id": 1,
  "name": "Guinness Draught",
  "style": "Stout",
  "brand": {
    "id": 1,
    "name": "Guinness"
  },
  "tasting_count": 2,
  "last_tasted_at": "2025-11-05T10:30:00.000000Z"
}
```

### 5. 測試品飲歷史（使用 TastingLogResource）

**端點**: `GET /api/beers/{id}/tasting_logs`

```bash
curl -X GET http://localhost/api/beers/1/tasting_logs \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**預期回應** (使用 TastingLogResource):
```json
[
  {
    "id": 1,
    "action": "increment",
    "tasted_at": "2025-11-05T10:30:00.000000Z",
    "note": "Very smooth!"
  },
  {
    "id": 2,
    "action": "initial",
    "tasted_at": "2025-11-05T10:00:00.000000Z",
    "note": null
  }
]
```

---

## ✅ 驗證項目清單

### Service Layer 驗證
- [ ] `TastingService::incrementCount()` 正確增加計數
- [ ] `TastingService::decrementCount()` 正確減少計數（且不低於 0）
- [ ] `TastingService::addBeerToTracking()` 正確創建初始記錄
- [ ] `TastingService::getTastingLogs()` 正確返回歷史記錄
- [ ] 資料庫事務正常運作（失敗時回滾）
- [ ] 行級鎖定防止競態條件

### API Resources 驗證
- [ ] `BeerResource` 正確轉換啤酒資料
- [ ] `BrandResource` 正確轉換品牌資料
- [ ] `TastingLogResource` 正確轉換品飲記錄
- [ ] 關聯資料使用 `whenLoaded()` 優化載入
- [ ] JSON 回應格式統一且一致

### Form Requests 驗證
- [ ] `StoreBeerRequest` 正確驗證新增啤酒資料
- [ ] `CountActionRequest` 正確驗證計數操作
- [ ] `RegisterRequest` 正確驗證註冊資料
- [ ] 驗證失敗時返回標準化錯誤訊息
- [ ] 自訂錯誤訊息正確顯示

### 控制器重構驗證
- [ ] `BeerController` 使用 Service Layer 處理業務邏輯
- [ ] `BeerController` 使用 Resources 格式化回應
- [ ] `BeerController` 使用 Form Requests 驗證
- [ ] `AuthController` 簡化註冊邏輯
- [ ] `BrandController` 使用 BrandResource
- [ ] 錯誤處理使用標準化錯誤碼 (error_code)

### 向後相容性驗證
- [ ] 所有現有測試通過
- [ ] API 回應格式保持一致（沒有破壞性變更）
- [ ] 資料庫事務和鎖定機制保持完整

---

## 🐛 常見問題排解

### 1. 測試失敗：Class not found

**原因**: Composer autoload 需要重新生成

**解決方案**:
```bash
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace composer dump-autoload
```

### 2. 測試失敗：Database connection error

**原因**: 測試資料庫未設定或未遷移

**解決方案**:
```bash
# 確認測試環境變數
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace cat .env.testing

# 執行測試資料庫遷移
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan migrate --env=testing
```

### 3. API 測試回應格式不符預期

**原因**: 可能是快取問題

**解決方案**:
```bash
# 清除所有快取
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan cache:clear
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan config:clear
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan route:clear
```

### 4. Service Layer 測試失敗：Transaction error

**原因**: 資料庫不支援事務或鎖定

**檢查**:
```bash
# 確認使用 PostgreSQL（支援事務和鎖定）
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan tinker

# 在 tinker 中執行
>>> DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME)
=> "pgsql"  // 應該是 pgsql
```

---

## 📊 效能比較測試

### 測試控制器程式碼減少

```bash
# 查看新舊控制器行數
git show HEAD~1:app/Http/Controllers/Api/BeerController.php | wc -l
# 輸出：199 行（重構前）

wc -l app/Http/Controllers/Api/BeerController.php
# 輸出：136 行（重構後）

# 減少：63 行 (-32%)
```

### 測試回應時間改善

使用 Apache Bench 或類似工具測試 API 回應時間：

```bash
# 測試獲取啤酒列表（10 個並發請求，共 100 次）
ab -n 100 -c 10 -H "Authorization: Bearer YOUR_TOKEN" http://localhost/api/beers

# 記錄 Time per request 指標
```

---

## 📝 測試報告範本

完成測試後，請填寫以下報告：

```markdown
## 測試報告 - 優先級5程式碼品質改善

**測試日期**: YYYY-MM-DD
**測試者**: [Your Name]
**測試環境**: Laradock / PHP 8.3 / PostgreSQL 17

### 測試結果

#### 單元測試
- 總測試數: __
- 通過: __
- 失敗: __
- 跳過: __

#### API 手動測試
- [ ] 用戶註冊 - ✅/❌
- [ ] 品牌列表 - ✅/❌
- [ ] 新增啤酒 - ✅/❌
- [ ] 計數操作 - ✅/❌
- [ ] 品飲歷史 - ✅/❌

#### 效能測試
- 控制器程式碼減少: __%
- API 回應時間改善: __ms → __ms

#### 問題與建議
- [列出發現的問題]
- [列出改進建議]

### 結論
[整體評估]
```

---

## 🚀 下一步

測試通過後，建議：

1. **合併到主分支**
   ```bash
   # 建立 Pull Request
   # 請求團隊審查
   # 合併到 main 分支
   ```

2. **部署到測試環境**
   ```bash
   # 依照部署流程執行
   ```

3. **監控生產環境**
   - 監控 API 回應時間
   - 監控錯誤率
   - 收集使用者回饋

---

**相關文件**:
- [專案優化建議報告](docs/project-optimization-recommendations.md)
- [README](README.md)
- [規格自動化指南](docs/spec-automation.md)
