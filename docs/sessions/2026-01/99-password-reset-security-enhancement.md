# Session: 密碼重設安全性強化

**Date**: 2026-01-23
**Status**: 📝 Planning
**Duration**: [預估] 4-5 天
**Issue**: #TBD
**Contributors**: @kiddchan

**Tags**: #security, #backend, #password-reset, #urgent

**Categories**: Security, Backend Development

---

## 📋 Overview

### Goal
強化密碼重設功能的安全性，補齊缺失的 5 個場景，解決安全漏洞與邊緣案例處理不完整的問題。

### Related Documents
- **進度評估報告**: [progress-evaluation-2026-01-23.md](../../../progress-evaluation-2026-01-23.md)
- **Feature Spec**: [spec/features/password_reset_email.feature](../../spec/features/password_reset_email.feature)
- **Test File**: [tests/Feature/PasswordResetEmailTest.php](../../tests/Feature/PasswordResetEmailTest.php)

### Context
根據進度評估報告，密碼重設功能目前：
- ✅ 已完成 40% (3/8 場景)
- ⚠️ 存在安全漏洞（無速率限制）
- ⚠️ 邊緣案例處理不完整
- 🔴 優先級：High（緊急）

---

## 🎯 Context

### Problem
目前密碼重設功能存在嚴重的安全問題：

**安全風險** 🚨：
- ❌ **無速率限制** → 容易遭受暴力攻擊（Brute Force）
- ❌ **無特殊字元信箱處理** → 可能導致功能失效
- ❌ **無信件寄送失敗處理** → 用戶無法知道失敗原因
- ❌ **無非活躍帳戶處理** → 可能洩漏帳戶狀態資訊
- ❌ **無審計日誌記錄** → 無法追蹤可疑活動

**影響**：
- 🚨 系統容易受到攻擊
- 🚨 用戶體驗差（錯誤訊息不明確）
- 🚨 無法追蹤安全事件

### User Story
> As a **系統管理員**,
> I want to **確保密碼重設功能安全且健壯**,
> so that **系統不會被惡意攻擊，且用戶可以安全地重設密碼**。

### Current State

#### 已完成的場景 (3/8) ✅
1. ✅ 成功請求重設信件
2. ✅ 信件包含正確資訊
3. ✅ 非註冊信箱的安全處理

#### 待補齊的場景 (5/8) ❌
4. ❌ 速率限制實現（防止暴力攻擊）
5. ❌ 特殊字元信箱處理
6. ❌ 信件寄送失敗處理
7. ❌ 非活躍帳戶處理
8. ❌ 審計日誌記錄

---

## 💡 Planning

### 安全威脅分析

#### Threat 1: 暴力攻擊（Brute Force Attack）
**攻擊方式**：
- 惡意用戶大量請求重設密碼
- 目的：
  1. 探測系統中存在的 Email
  2. 造成郵件服務超載（DoS）
  3. 騷擾真實用戶

**防護方式**：速率限制（Rate Limiting）

---

#### Threat 2: 信箱探測（Email Enumeration）
**攻擊方式**：
- 透過錯誤訊息判斷 Email 是否存在於系統中

**防護方式**：統一錯誤訊息

---

#### Threat 3: 時序攻擊（Timing Attack）
**攻擊方式**：
- 透過響應時間判斷 Email 是否存在

**防護方式**：統一響應時間

---

## 📋 實作計畫

### Phase 1: 速率限制實現 [優先級: 🔴 High]

**目標**：防止暴力攻擊，限制請求頻率

#### 1.1 Laravel Rate Limiting

Laravel 提供內建的速率限制功能。

**方法 A：使用 Middleware（推薦）**

```php
// routes/web.php 或 routes/api.php
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])
    ->middleware('throttle:forgot-password');
```

```php
// app/Providers/RouteServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

protected function configureRateLimiting()
{
    RateLimiter::for('forgot-password', function (Request $request) {
        return Limit::perMinute(3) // 每分鐘最多 3 次請求
            ->by($request->email ?? $request->ip()) // 依據 Email 或 IP
            ->response(function () {
                return response()->json([
                    'message' => 'Too many password reset attempts. Please try again later.'
                ], 429);
            });
    });
}
```

**方法 B：自訂速率限制邏輯**

