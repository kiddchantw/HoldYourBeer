# 部署平台比較與專案更新記錄

> 文件建立日期：2025-11-05
> 作者：Claude Code
> 專案：HoldYourBeer

本文件記錄了三個重要主題：
1. Google 登入功能的國際化修正
2. Laravel Cloud vs Zeabur 部署平台完整比較
3. Laravel Nightwatch vs Sentry 監控工具完整比較

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
Database:    PostgreSQL
Auth:        Laravel Sanctum
Mobile:      Flutter
Queue:       Background Jobs
Frontend:    Blade + Tailwind CSS + Livewire
```

### 需求匹配度分析

#### 1. Queue Workers 需求

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

#### 2. 資料庫需求（PostgreSQL）

**Laravel Cloud**：⭐⭐
```
⚠️ Serverless PostgreSQL 成本極高
❌ Storage: $1.50/GB/月（MySQL 的 12-15 倍）
❌ Compute: $0.16/vCPU/小時
⚠️ 20GB 資料 = $30/月（僅儲存）
✅ 自動擴展、休眠功能
```

**Zeabur**：⭐⭐⭐⭐⭐
```
✅ PostgreSQL 視為容器服務
✅ 按實際資源計費（RAM + CPU）
✅ 小型資料庫 ~$5/月（可能含在免費額度）
✅ 成本比 Laravel Cloud 低 90%+
✅ 容易設定
```

**關鍵發現**：PostgreSQL 在 Zeabur 的成本優勢極為明顯！

#### 3. 第三方套件支援

**Laravel Cloud**：⭐⭐⭐⭐⭐
```
✅ 標準 Laravel 環境
✅ 支援所有 PHP 套件
✅ Composer 套件完全相容
```

**Zeabur**：⭐⭐⭐⭐⭐
```
✅ 標準 Laravel 環境
✅ 支援所有 PHP 套件
✅ Composer 套件完全相容
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

## 💾 PostgreSQL vs MySQL 資料庫成本對比

> **重要發現**：HoldYourBeer 專案使用 PostgreSQL，這對成本有重大影響！

### Laravel Cloud 資料庫定價

#### MySQL 定價

```
Storage（儲存空間）：
├─ 美國區域：$0.10/GB/月
└─ 其他區域：$0.12/GB/月

Compute（運算資源）：
├─ Flex：$0.01/小時起（輕量級）
└─ Pro：更高規格（持續高負載）

範例（20GB 資料庫）：
├─ Storage：20GB × $0.10 = $2.00/月
├─ Backup：20GB × $0.10 = $2.00/月
├─ Compute (Flex)：約 $5-10/月
└─ 總計：約 $9-14/月
```

#### PostgreSQL 定價

```
Storage（儲存空間）：
└─ $1.50/GB/月（⚠️ 是 MySQL 的 12-15 倍）

Compute（運算資源）：
└─ $0.16/vCPU/小時（按秒計費）

範例（20GB 資料庫）：
├─ Storage：20GB × $1.50 = $30.00/月
├─ Compute (0.5 vCPU, 24/7)：
│  └─ $0.16 × 0.5 × 24 × 30 = $57.60/月
└─ 總計：約 $87.60/月

使用休眠優化（12hr/天）：
├─ Storage：$30.00/月（不變）
├─ Compute (0.5 vCPU, 12hr)：$28.80/月
└─ 總計：約 $58.80/月
```

### Zeabur 資料庫定價

**重點**：Zeabur 將資料庫視為容器服務，MySQL 和 PostgreSQL 成本相同！

```
PostgreSQL 容器（典型配置）：
├─ 512MB RAM：$2/月
├─ 0.25 vCPU：$3/月
└─ 總計：約 $5/月

Developer 方案優勢：
├─ 月費：$5
├─ 包含 $5 免費額度
└─ 如果資料庫用量 ≤ $5 = 完全免費！

實際成本（App + PostgreSQL）：
├─ Laravel App：~$3-5
├─ PostgreSQL：~$5（含在額度）
├─ Queue Worker：~$2-3
└─ 總計：約 $8-13/月
```

### 成本對比表（HoldYourBeer 使用 PostgreSQL）

| 平台 | 資料庫類型 | Storage (20GB) | Compute | 總成本/月 | 成本差異 |
|------|----------|---------------|---------|----------|---------|
| **Laravel Cloud** | PostgreSQL | $30 | $58 (24/7) | **~$108** | 基準 |
| **Laravel Cloud** | PostgreSQL + 休眠 | $30 | $29 (12hr) | **~$79** | -27% |
| **Laravel Cloud** | MySQL | $2 | $10 (24/7) | **~$32** | -70% |
| **Zeabur** | PostgreSQL | ~$2 | ~$3 | **~$10** | **-91%** ✅ |
| **Zeabur** | MySQL | ~$2 | ~$3 | **~$10** | **-91%** ✅ |

