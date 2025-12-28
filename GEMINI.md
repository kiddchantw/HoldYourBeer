# GEMINI.md - HoldYourBeer Backend Workspace

## 🌍 Workspace Overview

這是 **HoldYourBeer** 後端專案 (Laravel) 的專用 GEMINI 設定檔。
由於專案結構為 Monorepo 風格 (Laradock 在外層)，請務必參考此文件以正確執行 Docker 指令。

## 📍 Directory Structure

- **Backend Root**: `/var/www/beer/HoldYourBeer` (Current Directory)
- **Laradock Root**: `../../laradock`
- **Laradock Config**: `../../laradock/docker-compose.yml`

## 🚀 Correct Command Templates

請**務必**使用以下模板執行指令，不要猜測其他路徑：

### 1. Run Artisan Commands (in Workspace)
```bash
docker-compose -f ../../laradock/docker-compose.yml exec -T -w /var/www/beer/HoldYourBeer workspace php artisan <COMMAND>
```
*   `exec -T`: 確保在非互動模式下執行 (避免 TTY 錯誤)
*   `-w /var/www/beer/HoldYourBeer`: 強制指定工作目錄

### 2. Run Tests (SQLite)
```bash
docker-compose -f ../../laradock/docker-compose.yml exec -T -w /var/www/beer/HoldYourBeer workspace php artisan test <TEST_FILE>
```

### 3. Composer
```bash
docker-compose -f ../../laradock/docker-compose.yml exec -T -w /var/www/beer/HoldYourBeer workspace composer <COMMAND>
```

## ⚠️ Common Pitfalls

1.  **找不到 docker-compose.yml**: 千萬不要用 `../laradock`，要用 `../../laradock`。
2.  **TTY Error**: 記得加 `-T` 參數。
3.  **No Configuration File**: 必須加上 `-w /var/www/beer/HoldYourBeer` 確保在正確目錄下執行 PHPUnit。

## 🔗 Global References

- **完整文件**: 詳見根目錄的 `../laradock_setting.md`
