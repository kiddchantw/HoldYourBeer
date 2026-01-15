# Session: Slack Bot 整合方案

**Date**: 2026-01-12
**Status**: 🔄 In Progress
**Duration**: 預估 8-13 小時
**Issue**: N/A
**Contributors**: @kiddchan, Claude AI

**Tags**: #architecture, #infrastructure, #decisions

**Categories**: Notifications, Monitoring, DevOps

---

## 📋 Overview

### Goal
為 HoldYourBeer 專案整合 Slack Bot，實現重要事件通知（錯誤日誌、新用戶註冊、Feedback 提交、安全警報）。

### Related Documents
- **API 日誌格式**: `storage/logs/api-YYYY-MM-DD.log`
- **現有 Notification**: `app/Notifications/VerifyEmailNotification.php`
- **現有 Observer**: `app/Observers/BrandObserver.php`

### Commits
- 待實作

### 架構說明

**採用方案**: Laravel 官方 Slack Notification Channel

**核心元件**:
1. **laravel/slack-notification-channel** - 官方套件
2. **SlackMessage Builder** - 使用 Block Kit API 建構訊息
3. **Laravel Notification 系統** - 自動支援 Queue、Retry、Events
4. **Model Observers** - 監聽 Model 事件觸發通知

**優勢**:
- ✅ 完整的 Block Kit 支援
- ✅ 自動 Queue 處理（`implements ShouldQueue`）
- ✅ 內建 Retry 機制
- ✅ 易於測試（`Notification::fake()`）
- ✅ 符合 Laravel 最佳實踐

### 📋 快速參考

#### Phase 1: 基礎設施指令
```bash
# 1. 安裝官方套件
composer require laravel/slack-notification-channel

# 2. 移除舊檔案
rm app/Services/SlackNotificationService.php
rm app/Enums/SlackChannel.php
rm tests/Unit/Services/SlackNotificationServiceTest.php
rm app/Notifications/Slack/UserRegisteredNotification.php
rm app/Observers/UserObserver.php
```

#### Phase 2: 程式碼範例

**Notification 範例**:
```php
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;

public function toSlack($notifiable): SlackMessage
{
    return (new SlackMessage)
        ->headerBlock('🎉 新用戶註冊')
        ->sectionBlock(function (SectionBlock $block) {
            $block->field("*用戶名稱:*\n{$this->user->name}")->markdown();
            $block->field("*註冊方式:*\n📧 Email")->markdown();
        });
}
```

**Observer 範例**:
```php
public function created(User $user): void
{
    $user->notify(new UserRegisteredNotification($user));
}
```

**User Model 範例**:
```php
public function routeNotificationForSlack(): string
{
    return '#holdyourbeer-users';
}
```

**測試範例**:
```php
Notification::fake();

$user = User::factory()->create();

Notification::assertSentTo(
    $user,
    UserRegisteredNotification::class,
    function ($notification, $channels) use ($user) {
        return in_array('slack', $channels);
    }
);
```

#### 🔑 架構對比

| 項目 | 舊架構 (自訂) | 新架構 (官方) |
|------|--------------|--------------|
| 發送方式 | `SlackNotificationService::send()` | `$user->notify()` |
| 訊息格式 | 手動建構 array | `SlackMessage` Builder |
| Queue | 手動實作 | `implements ShouldQueue` |
| 測試 | Mock Service | `Notification::fake()` |
| 頻道路由 | Enum + Config | `routeNotificationForSlack()` |

---

## 🎯 Context

### Problem
目前系統缺乏即時監控與通知機制：
- ERROR 日誌需要手動查看 `storage/logs/`
- 新用戶註冊無法即時追蹤
- Feedback/Bug Report 提交後管理員無法第一時間知道
- 安全事件（多次登入失敗）沒有警報機制

### User Story
> As a 系統管理員，I want to 在 Slack 收到重要事件通知 so that 我能即時掌握系統狀態並快速回應問題。

### Current State
- `config/services.php` 已有基本 Slack 配置（bot_user_oauth_token, channel）
- `config/logging.php` 已有 Slack 日誌頻道（未啟用）
- User Model 已使用 `Notifiable` trait
- 已有 `BrandObserver` 觀察者模式範例
- 已有 Email Notification 類別可參考

**Gap**: 缺少 Slack Notification 類別、Observer 監聽器、安全事件處理

---

## 💡 Planning

### Approach Analysis

#### Option A: Laravel Notification + Observer [✅ CHOSEN]
使用 Laravel 內建 Notification 系統搭配 Observer 模式監聽 Model 事件。