### 關鍵洞察

#### 1. Laravel Cloud 的 PostgreSQL 極為昂貴

```
問題點：
❌ Storage 費用：$1.50/GB（MySQL 的 15 倍）
❌ 20GB = $30/月（MySQL 只要 $2）
❌ 100GB = $150/月（MySQL 只要 $10-12）
❌ 小專案難以負擔

適合場景：
✅ 企業級預算（$100+/月可接受）
✅ 需要 Serverless Postgres 特性
✅ 重視自動擴展和休眠
```

#### 2. Zeabur 對 PostgreSQL 極為友善

```
優勢：
✅ PostgreSQL 視為容器，不另外收儲存費
✅ 與 MySQL 成本完全相同
✅ 按實際資源（RAM + CPU）計費
✅ 小型資料庫可能完全包含在免費額度

PostgreSQL 使用者的福音：
- 不需要為 Postgres 付出額外成本
- 可放心使用 Postgres 特定功能
- 資料量增長不會暴增費用
```

#### 3. 如果使用 PostgreSQL → Zeabur 幾乎是唯一選擇

```
成本對比：
Laravel Cloud (Postgres): $79-108/月
Zeabur (Postgres):        $8-13/月

節省金額：$66-100/月
年度節省：$792-1,200/年

結論：
對於使用 PostgreSQL 的 Laravel 專案，
Zeabur 的成本優勢是壓倒性的！
```

### 遷移到 MySQL 的考量

#### 優勢

```
如果遷移到 MySQL：
✅ Laravel Cloud 成本降低 70%
✅ MySQL 在 Laravel 中完全夠用
✅ 大部分 Laravel 專案用 MySQL

Laravel Cloud (MySQL)：
├─ Growth + MySQL：$32/月
└─ 比 Postgres 便宜 $76/月
```

#### 代價

```
遷移成本：
❌ 需要資料庫遷移
❌ 可能需要調整查詢語法
❌ 喪失 Postgres 特定功能
❌ 開發時間成本

不建議遷移如果：
- 已使用 Postgres 特定功能
- 專案已接近完成
- 團隊熟悉 Postgres
```

### 建議決策樹

```
你的 Laravel 專案用什麼資料庫？

├─ PostgreSQL
│  └─ 部署平台選擇？
│     ├─ 預算 < $20/月
│     │  └─ → Zeabur ⭐⭐⭐⭐⭐（唯一選擇）
│     │
│     ├─ 預算 $50-100/月
│     │  ├─ 考慮遷移到 MySQL？
│     │  │  ├─ 可以 → Laravel Cloud (MySQL)
│     │  │  └─ 不行 → Zeabur
│     │  │
│     │  └─ 或直接用 Zeabur（便宜很多）
│     │
│     └─ 預算 $100+/月
│        └─ Laravel Cloud (Postgres) 可選
│           但 Zeabur 仍更划算
│
└─ MySQL
   └─ 部署平台選擇？
      ├─ 預算 < $30/月 → Zeabur
      ├─ 預算 $30-50/月 → 兩者皆可
      └─ 重視整合 → Laravel Cloud
```

---

## 🎖️ 綜合評分

### Laravel Cloud

| 評估項目 | 評分 | 說明 |
|---------|------|------|
| Laravel 整合 | ⭐⭐⭐⭐⭐ | 官方平台，完美整合 |
| Queue 管理 | ⭐⭐⭐⭐⭐ | Queue Clusters 自動化 |
| 開發體驗 | ⭐⭐⭐⭐⭐ | Preview Envs + Push-to-Deploy |
| 成本效益 (MySQL) | ⭐⭐⭐ | 小專案較貴（$30-40/月） |
| 成本效益 (PostgreSQL) | ⭐ | ⚠️ 極貴（$80-110/月） |
| 中文支援 | ⭐ | 無中文文件 |
| 穩定性 | ⭐⭐⭐ | 新服務，待觀察 |
| **總評** | **⭐⭐⭐⭐** | 功能強大但成本較高 |
| **PostgreSQL 專案** | **⭐⭐** | **不推薦：Postgres 成本過高** |

### Zeabur

