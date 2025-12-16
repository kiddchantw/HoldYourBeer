# Session: OAuth 三方登入與 Email 識別策略討論

**Date**: 2025-12-15
**Status**: ✅ Completed (Discussion & Analysis)
**Duration**: 1 hour
**Contributors**: @kiddchan, Claude AI
**Tags**: #architecture #decisions #authentication #oauth

**Categories**: Authentication, OAuth, User Identity, Product Design

---

## 📋 Overview

### Goal
釐清專案中的 OAuth 三方登入實作現狀，並討論「相同 email 使用不同 OAuth 提供者」的處理策略。

### Related Documents
- **Previous Session**: `11-google-oauth-email-verification-conflict.md`
- **Controller**: `app/Http/Controllers/SocialLoginController.php`
- **Model**: `app/Models/User.php`
- **Config**: `config/services.php`
- **Routes**: `routes/web.php`, `routes/api.php`

---

## 🎯 Context

### User Questions
1. **Q1**: 目前三方登入規劃，如果同一個 email 使用 Google 或 Apple ID login，會被視為同一 user 嗎？
2. **Q2**: 有提供 WhatsApp 的三方登入機制嗎？
3. **Q3**: 讓同一個 email 不同 OAuth 視為不同使用者會比較好嗎？

### Current State

#### 支援的 OAuth 提供者

| 提供者 | 狀態 | 配置位置 | 說明 |
|--------|------|----------|------|
| ✅ **Google** | 已完整支援 | `config/services.php:45-49` | Web + API 路由 |
| ✅ **Apple** | 已完整支援 | `config/services.php:51-55` | Web 路由 |
| ⚠️ **Facebook** | 預留但未配置 | `User.php:83` | 程式碼中有提到但未設定 |
| ❌ **WhatsApp** | 不支援 | - | 技術上不可行 |

**WhatsApp 不支援的原因：**
- WhatsApp 不提供標準的 OAuth 2.0 社交登入服務
- WhatsApp 主要是即時通訊應用，不像 Google/Facebook/Apple 有開放登入 API
- WhatsApp Business API 用於企業訊息功能，非用戶認證

#### 目前的 Email 處理策略

**實作方式**：**相同 email = 同一用戶**

參考 `SocialLoginController.php:53`：
```php
// Use email as the unique identifier
$user = User::where('email', $socialUser->getEmail())->first();

if ($user) {
    // Existing user - update verification status and provider info if needed
    // 更新 email_verified_at 和 provider（如果是首次 OAuth 登入）
    Auth::login($user, true);
} else {
    // Create new user with OAuth provider info
    $user = User::create([...]);
    Auth::login($user, true);
}
```

**行為說明：**
- ✅ 用戶用 `test@gmail.com` + Google 登入 → 建立帳號
- ✅ 同一用戶用 `test@gmail.com` + Apple 登入 → **登入同一帳號**
- ✅ 用戶先用 email/password 註冊，後用 Google 登入 → **合併為同一帳號**

#### 資料庫結構

參考 `database/migrations/2025_12_11_154607_add_provider_fields_to_users_table.php`：

```php
$table->string('provider')->nullable();     // 'local' | 'google' | 'apple' | 'facebook'
$table->string('provider_id')->nullable();  // OAuth 提供者的用戶 ID
$table->index(['provider', 'provider_id']);
```

**限制：**
- ⚠️ 只能記錄**第一個**使用的 OAuth 提供者
- ⚠️ 後續用其他 OAuth 登入時，`provider` 不會更新（`SocialLoginController.php:65-68`）

---

## 💡 Planning

### 核心問題：相同 Email 的處理策略

用戶提問：**讓同一個 email 不同 OAuth 視為不同使用者會比較好嗎？**

### Approach Analysis

#### Option A: 相同 email = 同一用戶 [✅ CHOSEN]

**實作概念：**
```
test@gmail.com + Google  ──┐
test@gmail.com + Apple   ──┤──> 同一個 User (id: 1)
test@gmail.com + Password ─┘
```

**Pros**:
1. **用戶體驗佳**
   - 用戶用不同方式登入，看到相同資料
   - 符合用戶直覺（email 是身份識別）
   - 不會產生重複帳號和混淆