**Pros**:
- 與現有架構完美整合
- 支援 Queue 異步處理
- 易於測試（`Notification::fake()`）
- 可重用現有 Notification 模式

**Cons**:
- 需要建立多個檔案
- Observer 需手動註冊

#### Option B: Slack SDK 直接整合 [❌ REJECTED]
使用官方 `slack-php/slack-api-bundle` 或類似套件。

**Pros**:
- 功能完整，支援互動式訊息
- 可存取完整 Slack API

**Cons**:
- 增加外部依賴
- 架構複雜度較高
- 與 Laravel 整合需額外配置

#### Option C: 純 Webhook [❌ REJECTED]
直接在各處使用 HTTP Client 發送 Webhook。

**Pros**:
- 最簡單直接

**Cons**:
- 程式碼重複
- 不易測試
- 無法使用 Queue

**Decision Rationale**: Option A 符合 Laravel 最佳實踐，與專案現有架構一致，且提供完整的 Queue 和測試支援。

**🔄 架構調整 (2026-01-12 14:56)**:
經過評估 [Laravel 官方文件](https://laravel.com/docs/12.x/notifications#slack-notifications)，決定調整為使用官方 `laravel/slack-notification-channel` 套件，而非自訂 `SlackNotificationService`。

**調整原因**:
1. ✅ 官方套件提供完整的 Block Kit Builder API
2. ✅ 自動整合 Queue、Retry、Events 機制
3. ✅ 更好的測試支援 (`Notification::fake()`)
4. ✅ 支援 On-Demand Notifications
5. ✅ 長期維護性更好，跟隨 Laravel 標準

**架構變更**:
- **移除**: 自訂 `SlackNotificationService` 和 `SlackChannel` Enum
- **新增**: 使用 `Illuminate\Notifications\Slack\SlackMessage` Builder
- **調整**: Observer 使用 `$user->notify()` 而非直接呼叫 Service

### Design Decisions

#### D1: 通知方式
- **Options**: Laravel Notification, Slack SDK, Raw Webhook
- **Chosen**: Laravel Notification
- **Reason**: 與現有架構一致、易測試、支援 Queue
- **Trade-offs**: 需建立較多檔案，但換來更好的維護性

#### D2: 錯誤日誌發送
- **Options**: 自訂 Handler, Monolog Slack Channel
- **Chosen**: Monolog Slack Channel（Laravel 內建）
- **Reason**: 零配置，只需設定環境變數
- **Trade-offs**: 格式固定，但足夠使用

#### D3: 異步處理
- **Options**: 同步發送, Queue 異步
- **Chosen**: Queue 異步（ShouldQueue）
- **Reason**: 避免影響 API 回應時間
- **Trade-offs**: 需確保 Queue Worker 運行

---

## ✅ Implementation Checklist (TDD Workflow)

> **開發模式**: Test-Driven Development (Red → Green → Refactor)
>
> 每個功能都遵循：
> 1. 🔴 **Red**: 先撰寫失敗的測試
> 2. 🟢 **Green**: 實作最少程式碼讓測試通過
> 3. 🔵 **Refactor**: 重構優化，確保測試仍通過

---

### Phase 1: 基礎設施（官方套件） ✅ 完成

#### 1.1 環境配置 [🔄 In Progress]
- [x] 建立 Slack App 並取得 Bot User OAuth Token ✅ 2026-01-12
- [x] 更新 `.env.example` - 新增 Slack 配置範本 ✅ 2026-01-12
- [ ] 更新 `.env` - 新增實際的 `SLACK_BOT_USER_OAUTH_TOKEN` 值
  > **待辦**: 需要從 Slack App 取得實際的 Bot User OAuth Token 並設定到 `.env`
- [x] 更新 `config/services.php` - 配置 Slack notifications ✅ 2026-01-12 (已存在)

#### 1.2 安裝官方套件 ✅ 完成
- [x] 安裝 `laravel/slack-notification-channel` ✅ 2026-01-12
  - 版本: v3.7.0
  - 狀態: 安裝成功
- [x] 驗證套件安裝成功 ✅ 2026-01-12
  ```bash
  composer show laravel/slack-notification-channel
  ```

#### 1.3 移除舊的自訂實作 ✅ 完成
- [x] 刪除 `app/Services/SlackNotificationService.php` ✅ 2026-01-12
- [x] 刪除 `app/Enums/SlackChannel.php` ✅ 2026-01-12
- [x] 刪除 `tests/Unit/Services/SlackNotificationServiceTest.php` ✅ 2026-01-12
- [x] 刪除 `app/Notifications/Slack/UserRegisteredNotification.php` (舊版) ✅ 2026-01-12
- [x] 刪除 `app/Observers/UserObserver.php` (舊版) ✅ 2026-01-12
- [x] 清理空目錄 (`app/Enums`, `tests/Unit/Services`) ✅ 2026-01-12

---

### Phase 2: 用戶註冊通知 (TDD - 官方架構) ✅ 完成

#### 2.1 UserRegisteredNotification (TDD) ✅ 完成
- [x] 🔴 **Red**: 建立 `tests/Feature/Notifications/Slack/UserRegisteredNotificationTest.php` ✅ 2026-01-12
  - [x] 測試：新用戶建立時觸發通知 (`Notification::fake()`)
  - [x] 測試：通知使用正確的 Slack channel
  - [x] 測試：通知實作 ShouldQueue 介面
  - [x] 測試：via() 方法回傳 slack channel
- [x] 🟢 **Green**: 實作功能 ✅ 2026-01-12
  - [x] 建立 `app/Notifications/Slack/UserRegisteredNotification.php`
    - [x] 實作 `via()` 方法回傳 `['slack']`
    - [x] 實作 `toSlack()` 使用 `SlackMessage` Builder
    - [x] 使用 `headerBlock()`, `sectionBlock()`, `field()` 等方法
    - [x] 實作 `implements ShouldQueue`
  - [x] 建立 `app/Observers/UserObserver.php`
    - [x] 在 `created()` 方法呼叫 `$user->notify(new UserRegisteredNotification($user))`
  - [x] 在 `User` Model 新增 `routeNotificationForSlack()` 方法
    - [x] 回傳 Slack channel `#holdyourbeer-users`
  - [x] 在 `AppServiceProvider` 註冊 Observer
  - [x] **增強**: 在通知標題加入環境名稱標籤 `[DEV]`, `[PRD]` (依據 `APP_ENV`)
- [x] 🔵 **Refactor**: 優化 ✅ 2026-01-12
  - [x] 優化 `getUserStatistics()` 為單一查詢（效能提升）
  - [x] 抽取 `getFormattedTimestamp()` 方法
  - [x] 新增完整 PHPDoc 註解（Notification 和 Observer）
  - [x] 改善程式碼可讀性

**測試結果**: ✅ 4 passed (4 assertions) - 0.83s

---

### Phase 3: 錯誤日誌通知（Monolog Slack Handler）✅ 完成

#### 3.1 配置 Monolog Slack Channel ✅ 完成
- [x] 更新 `config/logging.php`
  - [x] 新增 `slack` channel 使用 Monolog SlackWebhookHandler
  - [x] 設定只記錄 ERROR 和 CRITICAL 級別
  - [x] 配置 Webhook URL 從環境變數讀取
  - [x] 設定 `username` 為 `HoldYourBeer Bot`
  - [x] 設定 `emoji` 為 `:rotating_light:`
- [x] 更新 `stack` channel 包含 `slack` (於 `.env.example` 預設啟用)
- [x] 新增配置測試 `tests/Feature/LoggingConfigurationTest.php`
- [ ] 測試錯誤日誌發送 (需手動驗證，因為需要真實 Webhook URL)
  ```bash
  docker-compose -f ../../laradock/docker-compose.yml exec -T -w /var/www/beer/HoldYourBeer workspace php artisan tinker --execute="Log::error('Test error message', ['context' => 'test']);"
  ```

---

### Phase 4: 整合測試 & 驗收 ✅ 完成

#### 4.1 整合驗證 ✅ 完成
- [x] 執行完整測試套件 (Notification + Logging)
  ```bash
  docker-compose -f ../../laradock/docker-compose.yml exec -T -w /var/www/beer/HoldYourBeer workspace php artisan test --filter="UserRegisteredNotificationTest|LoggingConfigurationTest"
  ```
  > 測試結果: ✅ 6 passed (9 assertions)

#### 4.2 手動驗收測試 [⏳ Pending User Action]
- [ ] 在真實 Slack workspace 測試各通知
- [ ] 確認訊息格式正確
- [ ] 確認 Queue 正常運作
- [ ] 確認不同頻道路由正確
- [ ] 確認 Rate Limiting 生效

#### 4.3 文件更新 ✅ 完成
- [x] 更新 README 說明 Slack 整合 (於本文件完整記錄)
- [x] 記錄環境變數需求
- [x] 更新 `.env.example` 確保完整

---

## 📐 Technical Design

### 架構圖

```

┌─────────────────────────────────────────────────────────────────┐
│                        事件觸發層                                │
├─────────────────────────────────────────────────────────────────┤
│  Model Event (Observer)  │  Application Event  │  Log Channel   │
│  - User created          │  - SecurityAlert    │  - ERROR logs  │
│  - Feedback created      │                     │  - CRITICAL    │
└─────────────┬─────────────┴──────────┬──────────┴───────┬───────┘
              │                        │                  │
              ▼                        ▼                  ▼
┌─────────────────────────────────────────────────────────────────┐
│                      事件處理層 (Listeners/Observers)            │
├─────────────────────────────────────────────────────────────────┤
│  UserObserver            │  SecurityAlertListener               │
│  FeedbackObserver        │  SlackLogHandler (Monolog)           │
└─────────────────────────────────────┬───────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Queue (異步處理)                             │
├─────────────────────────────────────────────────────────────────┤
│  ShouldQueue   │  Retries: 3   │  Backoff: exponential          │
└─────────────────────────────────┬───────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Slack 頻道                                    │
├─────────────────────────────────────────────────────────────────┤
│  #users        │  #feedback    │  #errors      │  #security     │
│  新用戶註冊     │  用戶回饋      │  錯誤日誌      │  安全警報      │
└─────────────────────────────────────────────────────────────────┘
```

### 通知場景優先級

| 優先級 | 場景 | Slack 頻道 | 格式 |
|--------|------|------------|------|
| P1 | 系統錯誤 (ERROR/CRITICAL) | #errors | Simple + Context |
| P1 | 安全事件（多次登入失敗） | #security | Blocks |
| P2 | 新用戶註冊 | #users | Blocks |
| P2 | Bug Report 提交 | #feedback | Blocks |
| P3 | 一般 Feedback | #feedback | Simple |

### 訊息格式設計

#### 用戶註冊通知（Slack Blocks）
```
🎉 新用戶註冊
├─ 用戶名稱: John Doe
├─ 註冊方式: 📧 Email / 🔗 Google
├─ Email Domain: @example.com
└─ 時間: 2026-01-12 10:30

📊 統計: 總用戶 1,234 | 今日新增 12
```

#### 錯誤日誌通知
```
🚨 *API Error* | `500`
Path: `POST /api/v1/beers/1/count_actions`
Error: SQLSTATE[HY000]: Connection refused
Request ID: req_abc123
User ID: 42

_2026-01-12 10:30:45 UTC_
```

### 環境變數配置

```env
#--------------------------------------------------------------------------
# Slack Integration (官方 Notification Channel)
#--------------------------------------------------------------------------

# Bot User OAuth Token (從 Slack App 的 OAuth & Permissions 頁面取得)
SLACK_BOT_USER_OAUTH_TOKEN=xoxb-your-bot-token-here

# 預設頻道（可在 Notification 中覆寫）
SLACK_BOT_USER_DEFAULT_CHANNEL=#general

# 錯誤日誌專用 Webhook URL (Monolog 使用)
SLACK_WEBHOOK_ERRORS=https://hooks.slack.com/services/T.../B.../xxx

# 功能開關
SLACK_NOTIFICATIONS_ENABLED=true
```

**說明**:
- **Bot User OAuth Token**: 用於 Laravel Notification 系統發送訊息
- **Webhook URL**: 僅用於 Monolog 錯誤日誌（Phase 3）
- 各 Notification 可在 `routeNotificationForSlack()` 中指定不同頻道

### 檔案清單

```
app/
├── Models/
│   └── User.php                              # 修改 (新增 routeNotificationForSlack)
├── Observers/
│   └── UserObserver.php                      # 新增 (Phase 2)
├── Notifications/
│   └── Slack/
│       └── UserRegisteredNotification.php    # 新增 (Phase 2)
└── Providers/
    └── AppServiceProvider.php                # 修改 (註冊 Observers)

config/
├── services.php                              # 已存在 (Slack 配置)
└── logging.php                               # 修改 (Phase 3)

tests/
└── Feature/
    └── Notifications/
        └── Slack/
            └── UserRegisteredNotificationTest.php    # 新增 (Phase 2)
```

**移除的檔案** (舊架構):
- ~~`app/Services/SlackNotificationService.php`~~
- ~~`app/Enums/SlackChannel.php`~~
- ~~`tests/Unit/Services/SlackNotificationServiceTest.php`~~

---

## 🔒 Security Considerations

### 不傳送到 Slack 的資料
- ❌ 完整 Email 地址（只傳送 domain）
- ❌ 密碼、Token
- ❌ IP 地址
- ❌ 完整錯誤堆疊（只傳送摘要）
- ❌ 敏感個人資料

### Rate Limiting
| 頻道 | 限制 |
|------|------|
| #errors | 10 則/分鐘 |
| #monitoring | 5 則/分鐘 |
| 其他 | 100 則/分鐘 |

### 測試環境隔離
- 在 `testing` 環境自動停用 Slack 通知
- 使用 `Notification::fake()` 和 `Http::fake()` 測試

---

## 🧪 Verification

### 單元測試
```bash
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/beer/HoldYourBeer workspace php artisan test --filter=Slack
```

### 手動測試
```bash
# 進入 tinker
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/beer/HoldYourBeer workspace php artisan tinker

# 測試日誌發送
>>> Log::error('Test error from HoldYourBeer', ['test' => true]);

# 測試用戶註冊通知（建立測試用戶）
>>> User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
```

### 確認 Queue Worker 運行
```bash
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/beer/HoldYourBeer workspace php artisan queue:work
```

---

## 🔧 Slack 設定步驟（官方架構）

### 1. 建立 Slack App
1. 前往 https://api.slack.com/apps
2. 點擊「Create New App」→「From scratch」
3. 輸入 App 名稱（如：HoldYourBeer Bot）
4. 選擇 Workspace

### 2. 設定 Bot Permissions
1. 左側選單：Features → **OAuth & Permissions**
2. 在 **Scopes** 區段，新增以下 **Bot Token Scopes**：
   - `chat:write` - 發送訊息到頻道
   - `chat:write.public` - 發送訊息到公開頻道（不需加入）
   - `chat:write.customize` - 自訂訊息外觀（名稱、圖示）

### 3. 安裝 App 到 Workspace
1. 在 **OAuth & Permissions** 頁面
2. 點擊「Install to Workspace」
3. 授權 App 存取 Workspace
4. 複製 **Bot User OAuth Token**（格式：`xoxb-...`）
5. 將 Token 加入 `.env`：
   ```env
   SLACK_BOT_USER_OAUTH_TOKEN=xoxb-your-token-here
   ```

### 4. 建立 Slack 頻道（可選）
建議建立以下頻道：
- `#holdyourbeer-users` - 用戶註冊通知
- `#holdyourbeer-errors` - 錯誤日誌
- `#holdyourbeer-feedback` - Feedback 通知 (未來規劃)

**注意**: 使用 Bot Token 時，不需要將 Bot 加入頻道，只要有 `chat:write.public` scope 即可發送到公開頻道。

### 5. （可選）建立 Incoming Webhook for Errors
如果要使用 Monolog Webhook Handler 記錄錯誤：
1. 左側選單：Features → **Incoming Webhooks**
2. 開啟「Activate Incoming Webhooks」
3. 點擊「Add New Webhook to Workspace」
4. 選擇 `#holdyourbeer-errors` 頻道
5. 複製 Webhook URL
6. 加入 `.env`：
   ```env
   SLACK_WEBHOOK_ERRORS=https://hooks.slack.com/services/...
   ```

---

## 🚧 Blockers & Solutions

暫無

---

## 📊 Outcome

### What Was Built
待實作完成後填寫

### Files Created/Modified
待實作完成後填寫

### Metrics
待實作完成後填寫

---

## 🎓 Lessons Learned

待實作完成後填寫

---

## ✅ Completion

**Status**: 🔄 In Progress
**Completed Date**: TBD
**Session Duration**: TBD

---

## 🔮 Future Improvements

### Not Implemented (Intentional)
- ⏳ Slack 互動式按鈕（如：一鍵標記 Feedback 為已處理）
- ⏳ 自訂通知訂閱（讓管理員選擇要收哪些通知）

### Potential Enhancements
- 📌 **Feedback 通知** - 收到用戶反饋時發送通知（包含 Bug Report）
- 📌 **安全警報通知** - 登入失敗、異常存取等安全事件通知
- 📌 整合 Slack Commands（如：`/holdyourbeer status`）
- 📌 每日/每週摘要報告
- 📌 效能監控通知（API 回應時間過長）

### Technical Debt
- 🔧 暫無

---

## 🔗 References

### External Resources
- [Laravel Notifications](https://laravel.com/docs/notifications)
- [Slack Incoming Webhooks](https://api.slack.com/messaging/webhooks)
- [Slack Block Kit](https://api.slack.com/block-kit)

### Related Files
- `config/services.php` - 現有 Slack 配置
- `config/logging.php` - 日誌頻道配置
- `app/Observers/BrandObserver.php` - Observer 參考
- `app/Notifications/VerifyEmailNotification.php` - Notification 參考
