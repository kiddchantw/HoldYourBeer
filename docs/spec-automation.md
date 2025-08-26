# 規格自動化工具使用指南

這個文件說明如何使用 HoldYourBeer 專案的規格自動化工具來維護規格文件與測試文件的同步。

## 🎯 概述

專案現在提供兩個 Artisan 指令來自動化管理規格追蹤：

- **`php artisan spec:check`** - 檢查規格與測試的一致性
- **`php artisan spec:sync`** - 自動同步規格文件與測試文件

## 📋 指令說明

### spec:check - 一致性檢查

檢查 `.feature` 文件與對應測試文件的一致性。

#### 基本用法
```bash
# 檢查所有規格文件
php artisan spec:check

# 嚴格模式 - 如果發現不一致則退出碼為 1
php artisan spec:check --strict

# CI/CD 模式 - 輸出 JSON 格式報告
php artisan spec:check --ci
```

#### 在 Laradock 環境中使用
```bash
# 從專案根目錄執行
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace php artisan spec:check
```

#### 檢查項目
- ✅ Feature 文件是否有狀態追蹤表格
- ✅ 測試文件是否存在
- ✅ 測試方法是否與狀態表格對應
- ✅ 測試類別是否有 `@covers` 註解

#### 輸出範例
```
🔍 Checking spec-test consistency...

📊 Spec-Test Consistency Report:
┌─────────────────────────────┬───────┐
│ Metric                      │ Count │
├─────────────────────────────┼───────┤
│ Total Feature Files         │ 8     │
│ Features with Status Tracking│ 6     │
│ Features Missing Tests      │ 1     │
│ Total Test Files           │ 24    │
│ Tests with Spec Annotations │ 20    │
└─────────────────────────────┴───────┘
```

### spec:sync - 自動同步

自動同步規格文件狀態，根據現有測試更新狀態追蹤表格。

#### 基本用法
```bash
# 同步所有規格文件
php artisan spec:sync

# 預覽模式 - 查看會做什麼變更但不實際修改
php artisan spec:sync --dry-run

# 強制更新 - 即使檔案有手動變更也更新
php artisan spec:sync --force
```

#### 在 Laradock 環境中使用
```bash
# 從專案根目錄執行
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace php artisan spec:sync
```

#### 功能特色
- 🔄 自動推斷測試文件路徑
- ➕ 為缺少狀態追蹤的 feature 文件添加表格
- ✏️ 根據測試方法更新狀態追蹤表格
- 🏃 支援 dry-run 模式安全預覽

#### 輸出範例
```
🔄 Synchronizing spec files with test files...

📊 Spec Sync Report:
┌─────────────────────────────┬───────┐
│ Metric                      │ Count │
├─────────────────────────────┼───────┤
│ Total Feature Files         │ 8     │
│ Updated Features           │ 3     │
│ Missing Test Files Found   │ 1     │
│ Total Updates             │ 4     │
└─────────────────────────────┴───────┘

📝 Changes Made:
  ➕ Added missing status tracking table
     File: /spec/features/loading_states.feature
  ✏️ Updated status tracking table
     File: /spec/features/multilingual_switching.feature
```

## 🔧 整合到開發流程

### 1. 日常開發檢查
在開始開發前檢查現況：
```bash
php artisan spec:check
```

### 2. 開發完成後同步
完成測試後更新規格狀態：
```bash
# 先預覽變更
php artisan spec:sync --dry-run

# 確認無誤後執行
php artisan spec:sync
```

### 3. 提交前驗證
提交代碼前確保一致性：
```bash
php artisan spec:check --strict
```

如果退出碼為 1，表示有不一致需要修正。

## 📁 自動推斷邏輯

### 測試文件路徑推斷
工具會根據 feature 文件路徑自動推斷對應的測試文件：

```
spec/features/user-registration.feature
→ tests/Feature/UserRegistrationTest.php

spec/features/beer_tracking/adding_a_beer.feature  
→ tests/Feature/BeerTracking/AddingABeerTest.php
   或 tests/Feature/AddingABeerTest.php
```

### 狀態推斷規則
- 測試存在且通過 → `DONE`
- 測試存在但失敗 → `IN_PROGRESS`  
- 測試不存在 → `TODO`

## 🎨 狀態追蹤表格格式

工具會自動維護此格式的狀態追蹤表格：

```gherkin
# 1. Status: TODO | IN_PROGRESS | DONE
# 2. Design: docs/diagrams/feature-name-flow.md
# 3. Test: tests/Feature/FeatureTest.php
# 4. Scenario Status Tracking:
# | Scenario Name                    | Status        | Test Method                    | UI  | Backend |
# |----------------------------------|---------------|--------------------------------|-----|---------|
# | User can register with email    | DONE          | test_user_can_register         | DONE| DONE    |
# | Duplicate email validation      | IN_PROGRESS   | test_duplicate_email_validation| TODO| DONE    |
```

## 🚀 最佳實踐

### 開發流程建議
1. **開始開發前**：執行 `spec:check` 了解現況
2. **撰寫測試時**：使用規範的測試方法命名
3. **完成功能後**：執行 `spec:sync` 更新狀態
4. **提交前**：執行 `spec:check --strict` 驗證

### 測試方法命名建議
```php
// ✅ 好的命名
test_user_can_register()
test_duplicate_email_validation()
test_beer_creation_with_brand()

// ❌ 避免的命名  
testExample()
test1()
basicTest()
```

### 規格文件維護
- 保持場景描述清晰簡潔
- 定期執行 `spec:sync` 保持同步
- 手動調整自動生成的狀態如有需要

## 🐛 疑難排解

### 常見問題

**Q: 工具找不到我的測試文件**
A: 檢查測試文件命名是否遵循 Laravel 慣例，以 `Test.php` 結尾

**Q: 狀態追蹤表格格式不正確**
A: 執行 `spec:sync --force` 重新生成表格

**Q: 在 Laradock 中指令執行失敗**
A: 確認使用正確的 docker-compose 路徑和工作目錄

**Q: dry-run 模式看起來正確，但實際執行有問題**
A: 檢查文件權限，確保 Laravel 可以寫入 spec 目錄

### 調試技巧
```bash
# 檢查具體的錯誤訊息
php artisan spec:check --ci | jq '.issues'

# 查看會修改哪些文件
php artisan spec:sync --dry-run

# 檢查 Laravel 日誌
tail -f storage/logs/laravel.log
```

---

**注意**: 這些工具會直接修改 `.feature` 文件。建議在使用前先提交當前變更，以便必要時可以回滾。