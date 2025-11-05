# API Documentation with Laravel Scribe

> **Last Updated**: 2025-11-05
> **Scribe Version**: 5.5.0

## 概述

HoldYourBeer API 現在使用 Laravel Scribe 自動生成互動式文檔。Scribe 會從程式碼的 PHPDoc 註解中提取 API 資訊，並生成美觀的互動式文檔。

## 訪問文檔

### 開發環境

訪問以下 URL 查看 API 文檔：

- **互動式文檔**: http://localhost/docs
- **Postman Collection**: http://localhost/docs.postman
- **OpenAPI Spec**: http://localhost/docs.openapi

### 本地檔案位置

生成的文檔檔案存放在以下位置：

- **Postman Collection**: `storage/app/private/scribe/collection.json`
- **OpenAPI YAML**: `storage/app/private/scribe/openapi.yaml`
- **Blade Views**: `resources/views/scribe/`
- **Assets**: `public/vendor/scribe/`

## API 端點分組

文檔已按照以下方式分組：

### 1. Authentication（認證）
- `POST /api/register` - 註冊新用戶
- `POST /api/sanctum/token` - 登入並獲取 token
- `POST /api/logout` - 登出

### 2. Beer Brands（啤酒品牌）
- `GET /api/brands` - 獲取所有品牌列表

### 3. Beer Tracking（啤酒追蹤）
- `GET /api/beers` - 獲取我追蹤的啤酒列表（支援分頁、排序、篩選）
- `POST /api/beers` - 添加新啤酒到追蹤列表
- `POST /api/beers/{id}/count_actions` - 增加或減少品飲次數
- `GET /api/beers/{id}/tasting_logs` - 獲取品飲記錄

## 功能特點

### 1. 互動式測試

文檔包含 "Try It Out" 按鈕，允許直接在瀏覽器中測試 API 端點：
- 自動填入範例參數
- 支援身份驗證（Bearer Token）
- 顯示實際的請求和回應

### 2. 多語言範例

每個端點提供以下語言的範例程式碼：
- **Bash/cURL**
- **JavaScript/Fetch**

### 3. 完整的請求/回應範例

文檔包含：
- 所有必填和選填參數的說明
- 成功回應範例（200, 201）
- 錯誤回應範例（400, 404, 422）
- 參數驗證規則

### 4. 身份驗證說明

文檔清楚說明：
- 如何獲取 API token
- 如何在請求中使用 Bearer token
- 哪些端點需要認證（標記為 🔒）
- 哪些端點不需要認證

## 重新生成文檔

當你修改 API 端點或註解時，執行以下命令重新生成文檔：

```bash
php artisan scribe:generate
```

## 添加新端點的文檔

### 1. 為控制器類別添加分組

```php
/**
 * @group Group Name
 *
 * Group description
 */
class YourController extends Controller
{
    // ...
}
```

### 2. 為方法添加文檔

```php
/**
 * Endpoint title
 *
 * Detailed description of what this endpoint does.
 *
 * @authenticated  // 如果需要認證
 * @unauthenticated  // 如果不需要認證
 *
 * @urlParam id integer required The resource ID. Example: 1
 * @queryParam page integer Page number. Example: 1
 * @bodyParam name string required The name. Example: John Doe
 *
 * @response 200 {
 *   "data": {
 *     "id": 1,
 *     "name": "John Doe"
 *   }
 * }
 *
 * @response 404 {
 *   "error_code": "RES_001",
 *   "message": "Resource not found."
 * }
 */
public function yourMethod(Request $request)
{
    // ...
}
```

### 3. 常用的 Scribe 註解標籤

| 標籤 | 用途 | 範例 |
|------|------|------|
| `@group` | API 端點分組 | `@group User Management` |
| `@authenticated` | 需要認證 | `@authenticated` |
| `@unauthenticated` | 不需要認證 | `@unauthenticated` |
| `@urlParam` | URL 參數 | `@urlParam id integer required` |
| `@queryParam` | Query 參數 | `@queryParam page integer optional` |
| `@bodyParam` | Body 參數 | `@bodyParam name string required` |
| `@response` | 回應範例 | `@response 200 { "success": true }` |

## 配置選項

主要配置位於 `config/scribe.php`：

### 當前配置

```php
'title' => 'HoldYourBeer API Documentation',
'description' => 'HoldYourBeer API allows users to track their beer tastings...',
'base_url' => config('app.url'),
'type' => 'laravel',  // 使用 Laravel Blade 生成文檔

'auth' => [
    'enabled' => true,
    'default' => true,  // 預設所有端點需要認證
    'in' => 'bearer',   // Bearer token 認證
    'name' => 'Authorization',
],

'example_languages' => [
    'bash',
    'javascript',
],

'try_it_out' => [
    'enabled' => true,  // 啟用互動式測試
],
```

### 排除特定路由

```php
'routes' => [
    [
        'match' => [
            'prefixes' => ['api/*'],
        ],
        'exclude' => [
            'GET /api/user',  // Laravel Breeze 預設路由
        ],
    ],
],
```

## 整合到開發流程

### 1. Pre-commit Hook

可以在提交前自動檢查文檔是否需要更新：

```bash
# .git/hooks/pre-commit
php artisan scribe:generate
```

### 2. CI/CD Pipeline

在 CI/CD 流程中生成並部署文檔：

```yaml
# .github/workflows/ci.yml
- name: Generate API Documentation
  run: php artisan scribe:generate

- name: Deploy Documentation
  # 部署到文檔網站
```

## 進階功能

### 1. 自訂回應範例

使用 `@responseFile` 載入外部 JSON 檔案：

```php
/**
 * @responseFile storage/responses/users.json
 */
```

### 2. 回應欄位說明

```php
/**
 * @responseField id integer The user ID.
 * @responseField name string The user's full name.
 */
```

### 3. 自訂範例值

在 Form Request 中定義 `bodyParameters()` 方法：

```php
public function bodyParameters()
{
    return [
        'name' => [
            'description' => 'The user name.',
            'example' => 'John Doe',
        ],
    ];
}
```

## 常見問題

### Q: 如何測試需要認證的端點？

A: 在文檔頁面右上角點擊 "Authenticate" 按鈕，輸入你的 Bearer token。

### Q: 如何匯出 Postman Collection？

A: 訪問 http://localhost/docs.postman 或直接使用 `storage/app/private/scribe/collection.json`。

### Q: 如何客製化文檔外觀？

A: 修改 `resources/views/scribe/` 下的 Blade 模板檔案。

### Q: 生成文檔時出現資料庫錯誤？

A: 確保 `config/scribe.php` 中的 `database_connections_to_transact` 配置正確。

## 參考資源

- **Scribe 官方文檔**: https://scribe.knuckles.wtf/
- **Laravel 文檔**: https://laravel.com/docs/12.x
- **OpenAPI 規範**: https://swagger.io/specification/

## 維護建議

1. **定期更新文檔**: 每次修改 API 後重新生成文檔
2. **保持註解完整**: 確保所有公開端點都有完整的文檔註解
3. **測試互動功能**: 定期測試 "Try It Out" 功能是否正常
4. **版本控制**: 將 `.scribe/` 目錄加入 `.gitignore`，但保留配置檔案
5. **持續改進**: 根據團隊反饋優化文檔內容和格式

---

**文件版本**: v1.0
**維護者**: Development Team
**最後更新**: 2025-11-05
