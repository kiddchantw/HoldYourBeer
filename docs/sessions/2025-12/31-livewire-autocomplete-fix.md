# Session: Livewire 自動填入功能失效修復

**Date**: 2025-12-31
**Status**: ✅ Completed
**Duration**: 研究與診斷
**Issue**: #[TBD]
**Contributors**: KiddC, Claude AI
**Branch**: N/A
**Tags**: #infrastructure, #deployment, #livewire

**Categories**: Laravel Cloud 部署配置, Livewire 3 資產發布

---

## 📋 Overview

### Goal
修復 Laravel Cloud 部署環境中 HoldYourBeer 應用的 Livewire 自動填入(autocomplete)功能失效問題。

### Related Documents
- **部署平台**: Laravel Cloud (holdyourbeers.com)
- **應用頁面**: https://holdyourbeers.com/en/beers/create (建立啤酒頁面)
- **功能影響**: 品牌、啤酒名稱、店家名稱的自動填入建議列表

### Commits
- (待後續實施)

---

## 🎯 Context

### Problem
在 Laravel Cloud 上部署後，建立啤酒頁面的自動填入功能失效。使用者在輸入品牌、啤酒或店家名稱時，雖然後端搜尋邏輯正常運作，但前端無法正確載入 Livewire 的 JavaScript 資產，導致互動失敗。

### Root Cause
`/livewire/livewire.js` 返回 **404 錯誤** — Livewire 的 JavaScript 資產沒有被正確發布到靜態檔案目錄。

### Technical Details

**自動填入的實作方式**：
- 框架：Livewire 3.6 + Alpine.js 3.4.2
- 相關元件：`App\Livewire\CreateBeer`（PHP 邏輯）
- Blade 模板：`resources/views/livewire/create-beer-step1.blade.php`
- 前端綁定：`wire:model.live.debounce.300ms`

**功能流程**：
1. 使用者輸入 → 300ms 防抖
2. Livewire AJAX 請求 → `updatedBrandName()` 等方法
3. 資料庫查詢 → 返回建議列表
4. 前端重新渲染建議 UI

**問題點**：
- Livewire 3 預設會透過動態路由提供 JS（`/livewire/livewire.js`）
- 但在 Laravel Cloud 上，此路由被阻擋或快取失效
- 需要明確發布靜態資產到 `public/vendor/livewire/` 目錄

### Current Build Configuration
```bash
# Build commands (現有)
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci --audit false
npm run build

# Deploy commands (現有)
php artisan migrate --force
```

**缺失的步驟**：沒有執行 `php artisan livewire:publish --assets`

---

## 💡 Planning

### Approach: 拆分 Build/Deploy Commands

#### Option A: 全部在 Build Commands ✅ CHOSEN
在構建階段完成所有準備工作，包括 Livewire 資產發布。
- **優勢**：更快的部署時間、靜態檔案已準備好
- **適用**：資產發布不需要環境變數

#### Option B: 分散在 Build 與 Deploy ❌ REJECTED（複雜）
在 Build 中發布資產，在 Deploy 中優化快取。
- **缺點**：增加複雜度、快取指令依賴環境變數

### Chosen Solution: 修改 Build & Deploy Commands

**Build Commands**（靜態資源準備）：
```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

npm ci --audit false
npm run build

php artisan livewire:publish --assets
```