```php
// app/Http/Controllers/Auth/PasswordResetController.php
use Illuminate\Support\Facades\Cache;

public function sendResetLinkEmail(Request $request)
{
    $email = $request->input('email');
    $key = 'password_reset_' . $email;

    // 檢查速率限制
    if (Cache::has($key)) {
        $attempts = Cache::get($key);
        if ($attempts >= 3) {
            return response()->json([
                'message' => 'Too many attempts. Please try again in 15 minutes.'
            ], 429);
        }
    }

    // 增加嘗試次數
    Cache::put($key, Cache::get($key, 0) + 1, now()->addMinutes(15));

    // 執行密碼重設邏輯
    // ...
}
```

#### 1.2 速率限制策略

**建議配置**：
- 📧 **每個 Email**：3 次 / 15 分鐘
- 🌐 **每個 IP**：10 次 / 15 分鐘
- 🌍 **全域**：100 次 / 小時（防止分散式攻擊）

#### 1.3 測試

```php
// tests/Feature/PasswordResetEmailTest.php

/** @test */
public function it_applies_rate_limiting_to_password_reset_requests()
{
    $email = 'test@example.com';

    // 第 1-3 次請求應該成功
    for ($i = 0; $i < 3; $i++) {
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => $email
        ]);
        $response->assertStatus(200);
    }

    // 第 4 次請求應該被拒絕
    $response = $this->postJson('/api/v1/forgot-password', [
        'email' => $email
    ]);
    $response->assertStatus(429);
    $response->assertJson([
        'message' => 'Too many password reset attempts. Please try again later.'
    ]);
}
```

**預估時間**: 1 天

---

### Phase 2: 特殊字元信箱處理 [優先級: 🟡 Medium]

**目標**：正確處理包含特殊字元的 Email（例如：加號、點等）

#### 2.1 Email 驗證強化

```php
// app/Http/Requests/ForgotPasswordRequest.php
public function rules()
{
    return [
        'email' => [
            'required',
            'email:rfc,dns', // 嚴格驗證 RFC 標準與 DNS
            'max:255'
        ],
    ];
}
```

#### 2.2 Email 正規化

```php
// app/Services/EmailNormalizationService.php
class EmailNormalizationService
{
    public function normalize(string $email): string
    {
        // 轉小寫
        $email = strtolower($email);

        // 移除多餘空白
        $email = trim($email);

        // Gmail 特殊處理：忽略點與加號
        if (Str::endsWith($email, '@gmail.com')) {
            [$local, $domain] = explode('@', $email);

            // 移除點
            $local = str_replace('.', '', $local);

            // 移除加號後的內容
            if (Str::contains($local, '+')) {
                $local = Str::before($local, '+');
            }

            $email = $local . '@' . $domain;
        }

        return $email;
    }
}
```

#### 2.3 測試

```php
/** @test */
public function it_handles_emails_with_special_characters()
{
    $testCases = [
        'test+tag@example.com',
        'test.name@example.com',
        'test..double@example.com',
        'test@sub.example.com',
    ];

    foreach ($testCases as $email) {
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => $email
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Password reset link sent to your email.'
        ]);
    }
}

/** @test */
public function it_normalizes_gmail_addresses()
{
    User::factory()->create(['email' => 'testuser@gmail.com']);

    // 這些變體應該指向同一個帳戶
    $variants = [
        'test.user@gmail.com',
        'testuser+tag@gmail.com',
        'test.user+tag@gmail.com',
    ];

    foreach ($variants as $email) {
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => $email
        ]);

        $response->assertStatus(200);
    }
}
```

**預估時間**: 1 天

---

### Phase 3: 信件寄送失敗處理 [優先級: 🟡 Medium]

**目標**：優雅處理郵件發送失敗的情況

#### 3.1 錯誤處理

```php
// app/Http/Controllers/Auth/PasswordResetController.php
use Illuminate\Support\Facades\Log;

public function sendResetLinkEmail(Request $request)
{
    try {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => __($status)
            ]);
        }

        return response()->json([
            'message' => __($status)
        ], 400);

    } catch (\Exception $e) {
        // 記錄錯誤
        Log::error('Password reset email failed', [
            'email' => $request->email,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // 不要洩漏錯誤細節給用戶
        return response()->json([
            'message' => 'Unable to send password reset email. Please try again later.'
        ], 500);
    }
}
```

#### 3.2 郵件發送監控

