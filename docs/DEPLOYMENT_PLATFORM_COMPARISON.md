# 部署平台比較與專案更新記錄

> 文件建立日期：2025-11-05
> 作者：Claude Code
> 專案：HoldYourBeer

本文件記錄了兩個重要主題：
1. Google 登入功能的國際化修正
2. Laravel Cloud vs Zeabur 部署平台完整比較

---

## 第一部分：國際化翻譯修正

### 問題發現

在實作 Web Google 登入功能時，發現 Google 登入按鈕元件使用了翻譯函數，但缺少對應的翻譯鍵。

### 影響範圍

**元件位置**：`resources/views/components/google-login-button.blade.php`

```blade
<!-- Line 12 -->
<span>{{ $slot->isEmpty() ? __('Sign in with Google') : $slot }}</span>
```

**問題**：翻譯鍵 `'Sign in with Google'` 在兩個語言檔案中都不存在：
- `lang/en.json`
- `lang/zh-TW.json`

### 修正內容

#### 1. 英文語言檔 (`lang/en.json`)

**位置**：第 35 行

```json
{
    ...
    "Remember me": "Remember me",
    "Forgot your password?": "Forgot your password?",
    "Sign in with Google": "Sign in with Google"
}
```

#### 2. 繁體中文語言檔 (`lang/zh-TW.json`)

**位置**：第 56 行

```json
{
    ...
    "Remember me": "記住我",
    "Forgot your password?": "忘記密碼？",
    "Sign in with Google": "使用 Google 登入"
}
```

### 提交記錄

```
commit 613b431
i18n: add missing 'Sign in with Google' translations

Added translation keys for Google login button in both languages:
- English: "Sign in with Google"
- Chinese (Traditional): "使用 Google 登入"

This fixes the missing translation used in the google-login-button
Blade component.
```

### 結果

✅ Google 登入按鈕現在會正確顯示對應語言的文字
✅ 不再顯示未翻譯的鍵值
✅ 支援英文和繁體中文雙語切換

---

## 第二部分：部署平台比較分析

### 平台概述

#### Laravel Cloud
- **開發團隊**：Laravel 官方（Taylor Otwell）
- **推出時間**：2025 年 2 月 24 日（非常新）
- **定位**：專為 Laravel/PHP 應用優化的全託管平台
- **基礎架構**：AWS EC2 專屬伺服器
- **官網**：https://cloud.laravel.com

#### Zeabur
- **開發團隊**：台灣新創團隊
- **推出時間**：2024 年
- **定位**：多語言支援的 PaaS 平台
- **特色**：Product Hunt 破千票台灣第一名
- **官網**：https://zeabur.com
- **語言支援**：完整中文文件與技術支援

---

## 📊 完整功能與定價比較

### 定價方案對比

| 項目 | Laravel Cloud | Zeabur |
|------|---------------|--------|
| **入門方案** | Starter - $0/月 | Serverless - $0/月 |
| **入門限制** | 休眠模式、資源上限 | 僅靜態網站 + Serverless 函數 |
| **開發方案** | Growth - $20/月 | Developer - $5/月 |
| **企業方案** | Business - 高階定價 | Team - 中階定價 |
| **計費方式** | 月費 + 資源用量 | 按分鐘計費（vCPU + 記憶體） |
| **儲存費用** | $0.10-0.12/GB/月 | 包含在用量計費中 |

### 核心功能比較

| 功能 | Laravel Cloud | Zeabur |
|------|---------------|--------|
| **Laravel 整合** | ⭐⭐⭐⭐⭐ 原生完美整合 | ⭐⭐⭐⭐ 良好支援 |
| **自動部署** | ✅ Push-to-Deploy | ✅ 一鍵部署 |
| **Queue 管理** | ⭐⭐⭐⭐⭐ Queue Clusters 自動擴展 | ⭐⭐⭐ 需手動設定 |
| **Preview Environments** | ✅ 每個 PR 自動建立 | ❌ 無此功能 |
| **資料庫** | MySQL + Serverless Postgres | 支援多種資料庫服務 |
| **多語言支援** | ❌ 僅英文 | ✅ 完整中文 |
| **技術文件** | 英文為主 | 中文 + 英文 |
| **客服支援** | 英文 | 中文 + 英文 |