2. **資料連續性完整**
   - 🍺 啤酒記錄不會分散在多個帳號
   - 統計數據準確（總共喝了幾杯）
   - 口味筆記集中管理

3. **符合業界慣例**
   - GitHub, Notion, Slack, Trello 等都採用此設計
   - 降低用戶學習成本

4. **適合產品定位**
   - HoldYourBeer 是個人追蹤型應用
   - 資料累積是核心價值
   - 用戶不希望數據分散

**Cons**:
1. **安全風險：帳號接管**
   ```
   攻擊場景：
   1. 駭客用你的 email 註冊（但無法驗證 email）
   2. 你用 Google OAuth 登入（Google 已驗證 email）
   3. 系統合併帳號 → 駭客可能已建立惡意資料
   ```

2. **隱私疑慮**
   - 用戶可能不想合併工作和個人帳號
   - 例如：`john@company.com` (工作) vs Google 個人帳號

3. **技術限制**
   - 目前只能記錄第一個 OAuth 提供者
   - 無法追蹤用戶連結了哪些帳號

---

#### Option B: 相同 email + 不同 OAuth = 不同用戶 [❌ REJECTED]

**實作概念：**
```
test@gmail.com + Google   → User (id: 1)
test@gmail.com + Apple    → User (id: 2)
test@gmail.com + Password → User (id: 3)
```

**Pros**:
1. **明確的帳號隔離**
   - 絕對不會有帳號合併問題
   - 無接管風險

**Cons**:
1. **用戶體驗極差** ⚠️
   ```
   用戶：「我昨天用 Google 登入記錄了 5 杯啤酒，
         今天用 Apple 登入怎麼都不見了？」
   系統：「因為這是不同帳號」
   用戶：「...那我要怎麼看我總共喝了幾杯？」
   ```

2. **資料分散嚴重**
   - 同一人的啤酒記錄分散在 3 個帳號
   - 統計數據不準確（每個帳號都是獨立計算）
   - 無法追蹤長期飲酒習慣

3. **違反直覺**
   - 違反 SaaS 應用慣例
   - 用戶會認為是 bug
   - 支援成本高（用戶會不斷詢問「為什麼資料不見了」）

4. **技術複雜度增加**
   - 需要實作「帳號合併」功能（用戶發現後會要求）
   - 資料遷移邏輯複雜

---

### Decision Rationale

**✅ 選擇 Option A（相同 email = 同一用戶）**

**理由：**

1. **產品定位決定設計**
   - HoldYourBeer 是**個人追蹤型應用**
   - 核心價值在於資料的**累積和統計**
   - 用戶期望用不同方式登入看到**相同資料**

2. **用戶體驗優先**
   - 方案 B 會造成嚴重的用戶困惑
   - 違反用戶對「email 是身份識別」的基本認知

3. **業界標準**
   - 所有主流 SaaS 應用都採用方案 A
   - 用戶已習慣這種行為

4. **安全問題可以透過其他方式解決**
   - Email 驗證保護
   - OAuth 連結管理
   - 不需要犧牲用戶體驗來換取安全性

---

## 🚧 Current Problems & Solutions

### Problem 1: 只記錄第一個 OAuth 提供者 [🔧 需改進]

**問題**：

目前 `users` 表只有單一 `provider` 和 `provider_id` 欄位：

```php
// SocialLoginController.php:65-68
if (!$user->provider) {
    $updates['provider'] = $actualProvider;
    $updates['provider_id'] = $socialUser->getId();
}
```

**影響**：
- ❌ 用戶先用 Google 登入 → `provider='google'`
- ❌ 後用 Apple 登入 → `provider` **不會更新**（因為已有值）
- ❌ 無法追蹤用戶連結了哪些 OAuth 帳號

**解決方案：建立 `user_oauth_providers` 關聯表**

```php
Schema::create('user_oauth_providers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('provider');      // 'google', 'apple', 'facebook'
    $table->string('provider_id');   // OAuth provider's user ID
    $table->string('provider_email')->nullable(); // OAuth 帳號的 email
    $table->timestamp('linked_at');
    $table->timestamp('last_used_at')->nullable();
    $table->timestamps();

    // 確保同一個 OAuth 帳號只能連結一次
    $table->unique(['provider', 'provider_id']);

    // 加速查詢
    $table->index('user_id');
});
```