| 評估項目 | 評分 | 說明 |
|---------|------|------|
| Laravel 整合 | ⭐⭐⭐⭐ | 良好支援，非專用 |
| Queue 管理 | ⭐⭐⭐ | 需手動設定 |
| 開發體驗 | ⭐⭐⭐⭐ | 一鍵部署，簡單易用 |
| 成本效益 (MySQL) | ⭐⭐⭐⭐⭐ | 極佳（$5-10/月） |
| 成本效益 (PostgreSQL) | ⭐⭐⭐⭐⭐ | ✅ 傑出（$8-13/月，比 Cloud 便宜 90%） |
| 中文支援 | ⭐⭐⭐⭐⭐ | 完整中文文件和客服 |
| 穩定性 | ⭐⭐⭐⭐ | 已運營一年+ |
| **總評** | **⭐⭐⭐⭐** | 高 CP 值，適合中小專案 |
| **PostgreSQL 專案** | **⭐⭐⭐⭐⭐** | **強烈推薦：成本優勢壓倒性** |

---

## 🏆 最終建議

### 建議方案：**Zeabur** （當前階段）

> **⚠️ 關鍵發現**：HoldYourBeer 使用 **PostgreSQL**，這使得 Zeabur 成為幾乎唯一的合理選擇！

#### 選擇理由

1. **💰 成本優勢（PostgreSQL 專案更顯著）**
   ```
   使用 PostgreSQL 的成本對比：

   Zeabur:        $8-13/月
   Laravel Cloud: $79-108/月
   年度節省：     $792-1,140 ⭐⭐⭐

   成本差異：Zeabur 便宜 90%+！

   原因：
   - Laravel Cloud Postgres Storage: $1.50/GB/月
   - Zeabur 視 Postgres 為容器，成本極低
   - 20GB 資料在 Cloud = $30/月，在 Zeabur ≈ $2-3/月
   ```

2. **💾 PostgreSQL 友善度**
   ```
   ✅ Zeabur 對 PostgreSQL 和 MySQL 收費相同
   ✅ 不會因為選擇 Postgres 而付出額外成本
   ✅ 可放心使用 Postgres 特定功能
   ✅ 資料量增長不會暴增費用

   ❌ Laravel Cloud 的 Postgres 極為昂貴
   ```

3. **🇹🇼 在地優勢**
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
   資料庫：PostgreSQL
   流量：預期較低
   預算：成本敏感

   → Zeabur 完美匹配

   特別適合 PostgreSQL 專案：
   - 無需考慮遷移到 MySQL
   - 保留 Postgres 生態系優勢
   - 享受極低成本
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

> **⚠️ PostgreSQL 專案特別注意**：由於 Laravel Cloud 的 PostgreSQL 極為昂貴，**強烈不建議遷移**，除非符合以下條件：

考慮遷移當**所有**以下條件出現：

```
💰 預算條件（PostgreSQL 專案）：
├─ 月預算 > $100（PostgreSQL 在 Cloud 極貴）
├─ 願意接受 10 倍成本（vs Zeabur）
└─ 或願意遷移資料庫到 MySQL（降低 70% 成本）

📈 業務指標：
├─ 日活躍用戶 > 1000
├─ 每日推播 > 10,000 則
├─ Queue Jobs 積壓嚴重
└─ 需要多環境協作

💡 技術需求：
├─ Queue 自動擴展成為剛需
├─ 需要 Preview Environments
├─ 團隊規模擴大（> 3 人）
└─ 重視官方整合勝過成本

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
- [ ] Queue Jobs 正常執行

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

## 第三部分：應用程式監控工具比較

### 監控工具概述

#### Laravel Nightwatch
- **開發團隊**：Laravel 官方（Taylor Otwell）
- **推出時間**：2025 年 6 月正式推出
- **定位**：Laravel 專屬的 APM（Application Performance Monitoring）
- **焦點**：全方位效能監控 + 錯誤追蹤
- **特色**：深度整合 Laravel 內部機制
- **官網**：https://nightwatch.laravel.com

#### Sentry
- **開發團隊**：Sentry.io（獨立公司）
- **成立時間**：2012 年（成熟產品）
- **定位**：跨平台錯誤追蹤工具
- **焦點**：錯誤追蹤為主 + 效能監控為輔
- **特色**：支援多種程式語言和框架
- **官網**：https://sentry.io
- **Laravel 整合**：透過 `sentry/sentry-laravel` 套件

---

## 🔍 核心定位差異

### 監控範圍比較

| 監控項目 | Laravel Nightwatch | Sentry |
|---------|-------------------|--------|
| **錯誤追蹤** | ✅ 完整支援 | ✅ **核心功能** |
| **HTTP 請求** | ✅ **詳細追蹤** | ⚠️ 基本支援 |
| **資料庫查詢** | ✅ **SQL 效能分析** | ⚠️ 有限支援 |
| **Queue Jobs** | ✅ **深度監控** | ⚠️ 基本支援 |
| **排程任務** | ✅ 完整追蹤 | ⚠️ 基本支援 |
| **Laravel Events** | ✅ 自動追蹤 | ❌ 不支援 |
| **通知/郵件** | ✅ 完整追蹤 | ❌ 不支援 |
| **快取操作** | ✅ 監控 | ❌ 不支援 |
| **用戶行為追蹤** | ✅ 個別追蹤 | ⚠️ 部分支援 |
| **效能分析 (APM)** | ✅ **完整免費** | ⚠️ 付費功能 |
| **Session Replays** | ⚠️ 未知 | ✅ 50 replays（免費） |

### 功能定位

```
Laravel Nightwatch
├─ 定位：完整的 Laravel 應用程式健康檢查
├─ 範圍：錯誤 + 效能 + Laravel 所有元件
└─ 方式：自動追蹤，零配置