### Laravel 專屬功能

#### Laravel Cloud 優勢

```bash
# 自動執行的 Laravel 優化
php artisan optimize
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
```

**Queue Clusters 特色**：
- 🎯 自動擴展 Queue Workers
- 📊 即時監控 CPU、記憶體、Job 吞吐量
- 🔄 自動處理 Job Backlog
- ⚡ 零設定即用

**Preview Environments**：
- 每個 Pull Request 自動建立獨立環境
- 完整的生產環境模擬
- 自動部署並提供 URL
- PR 關閉後自動清理

#### Zeabur Laravel 支援

```bash
# 自動執行優化（與 Laravel Cloud 相同）
php artisan optimize
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# 自動編譯前端資源
npm install
npm run build
```

**部署特色**：
- 🚀 自動偵測 Laravel 專案
- 📦 自動處理 Composer 依賴
- 🎨 自動編譯 Vite/Laravel Mix
- ⚙️ 無需 Dockerfile

---

## 🎯 針對 HoldYourBeer 專案分析

### 專案技術棧回顧

```
Backend:     Laravel 12 + PHP 8.3
Database:    MySQL
Auth:        Laravel Sanctum + Firebase Auth
Mobile:      Flutter + Firebase
Queue:       FCM 推播通知
Frontend:    Blade + Tailwind CSS + Livewire
```

### 需求匹配度分析

#### 1. Queue Workers 需求（FCM 推播）

**Laravel Cloud**：⭐⭐⭐⭐⭐
```
✅ Queue Clusters 自動管理
✅ 自動擴展 Workers
✅ 即時監控效能
✅ 零設定成本
```

**Zeabur**：⭐⭐⭐
```
⚠️ 需手動設定 Queue Worker 容器
⚠️ 需手動配置擴展規則
✅ 支援 Redis Queue
✅ 可運作，但需額外配置
```

#### 2. 資料庫需求（MySQL）

**Laravel Cloud**：⭐⭐⭐⭐⭐
```
✅ 專屬 MySQL 服務
✅ 自動備份
✅ 可預測效能（AWS EC2）
```

**Zeabur**：⭐⭐⭐⭐
```
✅ 支援 MySQL 服務
✅ 容易設定
✅ 彈性計費
```

#### 3. Firebase 整合

**Laravel Cloud**：⭐⭐⭐⭐
```
✅ 標準 Laravel 環境
✅ 支援所有 PHP 套件
✅ kreait/laravel-firebase 正常運作
```

**Zeabur**：⭐⭐⭐⭐
```
✅ 標準 Laravel 環境
✅ 支援所有 PHP 套件
✅ kreait/laravel-firebase 正常運作
```

#### 4. 開發流程（Git + CI/CD）

**Laravel Cloud**：⭐⭐⭐⭐⭐
```
✅ Push-to-Deploy
✅ Preview Environments（每個 PR）
✅ 自動測試整合
✅ 適合團隊協作
```

**Zeabur**：⭐⭐⭐⭐
```
✅ Git 整合部署
✅ 自動部署
⚠️ 無 Preview Environments
✅ 支援多環境
```

---

## 💰 成本效益分析

### 小型專案場景（HoldYourBeer 當前階段）

#### Laravel Cloud 成本

```
基礎方案：
├─ Starter (免費)
│  └─ ⚠️ 休眠模式（不活躍時自動休眠）
│  └─ ❌ 可能影響即時性

建議方案：
├─ Growth - $20/月
│  ├─ ✅ 無休眠
│  ├─ ✅ Queue Clusters
│  ├─ ✅ Preview Environments
│  └─ ✅ Pro 規格

預估月費：$20-30
```

#### Zeabur 成本

```
基礎方案：
├─ Serverless (免費)
│  └─ ❌ 不支援容器化服務

建議方案：
├─ Developer - $5/月
│  ├─ ✅ $5 內資源免費
│  ├─ ✅ 容器化服務
│  ├─ ✅ 資料備份
│  └─ ✅ 按分鐘計費

預估月費：$5-10
（小流量情況下可能只需 $5）
```