**好處：**
- ✅ 可以記錄用戶連結的所有 OAuth 帳號
- ✅ 可以在個人設定頁面顯示「已連結帳號」
- ✅ 支援「解除連結」功能
- ✅ 追蹤最後使用時間

#### 📝 實作規劃：在目前系統上建立 `user_oauth_providers` 表

##### 第一步：建立 Migration

**檔案位置**：`database/migrations/YYYY_MM_DD_HHMMSS_create_user_oauth_providers_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_oauth_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider');           // 'google', 'apple', 'facebook'
            $table->string('provider_id');        // OAuth provider's user ID
            $table->string('provider_email')->nullable(); // OAuth 帳號的 email
            $table->timestamp('linked_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            // 確保同一個 OAuth 帳號只能連結一次
            $table->unique(['provider', 'provider_id'], 'unique_provider_account');

            // 加速查詢
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_oauth_providers');
    }
};
```

##### 第二步：建立 Model

**檔案位置**：`app/Models/UserOAuthProvider.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOAuthProvider extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_email',
        'linked_at',
        'last_used_at',
    ];

    protected $casts = [
        'linked_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns this OAuth provider link
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

##### 第三步：更新 User Model

**檔案位置**：`app/Models/User.php`

在 `User` model 中新增以下內容：

```php
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class User extends Authenticatable implements MustVerifyEmail
{
    // ... 現有程式碼 ...

    /**
     * Get all OAuth providers linked to this user
     */
    public function oauthProviders(): HasMany
    {
        return $this->hasMany(UserOAuthProvider::class);
    }

    /**
     * Check if user has linked a specific OAuth provider
     */
    public function hasOAuthProvider(string $provider): bool
    {
        return $this->oauthProviders()
            ->where('provider', $provider)
            ->exists();
    }

    /**
     * Get all linked OAuth providers as a collection
     */
    public function getLinkedProviders(): Collection
    {
        return $this->oauthProviders()
            ->orderBy('linked_at', 'desc')
            ->get();
    }

    /**
     * Get the number of authentication methods available
     * (password + OAuth providers)
     */
    public function getAuthMethodsCount(): int
    {
        $count = 0;

        // Check if user has password
        if ($this->password) {
            $count++;
        }

        // Add OAuth providers count
        $count += $this->oauthProviders()->count();

        return $count;
    }

    /**
     * Check if user can safely unlink an OAuth provider
     * (must have at least one other auth method)
     */
    public function canUnlinkOAuthProvider(): bool
    {
        return $this->getAuthMethodsCount() > 1;
    }
}
```

##### 第四步：修改 SocialLoginController

**檔案位置**：`app/Http/Controllers/SocialLoginController.php`

修改 `handleProviderCallback` 方法：

```php
use App\Models\UserOAuthProvider;