Sentry
├─ 定位：專注於錯誤捕捉和堆疊追蹤
├─ 範圍：錯誤為主，效能為輔
└─ 方式：需手動配置追蹤點
```

---

## 💰 定價比較（2025）

### Laravel Nightwatch 定價

| 方案 | 月費 | 事件數量 | 包含功能 |
|------|------|---------|---------|
| **Free** | **$0** | 免費額度（2025 年提高 50%） | 所有監控功能 |
| **付費方案** | 依用量計費 | 超額付費（費率已降低） | 完整功能無限制 |

**事件定義**（所有 Laravel 活動都計入）：
```
✅ 1 個 HTTP 請求 = 1 個事件
✅ 1 個 Queue Job = 1 個事件
✅ 1 個排程任務 = 1 個事件
✅ 1 個通知 = 1 個事件
✅ 1 個例外錯誤 = 1 個事件
✅ 1 個資料庫查詢 = 1 個事件（視設定）

→ 提供完整應用程式視圖
```

**效能影響**：
- 每個請求增加 < 3ms 延遲
- Agent 獨立運行，不影響主程序
- 已處理超過 10 億+日事件量

### Sentry 定價

| 方案 | 月費 | 錯誤數量 | 主要限制 |
|------|------|---------|---------|
| **Developer** | **$0** | 5,000 錯誤/月 | 1 用戶、50 Session Replays、5GB logs |
| **Team** | **$26/月** | 預付額度 + 彈性用量 | 效能監控需額外配置 |
| **Business** | **$80/月** | 預付額度 + 進階功能 | 跨專案分析 |
| **Enterprise** | 客製報價 | 無限制 | 完整平台功能 |

**事件定義**（僅錯誤計入）：
```
✅ 1 個錯誤/例外 = 1 個事件

⚠️ 效能監控（Performance Monitoring）：
   → Team 方案以上
   → 需額外配置
   → 需手動設定追蹤點（Transactions）

✅ Session Replays：
   → 免費方案：50 replays/月
   → 付費方案：依方案而定
```

---

## 🎯 針對 HoldYourBeer 專案分析

### 專案監控需求

```php
// HoldYourBeer 需要監控的場景

1. API 端點效能
   POST /api/v1/beers
   GET /api/v1/beers
   → 回應時間、成功率、錯誤類型

2. Google OAuth 流程
   GET /auth/google/callback
   → 轉換率、失敗原因

3. 背景 Queue Jobs
   dispatch(new ProcessBeerData($user, $beer))
   → 執行狀況、失敗率、積壓情況

4. 資料庫查詢效能
   Beer::with('brand')->where('user_id', $userId)->get()
   → 查詢時間、N+1 問題

5. 例外錯誤
   AuthenticationException
   ValidationException
   → 錯誤堆疊、發生頻率
```

### Nightwatch 監控能力

```
HoldYourBeer + Nightwatch 可自動監控：

✅ 所有 API 端點（回應時間、狀態碼、錯誤率）
✅ Queue Jobs 執行（背景任務成功率、執行時間）
✅ 資料庫查詢效能（識別慢查詢、N+1 問題）
✅ 認證錯誤（自動捕捉例外）
✅ Google OAuth 流程（追蹤整個認證流程）
✅ 用戶行為分析（追蹤個別用戶操作）