### 成長期場景（流量增加後）

#### Laravel Cloud

```
優勢：
├─ ✅ Queue 自動擴展（處理大量推播）
├─ ✅ 可預測效能（AWS EC2 專屬）
├─ ✅ 企業級穩定性
└─ ⚠️ 成本較高但功能完整

月費範圍：$20-50+
```

#### Zeabur

```
優勢：
├─ ✅ 彈性計費（用多少付多少）
├─ ✅ 手動控制資源
├─ ✅ 升級到 Team 方案增加資源
└─ ⚠️ Queue 擴展需手動管理

月費範圍：$10-30+
```

---

## 🎖️ 綜合評分

### Laravel Cloud

| 評估項目 | 評分 | 說明 |
|---------|------|------|
| Laravel 整合 | ⭐⭐⭐⭐⭐ | 官方平台，完美整合 |
| Queue 管理 | ⭐⭐⭐⭐⭐ | Queue Clusters 自動化 |
| 開發體驗 | ⭐⭐⭐⭐⭐ | Preview Envs + Push-to-Deploy |
| 成本效益 | ⭐⭐⭐ | 小專案較貴（$20/月起） |
| 中文支援 | ⭐ | 無中文文件 |
| 穩定性 | ⭐⭐⭐ | 新服務，待觀察 |
| **總評** | **⭐⭐⭐⭐** | 功能強大但成本較高 |

### Zeabur

| 評估項目 | 評分 | 說明 |
|---------|------|------|
| Laravel 整合 | ⭐⭐⭐⭐ | 良好支援，非專用 |
| Queue 管理 | ⭐⭐⭐ | 需手動設定 |
| 開發體驗 | ⭐⭐⭐⭐ | 一鍵部署，簡單易用 |
| 成本效益 | ⭐⭐⭐⭐⭐ | 極佳（$5/月起） |
| 中文支援 | ⭐⭐⭐⭐⭐ | 完整中文文件和客服 |
| 穩定性 | ⭐⭐⭐⭐ | 已運營一年+ |
| **總評** | **⭐⭐⭐⭐** | 高 CP 值，適合中小專案 |

---

## 🏆 最終建議

### 建議方案：**Zeabur** （當前階段）

#### 選擇理由

1. **💰 成本優勢**
   ```
   Zeabur:        $5/月
   Laravel Cloud: $20/月
   年度節省：     $180
   ```

2. **🇹🇼 在地優勢**
   ```
   ✅ 完整中文文件
   ✅ 中文技術支援
   ✅ 台灣團隊（時區相同）
   ✅ 溝通無障礙
   ```

3. **🚀 快速部署**
   ```
   ✅ 一鍵部署
   ✅ 自動偵測 Laravel
   ✅ 學習曲線低
   ✅ 適合快速迭代
   ```

4. **📊 專案階段匹配**
   ```
   當前：開發/測試階段
   流量：預期較低
   預算：成本敏感

   → Zeabur 完美匹配
   ```

### 使用路徑建議

```
階段一：開發/測試期（當前）
├─ 平台：Zeabur Developer ($5/月)
├─ 目標：快速部署、測試功能
└─ 優勢：低成本、中文支援

階段二：公開測試期（Beta）
├─ 平台：Zeabur Developer/Team
├─ 目標：收集用戶反饋
└─ 優勢：彈性擴展、成本可控

階段三：流量成長期
├─ 評估點：Queue 負載、用戶數量
├─ 選項 A：升級 Zeabur Team
└─ 選項 B：遷移到 Laravel Cloud
    （如需進階 Queue 管理）
```

### 遷移到 Laravel Cloud 的時機

考慮遷移當以下條件出現：

```
📈 業務指標：
├─ 日活躍用戶 > 1000
├─ 每日推播 > 10,000 則
├─ Queue Jobs 積壓嚴重
└─ 需要多環境協作

💡 技術需求：
├─ Queue 自動擴展成為剛需
├─ 需要 Preview Environments
├─ 團隊規模擴大（> 3 人）
└─ 預算允許 $20/月+

⚠️ Zeabur 限制：
├─ 手動 Queue 管理太耗時
├─ 資源上限不足（Team 方案後）
└─ 需要更企業級的解決方案
```

