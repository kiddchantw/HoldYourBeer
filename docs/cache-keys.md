# Cache Keys Documentation

> **Last Updated**: 2025-12-04
> **Purpose**: 記錄應用程式中所有使用的快取鍵，方便維護和除錯

## 概述

本文件記錄 HoldYourBeer 應用程式中所有使用的快取鍵，包括：
- 快取鍵名稱
- 用途說明
- TTL (Time To Live)
- 失效機制
- 相關檔案位置

---

## 快取鍵清單

### 品牌相關 (Brands)

#### `brands_list`
- **用途**: 快取品牌列表，按名稱字母順序排序
- **TTL**: 3600 秒 (1 小時)
- **失效機制**: 
  - 自動失效：透過 `BrandObserver` 監聽 `created`, `updated`, `deleted`, `restored` 事件
  - 手動清除：`Cache::forget('brands_list')`
- **相關檔案**:
  - Controller: `app/Http/Controllers/Api/BrandController.php`
  - Observer: `app/Observers/BrandObserver.php`
  - API Endpoint: `GET /api/v1/brands`
- **快取內容**: `Illuminate\Database\Eloquent\Collection` (Brand models)
- **預期快取命中率**: >95%

#### `brand_statistics` (預留)
- **用途**: 預留給未來品牌統計資料快取
- **TTL**: 未實作
- **失效機制**: 透過 `BrandObserver` 自動清除
- **狀態**: 已預留清除邏輯，尚未實作快取建立

#### `beer_charts_data` (預留)
- **用途**: 預留給未來啤酒圖表資料快取
- **TTL**: 未實作
- **失效機制**: 透過 `BrandObserver` 自動清除
- **狀態**: 已預留清除邏輯，尚未實作快取建立

---

## 快取策略

### 快取驅動
- **開發環境**: File-based cache (`CACHE_DRIVER=file`)
- **測試環境**: Array cache (`CACHE_DRIVER=array`)
- **生產環境**: 建議使用 Redis (`CACHE_DRIVER=redis`)

### 失效策略
1. **自動失效**: 透過 Model Observer 監聽資料變更事件
2. **時間失效**: TTL 到期後自動失效
3. **手動清除**: 使用 `Cache::forget()` 或 `php artisan cache:clear`

### 快取模式
- **Cache-Aside Pattern**: 應用程式負責管理快取
- **Write-Through**: 資料變更時自動清除相關快取

---

## 維護指南

### 新增快取鍵
1. 在此文件中記錄新的快取鍵
2. 在相關 Observer 中加入清除邏輯（如適用）
3. 更新相關 Controller 的 PHPDoc 說明

### 清除快取
```bash
# 清除所有快取
php artisan cache:clear

# 清除特定快取（Tinker）
php artisan tinker
>>> Cache::forget('brands_list')
```

### 監控快取
```bash
# 檢查快取狀態（如果實作了監控指令）
php artisan cache:status

# 檢查特定快取是否存在（Tinker）
php artisan tinker
>>> Cache::has('brands_list')
>>> Cache::get('brands_list')
```

---

## 相關文件

- [品牌列表快取實作 Session](sessions/2025-12/04-brand-list-cache-implementation.md)
- [Laravel Cache 官方文件](https://laravel.com/docs/11.x/cache)
- [Laravel Observer 官方文件](https://laravel.com/docs/11.x/eloquent#observers)

---

## 更新記錄

| 日期 | 變更內容 | 作者 |
|------|---------|------|
| 2025-12-04 | 初始版本，記錄 `brands_list` 快取鍵 | @kiddchan |