```php
// app/Listeners/PasswordResetEmailFailedListener.php
use Illuminate\Mail\Events\MessageSendingFailure;

class PasswordResetEmailFailedListener
{
    public function handle(MessageSendingFailure $event)
    {
        Log::error('Email sending failed', [
            'to' => $event->message->getTo(),
            'subject' => $event->message->getSubject(),
            'error' => $event->exception->getMessage()
        ]);

        // 可選：通知管理員
        // Notification::route('slack', env('SLACK_WEBHOOK'))
        //     ->notify(new EmailSendingFailedNotification($event));
    }
}
```

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    MessageSendingFailure::class => [
        PasswordResetEmailFailedListener::class,
    ],
];
```

#### 3.3 重試機制（使用 Queue）

```php
// app/Notifications/ResetPasswordNotification.php
use Illuminate\Bus\Queueable;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $tries = 3; // 重試 3 次
    public $backoff = [60, 300, 900]; // 重試間隔（秒）

    // ...
}
```

#### 3.4 測試

```php
/** @test */
public function it_handles_email_sending_failure_gracefully()
{
    // Mock Mail Facade to throw exception
    Mail::shouldReceive('send')
        ->andThrow(new \Exception('SMTP connection failed'));

    $response = $this->postJson('/api/v1/forgot-password', [
        'email' => 'test@example.com'
    ]);

    $response->assertStatus(500);
    $response->assertJson([
        'message' => 'Unable to send password reset email. Please try again later.'
    ]);

    // 確認錯誤被記錄
    $this->assertLogged('error', 'Password reset email failed');
}
```

**預估時間**: 1-2 天

---

### Phase 4: 非活躍帳戶處理 [優先級: 🟢 Low]

**目標**：正確處理 Email 未驗證或帳戶已停用的情況

#### 4.1 檢查帳戶狀態

```php
// app/Http/Controllers/Auth/PasswordResetController.php
public function sendResetLinkEmail(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $user = User::where('email', $request->email)->first();

    // 如果用戶不存在，仍返回成功訊息（防止信箱探測）
    if (!$user) {
        return response()->json([
            'message' => 'If your email exists in our system, you will receive a password reset link.'
        ]);
    }

    // 檢查帳戶是否已停用
    if ($user->is_suspended) {
        Log::warning('Password reset attempt for suspended account', [
            'email' => $request->email,
            'ip' => $request->ip()
        ]);

        // 返回通用訊息（不洩漏帳戶狀態）
        return response()->json([
            'message' => 'If your email exists in our system, you will receive a password reset link.'
        ]);
    }

    // 檢查 Email 是否已驗證
    if (!$user->hasVerifiedEmail()) {
        // 選項 1：要求先驗證 Email
        return response()->json([
            'message' => 'Please verify your email address before resetting your password.'
        ], 403);

        // 選項 2：同時發送驗證信與重設信
        // $user->sendEmailVerificationNotification();
        // Password::sendResetLink($request->only('email'));
    }

    // 發送重設密碼信件
    Password::sendResetLink($request->only('email'));

    return response()->json([
        'message' => 'Password reset link sent to your email.'
    ]);
}
```

#### 4.2 測試

```php
/** @test */
public function it_prevents_password_reset_for_unverified_accounts()
{
    $user = User::factory()->create([
        'email' => 'unverified@example.com',
        'email_verified_at' => null,
    ]);

    $response = $this->postJson('/api/v1/forgot-password', [
        'email' => 'unverified@example.com'
    ]);

    $response->assertStatus(403);
    $response->assertJson([
        'message' => 'Please verify your email address before resetting your password.'
    ]);
}