public function handleProviderCallback($locale = null, $provider = null): RedirectResponse
{
    // ... 前面的程式碼保持不變 ...

    $user = User::where('email', $socialUser->getEmail())->first();

    if ($user) {
        // 🔒 安全檢查：拒絕未驗證的本地帳號
        if ($user->isLocalUser() && !$user->email_verified_at) {
            $loginRoute = ($provider !== null)
                ? route('localized.login', ['locale' => $targetLocale])
                : route('login');

            return redirect($loginRoute)->withErrors([
                'social_login' => '此 email 已註冊但尚未驗證。請先完成 email 驗證，或使用密碼登入。'
            ]);
        }

        // 更新 email 驗證狀態（OAuth 已驗證）
        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        // 📊 記錄 OAuth 連結（使用新的關聯表）
        $user->oauthProviders()->updateOrCreate(
            [
                'provider' => $actualProvider,
                'provider_id' => $socialUser->getId(),
            ],
            [
                'provider_email' => $socialUser->getEmail(),
                'last_used_at' => now(),
                'linked_at' => now(),
            ]
        );

        Auth::login($user, true);
    } else {
        // 建立新用戶
        $user = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(16)),
            'email_verified_at' => now(),
            // ⚠️ 注意：不再設定 provider 和 provider_id（改用關聯表）
        ]);

        // 建立 OAuth 連結記錄
        UserOAuthProvider::create([
            'user_id' => $user->id,
            'provider' => $actualProvider,
            'provider_id' => $socialUser->getId(),
            'provider_email' => $socialUser->getEmail(),
            'linked_at' => now(),
            'last_used_at' => now(),
        ]);

        event(new Registered($user));
        Auth::login($user, true);
    }

    return redirect()->route('localized.dashboard', ['locale' => $targetLocale]);
}
```

##### 第五步：資料遷移 Script

**檔案位置**：`database/migrations/YYYY_MM_DD_HHMMSS_migrate_existing_oauth_data.php`

將現有 `users.provider` 資料遷移到新表：

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 將現有的 OAuth 用戶資料遷移到新表
        DB::table('users')
            ->whereNotNull('provider')
            ->whereIn('provider', ['google', 'apple', 'facebook'])
            ->whereNotNull('provider_id')
            ->orderBy('id')
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    DB::table('user_oauth_providers')->insert([
                        'user_id' => $user->id,
                        'provider' => $user->provider,
                        'provider_id' => $user->provider_id,
                        'provider_email' => $user->email,
                        'linked_at' => $user->created_at ?? now(),
                        'last_used_at' => $user->updated_at ?? now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // 清空遷移的資料
        DB::table('user_oauth_providers')->truncate();
    }
};
```

##### 第六步：執行 Migration

```bash
# 在 Laradock workspace 容器內執行
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace php artisan migrate

# 確認資料遷移結果
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace php artisan tinker
>>> \App\Models\UserOAuthProvider::count()
>>> \App\Models\User::with('oauthProviders')->find(1)
```

##### 第七步：測試驗證

建立測試檔案驗證功能：

**檔案位置**：`tests/Feature/OAuthProvidersTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserOAuthProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OAuthProvidersTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_multiple_oauth_providers(): void
    {
        $user = User::factory()->create();

        // 連結 Google
        UserOAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google_123',
            'provider_email' => $user->email,
            'linked_at' => now(),
        ]);

        // 連結 Apple
        UserOAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'apple',
            'provider_id' => 'apple_456',
            'provider_email' => $user->email,
            'linked_at' => now(),
        ]);

        $this->assertCount(2, $user->oauthProviders);
        $this->assertTrue($user->hasOAuthProvider('google'));
        $this->assertTrue($user->hasOAuthProvider('apple'));
        $this->assertFalse($user->hasOAuthProvider('facebook'));
    }

    public function test_deleting_user_cascades_oauth_providers(): void
    {
        $user = User::factory()->create();

        UserOAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google_123',
            'linked_at' => now(),
        ]);

        $this->assertCount(1, UserOAuthProvider::all());

        $user->delete();

        $this->assertCount(0, UserOAuthProvider::all());
    }

    public function test_user_can_check_auth_methods_count(): void
    {
        // 本地用戶（僅密碼）
        $localUser = User::factory()->create(['password' => bcrypt('password')]);
        $this->assertEquals(1, $localUser->getAuthMethodsCount());

        // 連結一個 OAuth
        UserOAuthProvider::create([
            'user_id' => $localUser->id,
            'provider' => 'google',
            'provider_id' => 'google_123',
            'linked_at' => now(),
        ]);
        $localUser->refresh();
        $this->assertEquals(2, $localUser->getAuthMethodsCount());
        $this->assertTrue($localUser->canUnlinkOAuthProvider());
    }
}
```

執行測試：

```bash
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace php artisan test --filter=OAuthProvidersTest
```

##### 第八步：後續清理（可選）

等新系統穩定運行後，可以考慮移除舊的欄位：

**檔案位置**：`database/migrations/YYYY_MM_DD_HHMMSS_remove_deprecated_provider_columns.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['provider', 'provider_id']);
            $table->dropColumn(['provider', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('password');
            $table->string('provider_id')->nullable()->after('provider');
            $table->index(['provider', 'provider_id']);
        });
    }
};
```