零配置設定：
composer require laravel/nightwatch
php artisan nightwatch:install

→ 立即獲得完整應用程式洞察
```

### Sentry 監控能力

```
HoldYourBeer + Sentry 可監控：

✅ 認證錯誤（例外捕捉 + 堆疊追蹤）
✅ Google OAuth 失敗（錯誤詳情）
✅ 資料庫錯誤（SQL 例外）
⚠️ Queue Jobs 失敗（需在 Job 中手動 report）
⚠️ API 效能（需付費 + 手動設定 Transactions）
❌ 資料庫查詢效能（不會自動追蹤）
❌ Queue Jobs 效能監控（僅錯誤，無效能數據）

需要配置：
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=your-dsn

手動追蹤效能（需付費方案）：
$transaction = \Sentry\startTransaction(...);
// 手動設定追蹤點
$transaction->finish();

→ 主要用於錯誤捕捉
```

---

## 🔬 深度監控範例

### Laravel Nightwatch - 自動深度追蹤

```php
// 以下程式碼 Nightwatch 自動監控，無需額外設定

// 1. Eloquent 查詢效能
User::where('email', $email)->first();
→ 自動記錄：查詢時間、SQL 語句、是否 N+1

// 2. Queue Job 完整生命週期
dispatch(new ProcessBeerData($user));
→ 自動記錄：排隊時間、執行時間、失敗原因、重試次數

// 3. Event 系統觸發
event(new UserRegistered($user));
→ 自動記錄：事件觸發、監聽器執行時間

// 4. HTTP 請求完整追蹤
Route::post('/api/beers', [BeerController::class, 'store']);
→ 自動記錄：中介層耗時、控制器耗時、回應時間、記憶體使用

// 5. 排程任務
$schedule->command('beer:process-statistics')->daily();
→ 自動記錄：執行時間、成功/失敗、輸出日誌
```

### Sentry - 錯誤為中心

```php
// 以下是 Sentry 的典型使用方式

// 1. 自動捕捉例外（主要功能）
try {
    User::findOrFail($id);
} catch (ModelNotFoundException $e) {
    // Sentry 自動捕捉並發送
    // 包含：完整堆疊、請求上下文、用戶資訊
}

// 2. 手動記錄錯誤
\Sentry\captureException($exception);
\Sentry\captureMessage('Something went wrong', 'warning');

// 3. 添加上下文資訊
\Sentry\configureScope(function ($scope) {
    $scope->setUser(['id' => auth()->id()]);
    $scope->setTag('user_email', $user->email);
});

// 4. 效能追蹤（需付費 Performance Monitoring）
$transaction = \Sentry\startTransaction([
    'name' => 'POST /api/beers',
    'op' => 'http.server',
]);

// 需手動設定每個追蹤點
$span = $transaction->startChild(['op' => 'db.query']);
// 執行資料庫查詢
$span->finish();

$transaction->finish();

// 5. Queue Jobs 需手動處理
class SendFcmNotification implements ShouldQueue
{
    public function failed(\Throwable $exception)
    {
        // 需手動報告失敗
        \Sentry\captureException($exception);
    }
}
```

---

## 📊 平台整合比較

### 與 Laravel Cloud 整合

| 功能 | Nightwatch | Sentry |
|------|-----------|--------|
| **官方整合文件** | ✅ https://nightwatch.laravel.com/docs/guides/cloud | ✅ 透過 Laravel 合作夥伴 |
| **設定複雜度** | ⭐⭐⭐⭐⭐ 零配置 | ⭐⭐⭐ 需配置 DSN 和環境變數 |
| **Agent 部署** | 自動加入 Background Process | 需手動配置 |
| **Laravel 深度** | ⭐⭐⭐⭐⭐ 原生整合 | ⭐⭐⭐ 透過套件整合 |

### 與 Zeabur 整合

| 功能 | Nightwatch | Sentry |
|------|-----------|--------|
| **支援程度** | ✅ 完全支援（平台無關） | ✅ 完全支援 |
| **設定方式** | 環境變數 + Composer | 環境變數 + Composer |
| **Agent 運行** | 需設定 Background Process | 無需額外 Process |
| **複雜度** | ⭐⭐⭐ 需手動設定 Agent | ⭐⭐⭐⭐ 安裝即用 |

**Zeabur + Nightwatch 設定**：
```bash
# 1. 安裝 Nightwatch
composer require laravel/nightwatch

# 2. 在 Zeabur 環境變數加入
NIGHTWATCH_API_TOKEN=your-token
NIGHTWATCH_PROJECT_ID=your-project-id