---

## 📋 Zeabur 部署檢查清單

### 前置準備

- [ ] 確認 `composer.json` 指定 PHP 版本
  ```json
  {
    "require": {
      "php": "^8.3"
    }
  }
  ```

- [ ] 環境變數設定
  ```env
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://your-domain.zeabur.app

  DB_CONNECTION=mysql
  DB_HOST=<zeabur-mysql-host>
  DB_DATABASE=holdyourbeer

  QUEUE_CONNECTION=database

  FIREBASE_CREDENTIALS=<base64-encoded-json>
  ```

- [ ] 建立必要服務
  - [ ] MySQL 資料庫
  - [ ] Redis（如使用 Redis Queue）
  - [ ] Queue Worker 容器

### 部署配置

- [ ] Git 連接設定
- [ ] 自動部署觸發條件
- [ ] 環境變數配置
- [ ] 網域設定（如需）

### 驗證項目

- [ ] 網站可正常訪問
- [ ] 資料庫連線正常
- [ ] Google OAuth 登入功能
- [ ] Firebase Auth 登入功能
- [ ] Queue Jobs 正常執行
- [ ] FCM 推播正常發送

---

## 📋 Laravel Cloud 部署檢查清單

### 前置準備

- [ ] 建立 Laravel Cloud 帳號
- [ ] 連接 GitHub/GitLab 倉庫
- [ ] 選擇適當方案（建議 Growth）

### 專案配置

- [ ] 環境變數設定
  ```env
  APP_ENV=production
  APP_DEBUG=false

  # Laravel Cloud 自動管理
  DB_CONNECTION=mysql
  QUEUE_CONNECTION=sync

  FIREBASE_CREDENTIALS=<base64-encoded-json>
  ```

- [ ] Queue Clusters 設定
  - [ ] 確認自動建立
  - [ ] 設定監控閾值

- [ ] Preview Environments
  - [ ] 啟用 PR 自動部署
  - [ ] 設定清理規則

### 驗證項目

- [ ] 主站部署成功
- [ ] Queue Clusters 運作正常
- [ ] Preview Environment 建立成功
- [ ] 所有功能測試通過

---

## 🔗 相關資源

### Laravel Cloud
- 官網：https://cloud.laravel.com
- 定價：https://cloud.laravel.com/pricing
- 文件：https://cloud.laravel.com/docs
- 部落格：https://blog.laravel.com

### Zeabur
- 官網：https://zeabur.com
- 定價：https://zeabur.com/pricing
- 文件（中文）：https://zeabur.com/docs/zh-TW
- Laravel 指南：https://zeabur.com/docs/zh-TW/guides/php/laravel

### HoldYourBeer 相關文件
- `README.md` - 專案概覽
- `CLAUDE.md` - AI 助手指南
- `docs/FIREBASE_AUTH_IMPLEMENTATION.md` - Firebase 整合
- `docs/WEB_GOOGLE_LOGIN.md` - Google OAuth 實作

---

## 📅 更新記錄

| 日期 | 內容 | 作者 |
|------|------|------|
| 2025-11-05 | 初版建立：i18n 修正 + 平台比較 | Claude Code |

---

## 💡 總結

### 關鍵決策點

1. **當前階段**：使用 **Zeabur**
   - 理由：成本低、中文支援、快速上線

2. **成長階段**：評估 **Laravel Cloud**
   - 觸發點：Queue 負載高、需要進階功能

3. **長期策略**：保持彈性
   - 兩個平台都是優秀選擇
   - 根據業務需求調整

### 行動建議

✅ **立即執行**：
1. 在 Zeabur 建立帳號
2. 部署 HoldYourBeer 到 Developer 方案
3. 驗證所有功能正常

📊 **持續監控**：
1. 每月檢查資源使用量
2. 追蹤 Queue Job 效能
3. 評估用戶成長曲線

🔄 **定期評估**：
1. 每季度檢視平台選擇
2. 對比實際成本與效益
3. 根據業務需求調整方案

---

**需要協助設定 Zeabur 部署嗎？或是想深入了解某個平台的特定功能？**