⚠️ **注意**：執行此 migration 前需確保：
1. 所有資料已正確遷移到新表
2. 所有程式碼已更新使用新的關聯表
3. 在生產環境充分測試
4. 備份資料庫

---

### Problem 2: Email 未驗證就允許 OAuth 合併 [🔒 安全風險]

**問題**：

目前程式碼允許未驗證的本地帳號與 OAuth 合併：

```php
// SocialLoginController.php:55-72
if ($user) {
    // ⚠️ 不管 email 是否驗證，都允許合併
    if (!$user->email_verified_at) {
        $updates['email_verified_at'] = now();
    }
    Auth::login($user, true);
}
```

**攻擊場景：**
```
1. 駭客用 victim@gmail.com 註冊（但無法完成 email 驗證）
2. 駭客在帳號中建立惡意資料
3. 真實用戶用 Google OAuth 登入（Google 已驗證 email）
4. 系統自動合併帳號
5. 真實用戶看到駭客建立的資料
```

**解決方案：增加驗證保護**

```php
// SocialLoginController.php (改進版)
if ($user) {
    // 🔒 安全檢查：僅允許 OAuth 用戶或已驗證用戶合併
    if ($user->isLocalUser() && !$user->email_verified_at) {
        // 拒絕合併未驗證的本地帳號
        return redirect()->route('login')->withErrors([
            'social_login' => '此 email 已註冊但尚未驗證。請先完成 email 驗證，或使用密碼登入。'
        ]);
    }

    // 安全：允許合併
    $user->update([
        'email_verified_at' => now()
    ]);

    // 記錄 OAuth 連結（使用新的關聯表）
    $user->oauthProviders()->updateOrCreate(
        [
            'provider' => $actualProvider,
            'provider_id' => $socialUser->getId(),
        ],
        [
            'provider_email' => $socialUser->getEmail(),
            'last_used_at' => now(),
            'linked_at' => now(),
        ]
    );

    Auth::login($user, true);
}
```

---

## ✅ Recommendations

### 短期改進（優先級高）

#### R1: 增加 Email 驗證保護 [🔒 安全]
- **目標**：防止未驗證帳號被 OAuth 接管
- **實作**：修改 `SocialLoginController.php:55-72`
- **工作量**：1-2 小時

**實作 Checklist**：
- [x] 在 `SocialLoginController::handleCallback` 中增加本地帳號驗證檢查
- [x] 當發現未驗證的本地帳號時，返回錯誤訊息而非自動合併
- [x] 允許已驗證帳號或 OAuth 帳號進行合併
- [x] 更新錯誤訊息提示用戶完成 email 驗證或使用密碼登入
- [x] 撰寫單元測試覆蓋以下情境：
  - [x] 未驗證本地帳號 + OAuth 登入 → 拒絕
  - [x] 已驗證本地帳號 + OAuth 登入 → 允許合併
  - [x] OAuth 帳號 + 另一個 OAuth 登入 → 允許合併
- [x] 在測試環境驗證流程

**✅ 實作完成** (2025-12-16)
- 修改檔案：`app/Http/Controllers/SocialLoginController.php:56-65`
- 測試檔案：`tests/Feature/SocialLoginTest.php`
- 測試結果：8 passed (50 assertions)

#### R2: 建立 OAuth 連結追蹤 [📊 功能完整性]
- **目標**：記錄用戶連結的所有 OAuth 帳號
- **實作**：
  - 建立 `user_oauth_providers` migration
  - 建立 `UserOAuthProvider` model
  - 更新 `User` model 增加關聯
  - 修改 `SocialLoginController` 使用新表
- **工作量**：3-4 小時

**實作 Checklist**：
- [x] **Migration**: 建立 `user_oauth_providers` 表
  - [x] 欄位：`id`, `user_id`, `provider`, `provider_id`, `provider_email`, `linked_at`, `last_used_at`, `timestamps`
  - [x] 外鍵：`user_id` → `users.id` (cascade delete)
  - [x] 唯一約束：`(provider, provider_id)`
  - [x] 索引：`user_id`