/** @test */
public function it_prevents_password_reset_for_suspended_accounts()
{
    $user = User::factory()->create([
        'email' => 'suspended@example.com',
        'is_suspended' => true,
    ]);

    $response = $this->postJson('/api/v1/forgot-password', [
        'email' => 'suspended@example.com'
    ]);

    // 不洩漏帳戶狀態，返回通用訊息
    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'If your email exists in our system, you will receive a password reset link.'
    ]);

    // 確認日誌記錄
    $this->assertLogged('warning', 'Password reset attempt for suspended account');
}
```

**預估時間**: 0.5 天

---

### Phase 5: 審計日誌記錄 [優先級: 🟡 Medium]

**目標**：記錄所有密碼重設相關活動，便於追蹤可疑行為

#### 5.1 建立審計日誌 Model

```php
// database/migrations/xxxx_create_audit_logs_table.php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->string('event_type'); // 例如：'password_reset_requested', 'password_reset_completed'
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
    $table->string('email')->nullable();
    $table->string('ip_address');
    $table->text('user_agent')->nullable();
    $table->json('metadata')->nullable(); // 額外資訊
    $table->timestamps();

    $table->index(['event_type', 'created_at']);
    $table->index('email');
});
```

```php
// app/Models/AuditLog.php
class AuditLog extends Model
{
    protected $fillable = [
        'event_type',
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

#### 5.2 記錄審計日誌

```php
// app/Services/AuditLogService.php
class AuditLogService
{
    public function log(string $eventType, ?User $user, array $metadata = []): void
    {
        AuditLog::create([
            'event_type' => $eventType,
            'user_id' => $user?->id,
            'email' => $metadata['email'] ?? $user?->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata
        ]);
    }
}
```

```php
// app/Http/Controllers/Auth/PasswordResetController.php
use App\Services\AuditLogService;

public function sendResetLinkEmail(Request $request, AuditLogService $auditLog)
{
    $user = User::where('email', $request->email)->first();

    // 記錄密碼重設請求
    $auditLog->log('password_reset_requested', $user, [
        'email' => $request->email,
        'success' => $user !== null
    ]);

    // ... 執行密碼重設邏輯
}

public function reset(Request $request, AuditLogService $auditLog)
{
    // 執行密碼重設
    $status = Password::reset(...);

    $user = User::where('email', $request->email)->first();

    // 記錄密碼重設完成
    $auditLog->log('password_reset_completed', $user, [
        'email' => $request->email,
        'success' => $status === Password::PASSWORD_RESET
    ]);

    // ...
}
```

#### 5.3 可疑活動偵測

```php
// app/Console/Commands/DetectSuspiciousPasswordResetActivity.php
class DetectSuspiciousPasswordResetActivity extends Command
{
    public function handle()
    {
        // 偵測可疑 IP（短時間內大量請求）
        $suspiciousIPs = AuditLog::where('event_type', 'password_reset_requested')
            ->where('created_at', '>', now()->subHour())
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) > 10')
            ->pluck('ip_address');

        foreach ($suspiciousIPs as $ip) {
            Log::warning('Suspicious password reset activity detected', [
                'ip' => $ip
            ]);

            // 可選：自動封鎖 IP
            // Cache::put('blocked_ip_' . $ip, true, now()->addHours(24));
        }
    }
}
```

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('detect:suspicious-password-reset')
        ->everyFifteenMinutes();
}
```

#### 5.4 測試

```php
/** @test */
public function it_logs_password_reset_requests()
{
    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/api/v1/forgot-password', [
        'email' => 'test@example.com'
    ]);

    $response->assertStatus(200);

    // 驗證審計日誌
    $this->assertDatabaseHas('audit_logs', [
        'event_type' => 'password_reset_requested',
        'email' => 'test@example.com',
        'user_id' => $user->id
    ]);
}

/** @test */
public function it_logs_password_reset_completion()
{
    $user = User::factory()->create(['email' => 'test@example.com']);
    $token = Password::createToken($user);

    $response = $this->postJson('/api/v1/reset-password', [
        'email' => 'test@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
        'token' => $token
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('audit_logs', [
        'event_type' => 'password_reset_completed',
        'email' => 'test@example.com',
        'user_id' => $user->id
    ]);
}
```

**預估時間**: 1 天

---

## 📊 整體實作計畫

### 建議實作順序（按優先級）

| Phase | 功能 | 優先級 | 預估時間 | 累計時間 | 安全影響 |
|-------|------|--------|---------|---------|---------|
| 1 | 速率限制實現 | 🔴 High | 1 天 | 1 天 | 防止暴力攻擊 |
| 5 | 審計日誌記錄 | 🟡 Medium | 1 天 | 2 天 | 追蹤可疑活動 |
| 2 | 特殊字元信箱處理 | 🟡 Medium | 1 天 | 3 天 | 功能完整性 |
| 3 | 信件寄送失敗處理 | 🟡 Medium | 1-2 天 | 4-5 天 | 用戶體驗 |
| 4 | 非活躍帳戶處理 | 🟢 Low | 0.5 天 | 4.5-5.5 天 | 資訊洩漏防護 |

**總預估時間**: 4.5-5.5 天

### MVP 範圍（最小可行方案）
優先實作以下功能：
1. ✅ Phase 1: 速率限制（最重要！）
2. ✅ Phase 5: 審計日誌記錄
3. ✅ Phase 2: 特殊字元信箱處理

**MVP 預估時間**: 3 天

---

## 🔒 安全最佳實踐

### 1. 時序攻擊防護

```php
// 確保響應時間一致（無論 Email 是否存在）
public function sendResetLinkEmail(Request $request)
{
    $startTime = microtime(true);

    $user = User::where('email', $request->email)->first();

    if ($user) {
        Password::sendResetLink($request->only('email'));
    }

    // 確保最少響應時間（例如 200ms）
    $minDuration = 0.2; // 秒
    $elapsed = microtime(true) - $startTime;
    if ($elapsed < $minDuration) {
        usleep(($minDuration - $elapsed) * 1000000);
    }

    return response()->json([
        'message' => 'If your email exists in our system, you will receive a password reset link.'
    ]);
}
```

### 2. Token 安全性

```php
// config/auth.php
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 15, // Token 15 分鐘後過期（而非預設 60 分鐘）
        'throttle' => 60, // 重新發送間隔
    ],
],
```

### 3. HTTPS 強制

```php
// app/Providers/AppServiceProvider.php
public function boot()
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
}
```

### 4. CSRF 保護

```php
// routes/web.php
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])
    ->middleware('csrf'); // 確保 CSRF token 驗證