# 3. 設定 Background Process（需手動配置）
# 在 Zeabur 控制台新增 Worker 服務
# Command: php artisan nightwatch:work
```

**Zeabur + Sentry 設定**：
```bash
# 1. 安裝 Sentry
composer require sentry/sentry-laravel

# 2. 在 Zeabur 環境變數加入
SENTRY_LARAVEL_DSN=your-dsn

# 3. 發布配置（可選）
php artisan sentry:publish

# 完成！無需額外 Process
```

---

## 🏆 選擇建議

### 選擇 Nightwatch 的情境

```
✅ 想要全面了解應用程式效能
✅ 需要監控 Queue Jobs 效能（HoldYourBeer 有背景任務！）
✅ 想自動發現慢查詢、N+1 問題
✅ 希望零配置、開箱即用
✅ 使用 Laravel Cloud（原生整合）
✅ 預算有限但需要完整監控
✅ 想追蹤用戶行為流程

適合角色：
- 開發者（優化效能）
- DevOps（監控應用健康）
- 產品經理（了解用戶行為）
```

### 選擇 Sentry 的情境

```
✅ 主要目的是捕捉和追蹤錯誤
✅ 需要詳細的錯誤堆疊和上下文
✅ 需要跨平台支援（Flutter + Web + Backend）
✅ 已經熟悉 Sentry 生態系
✅ 有多語言專案（不只 Laravel）
✅ 重視成熟度和穩定性（2012 年至今）
✅ 需要 Session Replays 功能

適合角色：
- 開發者（除錯）
- QA（追蹤 Bug）
- 客服（用戶問題排查）
```

### 兩者並用？

```
並用場景：
├─ Nightwatch：日常效能監控和優化
└─ Sentry：深度錯誤追蹤和 Session Replays

現實評估：
├─ Nightwatch 已包含錯誤追蹤功能
├─ 兩者有功能重疊
├─ 增加維護複雜度
└─ 多數情況選一個就夠

成本考量：
├─ Nightwatch Free + Sentry Free = 兩套系統維護
├─ 建議：選擇最符合主要需求的一個
└─ 除非有特殊跨平台需求
```

---

## 🎖️ 綜合評分

### Laravel Nightwatch

| 評估項目 | 評分 | 說明 |
|---------|------|------|
| Laravel 整合 | ⭐⭐⭐⭐⭐ | 原生深度整合 |
| 錯誤追蹤 | ⭐⭐⭐⭐⭐ | 自動捕捉所有例外 |
| 效能監控 | ⭐⭐⭐⭐⭐ | 完整 APM，免費 |
| Queue 監控 | ⭐⭐⭐⭐⭐ | 自動追蹤，零配置 |
| 資料庫分析 | ⭐⭐⭐⭐⭐ | SQL 效能、N+1 偵測 |
| 學習曲線 | ⭐⭐⭐⭐⭐ | 幾乎零學習成本 |
| 跨平台支援 | ⭐⭐ | Laravel 專用 |
| 成熟度 | ⭐⭐⭐ | 新產品（2025） |
| 免費方案 | ⭐⭐⭐⭐⭐ | 功能完整 |
| **總評** | **⭐⭐⭐⭐⭐** | Laravel 專案最佳選擇 |

### Sentry

| 評估項目 | 評分 | 說明 |
|---------|------|------|
| Laravel 整合 | ⭐⭐⭐ | 透過套件整合 |
| 錯誤追蹤 | ⭐⭐⭐⭐⭐ | 業界標準 |
| 效能監控 | ⭐⭐⭐ | 付費功能，需手動設定 |
| Queue 監控 | ⭐⭐ | 僅錯誤，無效能數據 |
| 資料庫分析 | ⭐ | 有限支援 |
| 學習曲線 | ⭐⭐⭐ | 需學習配置 |
| 跨平台支援 | ⭐⭐⭐⭐⭐ | 支援多種語言 |
| 成熟度 | ⭐⭐⭐⭐⭐ | 成熟產品（2012-） |
| 免費方案 | ⭐⭐⭐ | 錯誤追蹤基本功能 |
| **總評** | **⭐⭐⭐⭐** | 跨平台錯誤追蹤首選 |

---

## 💡 HoldYourBeer 專案最終建議

### 推薦方案：**Laravel Nightwatch**

#### 核心理由

1. **Queue Jobs 監控至關重要**
   ```php
   // HoldYourBeer 的核心功能
   dispatch(new ProcessBeerData($user, $beer));

   Nightwatch 優勢：
   ✅ 自動追蹤執行狀態
   ✅ 監控積壓情況（避免任務延遲）
   ✅ 失敗率統計
   ✅ 效能分析（找出瓶頸）

   Sentry 限制：
   ⚠️ 需在每個 Job 手動加 report()
   ⚠️ 只能看到失敗，看不到效能
   ⚠️ 無法監控 Queue 積壓
   ```

2. **API 效能優化需求**
   ```php
   // Flutter App 頻繁呼叫的 API
   GET /api/v1/beers
   POST /api/v1/beers/{id}/count_actions
   GET /api/v1/user

   Nightwatch：
   ✅ 自動追蹤所有端點
   ✅ 回應時間分析
   ✅ 資料庫查詢優化建議
   ✅ 免費

   Sentry：
   ⚠️ 需付費 Performance Monitoring
   ⚠️ 需手動設定 Transactions
   ⚠️ 配置複雜
   ```

3. **成本效益**
   ```
   Nightwatch Free:
   ✅ 完整 APM 功能
   ✅ 錯誤追蹤
   ✅ Queue 監控
   ✅ 資料庫分析
   ✅ 用戶行為追蹤
   → 全功能，零成本

   Sentry Free:
   ✅ 5,000 錯誤/月
   ⚠️ 無效能監控
   ⚠️ 無 Queue 深度監控
   → 基本錯誤追蹤
   ```

4. **Laravel 原生整合**
   ```
   Nightwatch:
   $ composer require laravel/nightwatch
   $ php artisan nightwatch:install
   → 完成！自動監控所有內容

   Sentry:
   $ composer require sentry/sentry-laravel
   $ php artisan sentry:publish --dsn=xxx
   $ 手動配置錯誤處理
   $ 手動設定效能追蹤（如需）
   → 需要更多配置
   ```

### 部署組合建議

#### 方案一：成本最優（推薦）

```
部署平台：Zeabur Developer ($5/月)
監控工具：Laravel Nightwatch (Free)