- [x] **Model**: 建立 `UserOAuthProvider` model
  - [x] 定義 `fillable` 屬性
  - [x] 定義與 `User` 的 `belongsTo` 關聯
  - [x] 增加 `$casts` 將 `linked_at` 和 `last_used_at` 轉為 Carbon
  - [x] 指定正確的表名稱 `user_oauth_providers`
- [x] **User Model**: 增加關聯方法
  - [x] 增加 `oauthProviders()` hasMany 關聯
  - [x] 增加輔助方法 `hasOAuthProvider(string $provider): bool`
  - [x] 增加輔助方法 `getLinkedProviders(): Collection`
  - [x] 增加輔助方法 `getAuthMethodsCount(): int`
  - [x] 增加輔助方法 `canUnlinkOAuthProvider(): bool`
- [x] **Controller**: 修改 `SocialLoginController`
  - [x] 移除更新 `users.provider` 和 `users.provider_id` 的邏輯
  - [x] 使用 `$user->oauthProviders()->updateOrCreate()` 記錄連結
  - [x] 更新 `last_used_at` 時間戳
- [x] **測試**：
  - [x] 測試首次 OAuth 登入建立記錄
  - [x] 測試相同 OAuth 再次登入更新 `last_used_at`
  - [x] 測試不同 OAuth 登入建立多筆記錄
  - [x] 更新測試驗證新的 OAuth provider 關聯
- [x] **資料遷移**: 將現有 `users.provider` 資料遷移到新表
  - [x] 撰寫 data migration script
  - [x] 執行 migration 成功

**✅ 實作完成** (2025-12-16)
- Migration: `database/migrations/2025_12_15_170845_create_user_oauth_providers_table.php`
- Migration: `database/migrations/2025_12_15_171043_migrate_existing_oauth_data_to_user_oauth_providers.php`
- Model: `app/Models/UserOAuthProvider.php`
- User Model 更新: `app/Models/User.php:106-157`
- Controller 更新: `app/Http/Controllers/SocialLoginController.php:6,75-85,98-105`
- 測試更新: `tests/Feature/SocialLoginTest.php:74-79`
- 測試結果：8 passed (52 assertions)

### 中期改進（優先級中）

#### R3: 帳號設定頁面 [👤 用戶體驗]
- **目標**：讓用戶看到已連結的 OAuth 帳號
- **功能**：
  - 顯示已連結的 OAuth 帳號列表
  - 顯示連結時間和最後使用時間
  - 提供「解除連結」按鈕（至少保留一種登入方式）
- **工作量**：4-6 小時

**✅ 實作完成** (2025-12-16)
- **Backend**: 使用現有的 `ProfileController::edit`
- **Frontend**:
  - 建立 `resources/views/profile/partials/connected-accounts-form.blade.php`
  - 更新 `resources/views/profile/edit.blade.php` 採用 Grid 版面設計
  - 建立 `lang/en/profile.php` 和 `lang/zh_TW/profile.php` 語系檔
- **Testing**:
  - 在 `tests/Feature/ProfileTest.php` 新增測試案例，驗證區塊顯示與狀態

**實作 Checklist**：
- [x] **Backend**：
  - [x] 使用 `ProfileController@edit` 載入 View
  - [x] 透過 `User` model 的關聯方法取得 OAuth 資料
- [x] **Frontend**：
  - [x] 建立帳號設定頁面 UI (Blade)
  - [x] 顯示已連結的 OAuth 提供者（Google, Apple, Facebook）
  - [x] 顯示每個連結的時間和最後使用時間
  - [x] 為未連結的提供者顯示「連結」按鈕
  - [x] 為已連結的提供者顯示「解除連結」按鈕
  - [x] 至少保留一種登入方式（disable 最後一個解除連結按鈕）
  - [x] 重構 Profile 頁面為 Grid Layout
- [x] **測試**：
  - [x] 測試頁面正確顯示已連結的 OAuth 帳號
  - [x] 測試未連結的提供者顯示「連結」選項
  - [x] 測試最後一種登入方式無法解除連結

#### R4: 連結/解除連結功能 [🔗 帳號管理]
- **目標**：用戶可主動管理 OAuth 連結
- **功能**：
  - 在已登入狀態下連結新的 OAuth 帳號
  - 解除不需要的 OAuth 連結
  - 安全檢查：至少保留一種登入方式