```

---

## 🧪 測試策略

### 測試覆蓋目標

| 類別 | 目標覆蓋率 |
|------|-----------|
| 速率限制 | 100% |
| 特殊字元處理 | 100% |
| 錯誤處理 | ≥ 90% |
| 審計日誌 | ≥ 90% |
| 帳戶狀態檢查 | 100% |

### 測試清單

- [ ] 速率限制正確運作（3 次 / 15 分鐘）
- [ ] 特殊字元 Email 正確處理
- [ ] Gmail 地址正規化正確
- [ ] 信件寄送失敗時優雅處理
- [ ] 未驗證帳戶無法重設密碼
- [ ] 停用帳戶不洩漏狀態
- [ ] 審計日誌正確記錄
- [ ] 時序攻擊防護有效
- [ ] Token 過期正確處理
- [ ] 統一錯誤訊息（防止信箱探測）

---

## 📊 成功指標

### Definition of Done

- [ ] 所有 5 個待補齊的場景實作完成
- [ ] 速率限制正確運作
- [ ] 審計日誌正確記錄所有事件
- [ ] 所有測試通過（≥ 90% 覆蓋率）
- [ ] 無安全漏洞（通過安全審查）
- [ ] 文件更新（API 文件、安全指南）
- [ ] `spec:status` 顯示 100% 完成

---

## ⚠️ 注意事項

### 常見陷阱

1. **洩漏帳戶資訊**
   - ❌ 錯誤：「此 Email 不存在」
   - ✅ 正確：「如果您的 Email 存在於系統中，您將收到重設連結」

2. **時序攻擊**
   - ❌ 錯誤：存在的 Email 響應時間較長（發送郵件）
   - ✅ 正確：統一響應時間

3. **速率限制過於寬鬆**
   - ❌ 錯誤：10 次 / 分鐘
   - ✅ 正確：3 次 / 15 分鐘

4. **忘記記錄審計日誌**
   - ❌ 錯誤：只記錄成功的請求
   - ✅ 正確：記錄所有請求（成功與失敗）

---

## 🔮 Future Enhancements

### 延後實作的功能

- ⏸️ **雙因素驗證（2FA）**
  - 重設密碼前要求 2FA 驗證
  - 提升安全性

- ⏸️ **IP 位置偵測與通知**
  - 偵測異常位置的密碼重設請求
  - Email 通知用戶

- ⏸️ **帳戶鎖定機制**
  - 多次失敗後自動鎖定帳戶
  - 需要管理員解鎖

- ⏸️ **機器人偵測（reCAPTCHA）**
  - 防止自動化攻擊
  - 整合 Google reCAPTCHA

---

## ✅ Completion Criteria

### Phase 完成標準

**每個 Phase 完成時**：
- [ ] 功能實作完成
- [ ] 測試通過（包含邊緣案例）
- [ ] 安全審查通過
- [ ] 程式碼審查通過

**整體完成標準**：
- [ ] 所有 5 個場景實作完成
- [ ] 密碼重設功能進度從 40% → 100%
- [ ] 無已知安全漏洞
- [ ] `php artisan spec:status` 顯示完成
- [ ] 文件更新完成

---

## 🔗 References

### Laravel 官方文件
- [Rate Limiting](https://laravel.com/docs/routing#rate-limiting)
- [Password Reset](https://laravel.com/docs/passwords)
- [Mail & Notifications](https://laravel.com/docs/notifications)

### 安全最佳實踐
- [OWASP - Forgot Password Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Forgot_Password_Cheat_Sheet.html)
- [OWASP - Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)

### 相關規範
- [NIST SP 800-63B - Digital Identity Guidelines](https://pages.nist.gov/800-63-3/sp800-63b.html)

---

**Last Updated**: 2026-01-23
**Next Steps**:
1. 閱讀完整的 `password_reset_email.feature` 規格檔
2. 決定實作 MVP 或完整功能
3. 開始實作 Phase 1: 速率限制（最優先！）
4. 設定審計日誌監控與告警