總成本：$5/月

優勢：
✅ 極低成本
✅ 完整監控功能
✅ 中文支援（Zeabur）
✅ Queue 監控（Nightwatch）

適合：開發/測試階段、小型專案
```

#### 方案二：最佳整合

```
部署平台：Laravel Cloud Growth ($20/月)
監控工具：Laravel Nightwatch (Free)

總成本：$20/月

優勢：
✅ 原生深度整合
✅ Queue Clusters + Nightwatch 監控
✅ Preview Environments
✅ 零配置

適合：成長期、重視整合度
```

#### 方案三：跨平台需求

```
部署平台：Zeabur Developer ($5/月)
監控工具：
├─ Laravel Nightwatch (Free) - Backend
└─ Sentry Developer (Free) - Flutter App

總成本：$5/月

優勢：
✅ Backend 完整監控（Nightwatch）
✅ Flutter 錯誤追蹤（Sentry）
✅ 跨平台覆蓋

適合：有 Flutter 錯誤追蹤需求
```

### 實施步驟

```bash
# Step 1: 部署到 Zeabur
# （參考前面章節的 Zeabur 部署檢查清單）

# Step 2: 安裝 Nightwatch
composer require laravel/nightwatch

# Step 3: 設定環境變數（在 Zeabur 控制台）
NIGHTWATCH_API_TOKEN=your-token
NIGHTWATCH_PROJECT_ID=your-project-id

# Step 4: 安裝並啟動
php artisan nightwatch:install

# Step 5: 設定 Background Process（在 Zeabur）
# 新增 Worker 服務
# Command: php artisan nightwatch:work