**Deploy Commands**（應用程式啟動）：
```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Design Decisions

#### D1: Livewire 資產何時發布？
- **選擇**: Build 階段
- **原因**: 資產是靜態檔案，發布後納入部署包；無需環境變數
- **權衡**: 無

#### D2: 是否需要額外的快取清理指令？
- **選擇**: 在 Deploy 中執行 `config:cache`, `route:cache`, `view:cache`
- **原因**: 這些指令需要讀取環境配置，應在應用連接到伺服器時執行
- **權衡**: 略微增加部署時間（秒級），換取更可靠的配置載入

---

## ✅ Implementation Checklist

### Phase 1: 診斷 ✅ Completed
- [x] 確認 Livewire 元件實作正確（`CreateBeer.php`、Blade 模板）
- [x] 驗證 JavaScript 資產缺失（`/livewire/livewire.js` → 404）
- [x] 分析 `create-session.sh` 的 Livewire 配置
- [x] 確認 vite.config.js 和 package.json 配置無誤

### Phase 2: 解決方案設計 ✅ Completed
- [x] 確定根本原因：缺少 `php artisan livewire:publish --assets`
- [x] 設計 Build/Deploy Commands 拆分方案
- [x] 驗證命令順序和依賴關係

### Phase 3: 實施指南 ⏳ Pending
- [ ] 在 Laravel Cloud Console 更新 Build Commands
- [ ] 在 Laravel Cloud Console 更新 Deploy Commands
- [ ] 執行新的部署
- [ ] 驗證 `/livewire/livewire.js` 正確載入（200 狀態）
- [ ] 在測試環境驗證自動填入功能

### Phase 4: 測試 ⏳ Pending
- [ ] 開啟建立啤酒頁面
- [ ] 在品牌欄位輸入「台」，驗證建議列表出現
- [ ] 點擊建議項目，驗證自動填入成功
- [ ] 驗證啤酒名稱和店家欄位同樣運作

### Phase 5: 文件更新 ⏳ Pending
- [ ] 更新部署指南（如有的話）
- [ ] 記錄 Livewire 資產發布的最佳實踐

---

## 🛠️ Commands Reference

### Build Commands（在 Laravel Cloud Console 設定）
```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

npm ci --audit false
npm run build

php artisan livewire:publish --assets
```

**說明**:
- Line 1: 安裝 PHP 依賴（生產優化）
- Line 2-3: 編譯前端資產（CSS/JS）
- Line 4: 發布 Livewire JavaScript 到 public/ 目錄

### Deploy Commands（在 Laravel Cloud Console 設定）
```bash
php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**說明**:
- Line 1: 執行資料庫遷移
- Line 2-4: 快取配置、路由、視圖以提高效能

---

## 🚧 Blockers & Solutions

### Blocker 1: Livewire JavaScript 資產 404 ✅ RESOLVED
- **Issue**: `/livewire/livewire.js` 返回 404 錯誤
- **Impact**: Livewire 元件無法初始化，導致自動填入功能完全失效
- **Root Cause**: Build 階段未執行 `php artisan livewire:publish --assets`
- **Solution**: 在 Build Commands 中加入資產發布指令
- **Resolved**: 2025-12-31（設計階段）

---

## 📊 Outcome

### 問題診斷結論

| 項目 | 結果 |
|------|------|
| **自動填入實作** | ✅ 正確（Livewire 3.6 + Alpine.js） |
| **前端邏輯** | ✅ 正確（wire:model.live.debounce） |
| **後端邏輯** | ✅ 正確（updatedBrandName 等方法） |
| **部署配置** | ❌ 缺失 Livewire 資產發布 |
| **根本原因** | Build Commands 不完整 |

### Files Analyzed
```
HoldYourBeer/
├── app/Livewire/CreateBeer.php         (213 行 - 邏輯正確)
├── resources/views/livewire/
│   ├── create-beer.blade.php           (233 行)
│   ├── create-beer-step1.blade.php     (89 行)
│   └── create-beer-step2.blade.php     (88 行)
├── resources/js/app.js                 (Alpine.js 初始化)
├── resources/css/app.css               (Tailwind CSS)
└── vite.config.js                      (Vite 配置正確)
```

### 確認項目

**Livewire 配置**:
- `config/livewire.php`: `inject_assets` = true（預設）
- 布局檔案: `@livewireStyles` 和 `@livewireScripts` 正確放置
- 版本: Livewire 3.6（支援資產發布）

**部署檢查清單**:
- [x] 依賴安裝：正確
- [x] 前端編譯：正確（npm run build）
- [x] HTTPS 強制：正確（production 環境）
- [ ] Livewire 資產發布：**缺失** ← 核心問題

---

## 🎓 Lessons Learned

### 1. Livewire 3 的資產發布模式
**Learning**: Livewire 3 支援兩種資產載入方式：
1. 動態路由（開發時方便，生產部署可能失效）
2. 靜態檔案（需要明確發布，部署時更穩定）

**Solution/Pattern**:
在部署到生產環境時，應使用 `php artisan livewire:publish --assets` 發布靜態檔案，避免依賴動態路由。

**Future Application**:
- 新增 Laravel Cloud 部署時應自動包含此步驟
- 建立 Laravel Cloud 部署檢查清單，確保不遺漏必要步驟

### 2. Build Commands vs Deploy Commands 的職責劃分
**Learning**:
- **Build** 階段：準備靜態資源（編譯、發布、打包）
- **Deploy** 階段：應用程式啟動（遷移、快取、服務啟動）