- **工作量**：3-4 小時

**✅ 實作完成** (2025-12-16)
- Controller 方法: `app/Http/Controllers/SocialLoginController.php:118-210`
- 路由定義: `routes/web.php:100-104,118-122`
- 測試檔案: `tests/Feature/OAuthLinkUnlinkTest.php`
- 測試結果：9 passed (37 assertions)
- 資料庫遷移: `database/migrations/2025_12_15_232651_make_users_password_nullable.php`

**實作 Checklist**：
- [x] **連結功能**：
  - [x] 建立路由：`GET /auth/{provider}/link`
  - [x] 修改 `SocialLoginController` 增加 `linkProvider()` 方法
  - [x] 檢查用戶已登入
  - [x] 執行 OAuth 流程
  - [x] 檢查 OAuth email 是否與當前用戶 email 一致
  - [x] 建立 `user_oauth_providers` 記錄
  - [x] 返回成功訊息
- [x] **解除連結功能**：
  - [x] 建立路由：`DELETE /auth/{provider}/unlink`
  - [x] 建立 `unlinkProvider()` 方法
  - [x] 檢查用戶至少有 2 種登入方式（password 或其他 OAuth）
  - [x] 刪除 `user_oauth_providers` 記錄
  - [x] 返回成功訊息
- [x] **安全檢查**：
  - [x] 確保用戶無法連結其他人的 OAuth 帳號
  - [x] 確保至少保留一種登入方式
  - [x] 記錄審計日誌（透過 `linked_at` 和 `last_used_at`）
- [x] **測試**：
  - [x] 測試已登入用戶連結新 OAuth 帳號
  - [x] 測試連結時 email 不一致的情況
  - [x] 測試解除連結功能
  - [x] 測試最後一種登入方式無法解除連結
  - [x] 測試未登入用戶無法連結
  - [x] 測試 OAuth 帳號已連結到其他用戶的情況
  - [x] 測試有密碼和 OAuth 的用戶可以解除 OAuth
  - [x] 測試有多個 OAuth 的用戶可以解除其中一個

---

## 🎓 Lessons Learned

### 1. 產品定位決定技術決策

**Learning**:
- 技術設計不能脫離產品定位
- HoldYourBeer 作為**個人追蹤型應用**，資料連續性是核心價值
- 方案 B（不同 OAuth = 不同用戶）雖然技術上更簡單，但違反產品目標

**Future Application**:
- 在做架構決策時，先問：「這符合產品的核心價值嗎？」
- 不要為了技術簡單而犧牲用戶體驗

---

### 2. 安全性可以透過多層防護實現

**Learning**:
- 不需要透過「拆分帳號」來防止接管風險
- 可以用更精細的方式保護：Email 驗證、連結管理、審計日誌

**Pattern**:
```
安全防護層：
1. Email 驗證保護（防止未驗證帳號合併）
2. OAuth 連結追蹤（審計）
3. 帳號設定頁面（透明度）
4. 解除連結功能（用戶控制）
```

**Future Application**:
- 優先採用「多層防護」而非「限制功能」
- 給用戶更多控制權，而非限制

---

### 3. 業界標準有其道理

**Learning**:
- 所有主流 SaaS 應用（GitHub, Notion, Slack）都用「相同 email = 同一用戶」
- 這不是巧合，而是經過大量用戶驗證的最佳實踐

**Future Application**:
- 遇到設計問題時，先調查業界標準
- 如果要偏離標準，需要有非常充分的理由

---

## 🔮 Future Improvements

### Not Implemented (Intentional)

以下功能在討論中被提及，但基於優先級和實際需求考量，目前不予實作：

#### ⏳ R5: 帳號合併工具 [🛠️ 管理工具]

**為什麼不實作**：
- 目前需求不明確，尚未有用戶反饋需要此功能
- 實作成本高（8-10 小時），但使用頻率預期極低
- 可以先觀察實際使用情況，等有明確需求再實作

**原規劃目標**：處理特殊情況（例如用戶誤建多個帳號）