# Step 6: 驗證
# 訪問 https://nightwatch.laravel.com 查看監控數據
```

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

### Laravel Nightwatch
- 官網：https://nightwatch.laravel.com
- 定價：https://nightwatch.laravel.com/pricing
- 文件：https://nightwatch.laravel.com/docs
- Laravel Cloud 整合：https://nightwatch.laravel.com/docs/guides/cloud
- vs Pulse 比較：https://nightwatch.laravel.com/nightwatch-vs-pulse

### Sentry
- 官網：https://sentry.io
- 定價：https://sentry.io/pricing
- Laravel 文件：https://docs.sentry.io/platforms/php/guides/laravel/
- GitHub：https://github.com/getsentry/sentry-laravel

### HoldYourBeer 相關文件
- `README.md` - 專案概覽
- `CLAUDE.md` - AI 助手指南
- `docs/WEB_GOOGLE_LOGIN.md` - Google OAuth 實作

---

## 📅 更新記錄

| 日期 | 內容 | 作者 |
|------|------|------|
| 2025-11-05 | 初版建立：i18n 修正 + 平台比較 | Claude Code |
| 2025-11-05 | 新增第三部分：監控工具比較（Nightwatch vs Sentry） | Claude Code |
| 2025-11-06 | 新增 PostgreSQL vs MySQL 成本對比，更新專案使用 PostgreSQL | Claude Code |

---

## 💡 總結

### 關鍵決策點

#### 部署平台選擇（PostgreSQL 專案）

1. **當前階段**：使用 **Zeabur** ⭐⭐⭐⭐⭐
   - 理由：PostgreSQL 成本極低、中文支援、快速上線
   - 成本：$8-13/月（包含 PostgreSQL）
   - **關鍵優勢**：Postgres 比 Laravel Cloud 便宜 90%

2. **成長階段**：繼續使用 **Zeabur** 或升級 Team 方案
   - 觸發點：流量增長、需要更多資源
   - 成本：$20-50/月
   - **不建議** Laravel Cloud（PostgreSQL 極貴：$80-110/月）

3. **長期策略**：除非預算充足，否則保持 Zeabur
   - PostgreSQL 專案：Zeabur 幾乎是唯一合理選擇
   - 或考慮遷移到 MySQL 後使用 Laravel Cloud
   - 根據業務需求和預算調整

#### 監控工具選擇

1. **當前階段**：使用 **Laravel Nightwatch**
   - 理由：完整免費、Queue 監控、零配置
   - 成本：$0（免費方案）

2. **跨平台需求**：考慮加入 **Sentry**
   - 觸發點：需要 Flutter App 錯誤追蹤
   - 成本：$0（免費方案）或 $26/月（Team）

3. **最佳組合**：Nightwatch + Sentry 並用
   - Backend：Nightwatch（效能 + 錯誤）
   - Mobile：Sentry（Flutter 錯誤追蹤）

### 行動建議

#### 階段一：部署設定

✅ **立即執行**：
1. 在 Zeabur 建立帳號並部署專案（Developer $5/月）
2. 設定環境變數和必要服務（**PostgreSQL**、Redis）
3. 驗證所有功能正常（登入、API、Queue）
4. 確認 PostgreSQL 資料庫正常運作

#### 階段二：監控設定

✅ **安裝 Nightwatch**：
1. 安裝套件：`composer require laravel/nightwatch`
2. 設定環境變數：`NIGHTWATCH_API_TOKEN`、`NIGHTWATCH_PROJECT_ID`
3. 執行安裝：`php artisan nightwatch:install`
4. 在 Zeabur 設定 Background Process

⚠️ **可選：安裝 Sentry**（如需 Flutter 錯誤追蹤）：
1. 安裝套件：`composer require sentry/sentry-laravel`
2. 設定 DSN：`SENTRY_LARAVEL_DSN`
3. Flutter App 整合 Sentry SDK

#### 階段三：持續優化

📊 **持續監控**：
1. 每週檢查 Nightwatch 儀表板
2. 追蹤 Queue Job 效能和失敗率
3. 監控 API 回應時間
4. 識別並優化慢查詢

🔄 **定期評估**：
1. 每月檢查資源使用量和成本
2. 每季度檢視平台選擇
3. 評估用戶成長趨勢
4. 根據業務需求調整方案

### 推薦配置（HoldYourBeer - PostgreSQL 專案）

```
當前最佳組合（針對 PostgreSQL 專案優化）：

部署平台：
└─ Zeabur Developer ($5/月)
   ├─ Laravel App 容器：~$3-5
   ├─ PostgreSQL 容器：~$5（含在免費額度）
   └─ Queue Worker：~$2-3

資料庫：
└─ PostgreSQL（與 MySQL 成本相同！）
   ├─ 無額外儲存費用
   ├─ 按容器資源計費
   └─ 比 Laravel Cloud 便宜 90%

監控工具：
├─ Laravel Nightwatch (Free) - Backend 完整監控
└─ (可選) Sentry (Free) - Flutter 錯誤追蹤

實際總成本：$8-13/月
vs Laravel Cloud：$79-108/月
年度節省：$792-1,140！

總價值：完整部署 + PostgreSQL + 深度監控
```

---

**本文件涵蓋四大主題：i18n 修正、部署平台比較、PostgreSQL vs MySQL 成本對比、監控工具比較，為 HoldYourBeer 專案（PostgreSQL）提供完整的技術選型指南。**