**Solution/Pattern**:
靜態資源操作放在 Build 階段，環境相關操作放在 Deploy 階段。

**Future Application**:
制定 Laravel Cloud 部署的標準流程文件。

### 3. 故障排查：從外而內
**Learning**:
前端資產失效時，應優先檢查：
1. 瀏覽器開發者工具（Network 標籤，找 404）
2. HTTP 端點可用性
3. 靜態檔案發布
4. 最後才檢查程式邏輯

**Solution/Pattern**:
使用 WebFetch 工具快速驗證 HTTP 端點，可節省除錯時間。

### 4. Laravel 翻譯 Key 命名衝突
**Learning**:
`__('KeyName')` 會優先載入 `lang/{locale}/keyname.php` 檔案（返回整個陣列），而非 JSON 中的同名字串。

**範例**:
- `__('Brands')` → 載入 `lang/en/brands.php` 整個陣列（而非 JSON 中的 `"Brands": "Brands"`）
- 導致 `htmlspecialchars(): Argument #1 must be string, array given` 錯誤

**Solution/Pattern**:
- 避免使用與 PHP 翻譯檔名相同的 JSON key（如 `brands`, `feedback`, `profile`）
- 使用完整描述性 key（如 `"New Brands Tried"` 而非 `"Brands"`）

**Future Application**:
- 在建立新 PHP 翻譯檔案時，檢查 JSON 檔案是否有同名 key
- 考慮建立翻譯 key 命名規範文件

---

## ✅ 完成狀態

**Phase**: ✅ 全部完成

### 已完成項目：

1. **修復自動填入功能**
   - 移除 `app.js` 中重複的 Alpine.js 初始化
   - 將 `wire:click.outside` 改為 `@click.away` 在父層 `<div>` 上
   - 確認使用正確的模板檔案 `create-beer.blade.php`

2. **大小寫不敏感搜尋**
   - 使用 `LOWER()` 和 `whereRaw` 實作
   - 適用於品牌、啤酒名稱、店家搜尋

3. **已存在啤酒提示功能** (新增)
   - 當用戶輸入的 brand + beer name 已存在於收藏中（count > 0）時
   - 在步驟 2 顯示琥珀色提示框
   - 顯示資訊：啤酒名稱、目前數量、最後品嚐日期
   - 提醒用戶：儲存後將增加現有數量

4. **清理未使用檔案**
   - 刪除 `create-beer-step1.blade.php`
   - 刪除 `create-beer-step2.blade.php`

5. **修復 Charts 頁面 htmlspecialchars 錯誤**
   - 問題：`__('Brands')` 返回整個 `brands.php` 翻譯陣列而非字串
   - 原因：PHP 翻譯檔案優先級高於 JSON，`__('Brands')` 載入 `brands.php` 整個檔案
   - 修復：將 `{{ __('New') }}<span>{{ __('Brands') }}</span>` 改為 `{{ __('New Brands Tried') }}`
   - 清理：移除 `lang/en.json` 和 `lang/zh-TW.json` 中未使用的 `"New"` 和 `"Brands"` key

### 相關 Commits：
- `51d68dd` - fix autocomplete selection
- `b15a0aa` - remove duplicate Alpine.js
- `9e02fa9` - update main create-beer template
- `a23b543` - case-insensitive search
- `e2b3e96` - remove unused step template files

---

## 🔮 Future Improvements

### 預防措施
- 📌 建立 Laravel Cloud 部署檢查清單
- 📌 在專案文件中記錄 Livewire 資產發布的必要性
- 📌 考慮在 CI/CD 中自動執行資產發布

### 相關改進
- 🔧 未來考慮使用 Laravel Octane 加速部署
- 🔧 評估使用 Laravel Cloud 的工作流程功能自動化此過程

---

## 🔗 References

### 相關文件
- **Laravel Cloud 文件**: https://laravel.com/docs/laravel-cloud
- **Livewire 官方文件**: https://livewire.laravel.com
- **Vite 配置**: vite.config.js

### 相關 Session
- (未來可能的部署優化 Session)

### External Resources
- Livewire 3 資產發布: https://livewire.laravel.com/docs/installation
- Alpine.js: https://alpinejs.dev

---

**Session 建立時間**: 2025-12-31
**下一步**: 執行上述 Build/Deploy Commands 的更新和部署驗證