**原規劃功能**：
- Admin 介面手動合併帳號
- 資料遷移（啤酒記錄、統計）
- 審計日誌

**如果未來要實作的 Checklist**：
<details>
<summary>展開查看完整實作清單</summary>

- [ ] **Admin 介面**：
  - [ ] 建立 Admin 權限管理
  - [ ] 建立帳號搜尋介面（依 email, user_id）
  - [ ] 建立帳號合併預覽頁面
  - [ ] 顯示兩個帳號的所有資料（啤酒記錄、OAuth 連結、統計）
- [ ] **合併邏輯**：
  - [ ] 建立 `AccountMergeService`
  - [ ] 實作資料遷移邏輯：
    - [ ] 啤酒記錄（`beers`）
    - [ ] OAuth 連結（`user_oauth_providers`）
    - [ ] 統計數據
    - [ ] 其他相關資料
  - [ ] 處理重複資料的策略
  - [ ] 軟刪除被合併的帳號
- [ ] **審計日誌**：
  - [ ] 記錄合併操作的詳細資訊
  - [ ] 記錄操作的 Admin 用戶
  - [ ] 記錄合併的時間和原因
  - [ ] 保留資料快照以便回溯
- [ ] **測試**：
  - [ ] 測試合併兩個帳號的資料完整性
  - [ ] 測試合併後的統計數據正確性
  - [ ] 測試審計日誌完整記錄
  - [ ] 測試權限控制（僅 Admin 可執行）

</details>

---

#### ⏳ Facebook OAuth
- **狀態**：程式碼已預留，但尚未配置
- **原因**：等確認需求和 Facebook App 審核流程

#### ⏳ Two-Factor Authentication (2FA)
- **狀態**：未來可能需要
- **原因**：目前優先級低，先完成基礎 OAuth 功能

### Potential Enhancements
- 📌 **手機號碼驗證**：作為 email 的補充驗證方式
- 📌 **登入歷史記錄**：追蹤用戶的登入行為（裝置、IP、時間）
- 📌 **異常登入偵測**：新裝置登入時發送通知

### Technical Debt
- 🔧 **users.provider 欄位**：未來可能移除（改用關聯表）
- 🔧 **Migration 歷史遺留**：`2025_08_20_041706` 和 `2025_11_06_174229` 已失效但仍保留

---

## 🔗 References

### Related Sessions
- [11-google-oauth-email-verification-conflict.md](./11-google-oauth-email-verification-conflict.md) - Google OAuth 與 Email 驗證衝突分析

### Code References
- `app/Http/Controllers/SocialLoginController.php` - OAuth 登入邏輯
- `app/Models/User.php:82-84` - `isOAuthUser()` 方法
- `config/services.php:45-55` - OAuth 提供者配置
- `database/migrations/2025_12_11_154607_add_provider_fields_to_users_table.php` - Provider 欄位

### External Resources
- [Laravel Socialite Documentation](https://laravel.com/docs/11.x/socialite)
- [OAuth 2.0 Best Practices](https://datatracker.ietf.org/doc/html/draft-ietf-oauth-security-topics)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)

### Industry Examples
- GitHub: 相同 email 可綁定多個 OAuth 帳號
- Notion: 支援 Google, Apple, SSO，相同 email = 同一帳號
- Slack: 工作區內相同 email 自動合併

---

## 📝 Summary

### Key Decisions
1. ✅ **保持「相同 email = 同一用戶」設計**
   - 符合產品定位（個人追蹤型應用）
   - 符合業界標準
   - 提供更好的用戶體驗

2. ✅ **不支援 WhatsApp OAuth**
   - WhatsApp 不提供 OAuth 2.0 登入服務
   - 技術上不可行

3. ✅ **優先改進安全機制**
   - Email 驗證保護
   - OAuth 連結追蹤
   - 帳號管理功能

### Next Steps
1. 實作 Email 驗證保護（防止帳號接管）
2. 建立 `user_oauth_providers` 關聯表
3. 開發帳號設定頁面（顯示已連結的 OAuth 帳號）

---

**Session Completed**: 2025-12-15
**Status**: ✅ Discussion & Analysis Completed
**Follow-up Required**: Implementation of security improvements
