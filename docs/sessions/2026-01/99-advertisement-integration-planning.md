# Session: 廣告整合與收益系統規劃（Web 端）

**Date**: 2026-01-23
**Status**: 📝 Planning
**Duration**: [預估] TBD
**Issue**: #TBD
**Contributors**: @kiddchan

**Tags**: #planning, #monetization, #advertising, #revenue

**Categories**: Monetization, Infrastructure, GDPR Compliance

---

## 📋 Overview

### Goal
規劃 HoldYourBeer Web 端（Laravel）的廣告整合與收益系統，實現應用程式的營收來源，同時確保用戶體驗與隱私合規。

### Related Documents
- **進度評估報告**: [progress-evaluation-2026-01-23.md](../../../progress-evaluation-2026-01-23.md)
- **Feature Spec**: [spec/features/advertisement_integration.feature](../../spec/features/advertisement_integration.feature)
- **相關規劃**: [23-google-analytics-integration-planning.md](23-google-analytics-integration-planning.md)

### Context
根據進度評估報告，廣告整合與收益系統目前：
- 📝 Feature 規格檔已存在（12 個場景）
- 🚧 前後端都尚未開始實作（0%）
- 🟢 優先級：Low

---

## 🎯 Context

### Problem
目前系統缺乏營收來源，無法：
- 支持長期營運成本
- 投資產品開發與改進
- 提供免費服務給使用者

### User Story
> As a **產品擁有者**,
> I want to **透過廣告或聯盟行銷獲得收益**,
> so that **我可以維持應用程式的長期營運並提供免費服務給使用者**。

### Current State
- ❌ 無任何營收來源
- ❌ 無廣告系統
- ❌ 無收益追蹤
- ❌ 無 Cookie 同意管理（廣告用途）
- ❌ 無聯盟行銷整合

---

## 🔍 功能範圍分析

### 根據 Feature Spec 的 12 個場景

根據 `advertisement_integration.feature` 規格檔，功能涵蓋：

#### 1️⃣ 廣告系統
- 📺 Google AdSense 整合
- 🎯 廣告管理系統
- 📍 策略性廣告位置
- 📱 行動響應式廣告

#### 2️⃣ 收益追蹤
- 💰 收益追蹤和報告
- 📊 廣告效能監控

#### 3️⃣ 隱私合規
- 🍪 Cookie 同意處理
- 🔒 GDPR/CCPA 隱私合規

#### 4️⃣ 優化與測試
- 🧪 A/B 測試廣告位置
- ⚡ 效能影響監控

#### 5️⃣ 聯盟行銷
- 🔗 聯盟行銷連結

---

## 💡 技術方案分析

### Option A: Google AdSense [✅ RECOMMENDED for Web]

**技術堆疊**：
- Google AdSense（廣告平台）
- AdSense Auto Ads（自動廣告）
- AdSense 手動廣告單元

**實作方式**：

#### 前端（Blade）
```blade
{{-- resources/views/layouts/app.blade.php --}}
<head>
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-XXXXXXXXXXXXXXXX"
            crossorigin="anonymous"></script>
</head>

<body>
    <!-- 廣告單元 -->
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="ca-pub-XXXXXXXXXXXXXXXX"
         data-ad-slot="XXXXXXXXXX"
         data-ad-format="auto"
         data-full-width-responsive="true"></ins>
    <script>
         (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
</body>
```

**Pros**:
- ✅ 最大的廣告網路，填充率高
- ✅ 易於整合（只需加入程式碼）
- ✅ 自動優化廣告顯示
- ✅ 支援多種廣告格式（橫幅、插頁、原生廣告）
- ✅ 收益穩定
- ✅ 免費使用

**Cons**:
- ⚠️ 需要達到流量門檻才能申請
- ⚠️ 審核嚴格（內容政策）
- ⚠️ 廣告內容無法完全控制
- ⚠️ 影響頁面載入速度

---

### Option B: 聯盟行銷（Affiliate Marketing）[✅ COMPLEMENTARY]

**技術堆疊**：
- 啤酒電商平台聯盟計畫
- Amazon Associates（如適用）
- 本地酒類零售商聯盟

**實作方式**：

```blade
{{-- 啤酒詳情頁 --}}
<div class="affiliate-links">
    <h3>Where to Buy</h3>
    <a href="{{ $affiliateLink }}" target="_blank" rel="nofollow sponsored">
        Buy on [Partner Store] →
    </a>
</div>
```

**Pros**:
- ✅ 與內容高度相關（啤酒推薦 → 購買連結）
- ✅ 對用戶有實際價值
- ✅ 不影響頁面體驗
- ✅ 可能的高轉換率
- ✅ 無需審核（取決於聯盟計畫）

**Cons**:
- ⚠️ 需要找到合適的聯盟夥伴
- ⚠️ 收益依賴轉換率
- ⚠️ 可能需要手動管理連結

---

### Option C: 直接廣告（Direct Ads）[❌ NOT RECOMMENDED INITIALLY]

**技術堆疊**：
- 自建廣告管理系統
- 直接與廣告主洽談

**Pros**:
- ✅ 100% 收益（無平台抽成）
- ✅ 完全控制廣告內容
- ✅ 更高的 CPM

**Cons**:
- ❌ 需要有足夠流量吸引廣告主
- ❌ 需要業務開發能力
- ❌ 需要自建廣告管理系統
- ❌ 對小型專案來說不切實際

---

**Decision Rationale**:
選擇 **Option A + Option B 混合方案** 因為：
1. ✅ AdSense 提供穩定的基礎收益
2. ✅ 聯盟行銷與內容高度相關
3. ✅ 兩者可以互補（AdSense 填充頁面，聯盟連結在內容中）
4. ✅ 初期投入成本低

---

## 📋 實作範圍規劃

### Phase 1: Google AdSense 帳號申請與設定 [優先級: 🔴 High]

**目標**：申請並設定 AdSense 帳號

#### 1.1 申請前準備
- [ ] 確保網站符合 AdSense 政策
  - [ ] 有足夠的原創內容
  - [ ] 無違反政策的內容（成人、暴力等）
  - [ ] 網站已運行至少 6 個月（建議）
  - [ ] 有一定流量（建議 > 1000 次瀏覽/天）

#### 1.2 申請流程
- [ ] 建立 Google AdSense 帳號
- [ ] 提交網站審核
- [ ] 等待審核通過（通常 1-2 週）

#### 1.3 取得廣告代碼
- [ ] 取得 Publisher ID（ca-pub-XXXXXXXXXXXXXXXX）
- [ ] 建立廣告單元
- [ ] 取得廣告單元代碼

**預估時間**: 申請流程 1 天 + 等待審核 1-2 週

---

### Phase 2: Cookie 同意管理（廣告用途）[優先級: 🔴 High]

**目標**：實作 GDPR/CCPA 合規的 Cookie 同意機制

#### 2.1 Cookie 同意橫幅（擴展 GA 版本）

```blade
{{-- resources/views/components/cookie-consent.blade.php --}}
@if(!session('cookie_consent'))
<div id="cookie-consent-banner" class="fixed bottom-0 left-0 right-0 bg-gray-800 text-white p-4 z-50">
    <div class="container mx-auto flex items-center justify-between">
        <p class="text-sm">
            We use cookies for analytics and personalized ads.
            <a href="{{ route('privacy-policy') }}" class="underline">Learn more</a>
        </p>
        <div class="flex gap-2">
            <button onclick="acceptCookies('all')" class="bg-blue-500 px-4 py-2 rounded">
                Accept All
            </button>
            <button onclick="acceptCookies('necessary')" class="bg-gray-500 px-4 py-2 rounded">
                Necessary Only
            </button>
            <button onclick="showCookieSettings()" class="text-sm underline">
                Customize
            </button>
        </div>
    </div>
</div>
@endif

<script>
function acceptCookies(type) {
    // 發送同意狀態到後端
    fetch('/api/cookie-consent', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({type: type})
    });

    // 根據同意類型載入對應腳本
    if (type === 'all') {
        loadGoogleAnalytics();
        loadGoogleAdSense();
    } else if (type === 'necessary') {
        // 只載入必要功能
    }

    // 隱藏橫幅
    document.getElementById('cookie-consent-banner').style.display = 'none';
}
</script>
```

#### 2.2 Laravel 端實作
```php
// app/Http/Controllers/CookieConsentController.php
public function store(Request $request)
{
    $request->validate([
        'type' => 'required|in:all,necessary,custom'
    ]);

    session(['cookie_consent' => $request->type]);

    return response()->json(['success' => true]);
}
```

#### 2.3 Google Consent Mode
```javascript
// 設定 Google Consent Mode
gtag('consent', 'default', {
    'ad_storage': 'denied',
    'analytics_storage': 'denied',
    'ad_user_data': 'denied',
    'ad_personalization': 'denied'
});

// 用戶同意後更新
gtag('consent', 'update', {
    'ad_storage': 'granted',
    'analytics_storage': 'granted',
    'ad_user_data': 'granted',
    'ad_personalization': 'granted'
});
```

**預估時間**: 2-3 天

---

### Phase 3: AdSense 廣告單元整合 [優先級: 🟡 Medium]

**目標**：在戰略位置加入廣告單元

#### 3.1 廣告位置策略

**桌面版建議位置**：
1. **Header Banner**（頂部橫幅）- 728x90 或 970x90
2. **Sidebar**（側邊欄）- 300x250 或 300x600
3. **In-Feed Ads**（內容中）- 原生廣告
4. **Footer Banner**（底部橫幅）- 728x90

**手機版建議位置**：
1. **Mobile Banner**（頂部）- 320x50 或 320x100
2. **In-Feed Ads**（內容中）- 原生廣告
3. **Anchor Ads**（固定底部）- 自動

#### 3.2 Blade Component 建立

```blade
{{-- resources/views/components/ad-unit.blade.php --}}
@props(['slot', 'format' => 'auto', 'style' => 'display:block'])

@if(session('cookie_consent') === 'all' && config('services.adsense.enabled'))
<ins class="adsbygoogle"
     style="{{ $style }}"
     data-ad-client="{{ config('services.adsense.client_id') }}"
     data-ad-slot="{{ $slot }}"
     data-ad-format="{{ $format }}"
     data-full-width-responsive="true"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script>
@endif
```

#### 3.3 使用範例

```blade
{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <div class="container">
        <!-- 頂部橫幅廣告 -->
        <x-ad-unit slot="1234567890" format="horizontal" />

        <!-- 內容 -->
        <div class="content">
            <!-- 啤酒列表 -->
        </div>

        <!-- 側邊欄廣告 -->
        <aside>
            <x-ad-unit slot="0987654321" format="rectangle" />
        </aside>
    </div>
</x-app-layout>
```

#### 3.4 環境變數配置

```env
# .env
ADSENSE_ENABLED=true
ADSENSE_CLIENT_ID=ca-pub-XXXXXXXXXXXXXXXX
ADSENSE_AUTO_ADS=true
```

```php
// config/services.php
'adsense' => [
    'enabled' => env('ADSENSE_ENABLED', false),
    'client_id' => env('ADSENSE_CLIENT_ID'),
    'auto_ads' => env('ADSENSE_AUTO_ADS', false),
],
```

**預估時間**: 2-3 天

---

### Phase 4: Auto Ads 整合 [優先級: 🟡 Medium]

**目標**：啟用 AdSense Auto Ads，自動優化廣告位置

#### 4.1 Auto Ads 腳本

```blade
{{-- resources/views/layouts/app.blade.php --}}
@if(session('cookie_consent') === 'all' && config('services.adsense.auto_ads'))
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('services.adsense.client_id') }}"
        crossorigin="anonymous"></script>
@endif
```

#### 4.2 AdSense 後台設定
- [ ] 登入 AdSense 後台
- [ ] 啟用 Auto Ads
- [ ] 選擇廣告格式（橫幅、插頁、錨點、穿插等）
- [ ] 調整廣告密度

**優點**：
- ✅ Google 自動優化廣告位置
- ✅ 減少手動管理成本
- ✅ 通常能提升收益

**缺點**：
- ⚠️ 對頁面設計控制度較低
- ⚠️ 可能顯示在不想要的位置

**預估時間**: 1 天

---

### Phase 5: 聯盟行銷整合 [優先級: 🟡 Medium]

**目標**：在啤酒詳情頁加入購買連結

#### 5.1 資料庫設計

```php
// database/migrations/xxxx_add_affiliate_links_to_beers_table.php
Schema::table('beers', function (Blueprint $table) {
    $table->string('affiliate_link')->nullable();
    $table->string('affiliate_partner')->nullable(); // 例如：'Amazon', 'Local Store'
});
```

#### 5.2 Admin 後台管理（Laravel Nova）

```php
// app/Nova/Beer.php
public function fields(Request $request)
{
    return [
        // ... existing fields

        Text::make('Affiliate Link')->nullable(),
        Select::make('Affiliate Partner')->options([
            'amazon' => 'Amazon',
            'local' => 'Local Store',
            'custom' => 'Custom'
        ])->nullable(),
    ];
}
```

#### 5.3 前端顯示

```blade
{{-- resources/views/beers/show.blade.php --}}
<div class="beer-details">
    <h1>{{ $beer->name }}</h1>

    @if($beer->affiliate_link)
    <div class="buy-section mt-4 p-4 bg-blue-50 rounded">
        <h3 class="font-semibold">Where to Buy</h3>
        <a href="{{ $beer->affiliate_link }}"
           target="_blank"
           rel="nofollow sponsored noopener"
           class="btn btn-primary mt-2"
           onclick="trackAffiliateClick('{{ $beer->id }}')">
            Buy on {{ $beer->affiliate_partner }} →
        </a>
    </div>
    @endif
</div>

<script>
function trackAffiliateClick(beerId) {
    // 追蹤點擊事件
    gtag('event', 'affiliate_click', {
        beer_id: beerId,
        partner: '{{ $beer->affiliate_partner }}'
    });
}
</script>
```

**預估時間**: 2-3 天

---

### Phase 6: 收益追蹤與報告 [優先級: 🟢 Low]

**目標**：追蹤廣告收益與效能

#### 6.1 AdSense API 整合

```bash
composer require google/apiclient
```

```php
// app/Services/AdSenseReportService.php
class AdSenseReportService
{
    public function getRevenue($startDate, $endDate)
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/adsense-credentials.json'));
        $client->addScope(Google_Service_AdSense::ADSENSE_READONLY);

        $service = new Google_Service_AdSense($client);

        $report = $service->accounts_reports->generate(
            'accounts/' . config('services.adsense.account_id'),
            $startDate,
            $endDate,
            ['metrics' => ['EARNINGS', 'PAGE_VIEWS', 'CLICKS']]
        );

        return $report;
    }
}
```

#### 6.2 Dashboard 顯示

```blade
{{-- resources/views/admin/revenue-dashboard.blade.php --}}
<div class="revenue-dashboard">
    <h2>Ad Revenue</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Today's Earnings</h3>
            <p class="amount">${{ $todayEarnings }}</p>
        </div>

        <div class="stat-card">
            <h3>This Month</h3>
            <p class="amount">${{ $monthEarnings }}</p>
        </div>

        <div class="stat-card">
            <h3>Page RPM</h3>
            <p class="amount">${{ $rpm }}</p>
        </div>
    </div>
</div>
```

**預估時間**: 2-3 天

---

### Phase 7: A/B 測試廣告位置 [優先級: 🟢 Low]

**目標**：測試不同廣告位置的效果

#### 7.1 測試框架

使用 Google Optimize 或自建 A/B 測試：

```javascript
// Variant A: 側邊欄廣告
// Variant B: 內容中廣告

if (getExperimentVariant() === 'A') {
    showSidebarAd();
} else {
    showInContentAd();
}

// 追蹤轉換
gtag('event', 'ad_impression', {
    variant: getExperimentVariant()
});
```

**預估時間**: 3-5 天（含實驗設計與分析）

---

### Phase 8: 效能影響監控 [優先級: 🟡 Medium]

**目標**：監控廣告對頁面載入速度的影響

#### 8.1 Core Web Vitals 追蹤

```javascript
// 監控 LCP（Largest Contentful Paint）
new PerformanceObserver((entryList) => {
    for (const entry of entryList.getEntries()) {
        console.log('LCP:', entry.renderTime || entry.loadTime);

        gtag('event', 'web_vitals', {
            name: 'LCP',
            value: entry.renderTime || entry.loadTime,
            metric_id: entry.id
        });
    }
}).observe({type: 'largest-contentful-paint', buffered: true});
```

#### 8.2 廣告載入延遲

```javascript
// 延遲載入廣告（頁面載入完成後）
window.addEventListener('load', function() {
    setTimeout(function() {
        loadAdSenseAds();
    }, 1000); // 延遲 1 秒
});
```

**預估時間**: 1-2 天

---

### Phase 9: 隱私政策更新 [優先級: 🔴 High]

**目標**：更新隱私政策說明廣告使用

#### 9.1 隱私政策內容

```blade
{{-- resources/views/privacy-policy.blade.php --}}
<section>
    <h2>Advertising</h2>
    <p>
        We use Google AdSense to display advertisements on our website.
        Google AdSense uses cookies to serve ads based on your prior visits
        to our website or other websites.
    </p>

    <h3>How to Opt-Out</h3>
    <p>
        You can opt out of personalized advertising by visiting
        <a href="https://www.google.com/settings/ads">Google's Ads Settings</a>.
    </p>
</section>

<section>
    <h2>Affiliate Marketing</h2>
    <p>
        We participate in affiliate marketing programs. When you click on
        affiliate links and make a purchase, we may earn a commission at
        no additional cost to you.
    </p>
</section>
```

**預估時間**: 1 天

---

### Phase 10: 廣告封鎖偵測（可選）[優先級: 🟢 Low]

**目標**：偵測使用廣告封鎖器的使用者

#### 10.1 偵測腳本

```javascript
// 偵測 AdBlock
setTimeout(function() {
    if (typeof adsbygoogle === 'undefined' || adsbygoogle.loaded !== true) {
        // AdBlock 被偵測到
        showAdBlockMessage();
    }
}, 2000);

function showAdBlockMessage() {
    // 顯示友善訊息
    const message = document.createElement('div');
    message.innerHTML = `
        <div class="adblock-notice">
            <p>We noticed you're using an ad blocker.</p>
            <p>Ads help us keep this service free. Please consider whitelisting us.</p>
        </div>
    `;
    document.body.appendChild(message);
}
```

**注意**：此功能可能引起使用者反感，需謹慎使用。

**預估時間**: 1 天

---

## 📊 整體實作計畫

### 建議實作順序（按優先級）

| Phase | 功能 | 優先級 | 預估時間 | 累計時間 |
|-------|------|--------|---------|---------|
| 1 | AdSense 帳號申請 | 🔴 High | 1 天 + 審核 | 1 天 + 審核 |
| 2 | Cookie 同意管理 | 🔴 High | 2-3 天 | 3-4 天 |
| 9 | 隱私政策更新 | 🔴 High | 1 天 | 4-5 天 |
| 3 | AdSense 廣告單元整合 | 🟡 Medium | 2-3 天 | 6-8 天 |
| 4 | Auto Ads 整合 | 🟡 Medium | 1 天 | 7-9 天 |
| 5 | 聯盟行銷整合 | 🟡 Medium | 2-3 天 | 9-12 天 |
| 8 | 效能影響監控 | 🟡 Medium | 1-2 天 | 10-14 天 |
| 6 | 收益追蹤與報告 | 🟢 Low | 2-3 天 | 12-17 天 |
| 7 | A/B 測試廣告位置 | 🟢 Low | 3-5 天 | 15-22 天 |
| 10 | 廣告封鎖偵測 | 🟢 Low | 1 天 | 16-23 天 |

**總預估時間**: 16-23 天（不含 AdSense 審核時間）

### MVP 範圍（最小可行方案）
優先實作以下功能：
1. ✅ Phase 1: AdSense 帳號申請（前置作業）
2. ✅ Phase 2: Cookie 同意管理（法規要求）
3. ✅ Phase 9: 隱私政策更新（法規要求）
4. ✅ Phase 3: AdSense 廣告單元整合（核心功能）

**MVP 預估時間**: 6-8 天（不含審核）

---

## 🔒 GDPR/CCPA 合規注意事項

### 必須實作的功能

1. **Cookie 同意機制** ✅
   - 在載入廣告前取得用戶同意
   - 支援 Google Consent Mode v2
   - 提供明確的選擇權

2. **隱私政策更新** ✅
   - 說明使用 Google AdSense
   - 說明個人化廣告
   - 提供退出機制連結

3. **用戶數據控制** ✅
   - 允許用戶選擇不接收個人化廣告
   - 提供數據刪除請求機制

4. **透明度** ✅
   - 明確標示聯盟連結（rel="sponsored"）
   - 說明廣告收益模式

---

## 💰 收益預估

### AdSense 收益計算

**基本公式**：
```
月收益 = (月流量 × 頁面 RPM) / 1000
```

**範例估算**：
- 假設月流量：50,000 次瀏覽
- 頁面 RPM：$2-$5（取決於利基市場）
- 預估月收益：$100-$250

**影響因素**：
- ✅ 流量品質（地區、裝置）
- ✅ 內容相關性
- ✅ 廣告位置
- ✅ 點擊率（CTR）
- ✅ 廣告格式

### 聯盟行銷收益

**計算方式**：
```
收益 = 點擊次數 × 轉換率 × 平均訂單金額 × 佣金比例
```

**範例估算**：
- 假設月點擊：1,000 次
- 轉換率：2%
- 平均訂單：$50
- 佣金比例：5%
- 預估月收益：1000 × 0.02 × 50 × 0.05 = $50

---

## 🧪 測試策略

### 測試工具

1. **AdSense 測試模式**
   - 使用 AdSense 測試廣告
   - 驗證廣告正確顯示

2. **Google Tag Assistant**
   - 檢查 Consent Mode 設定
   - 驗證廣告請求正確觸發

3. **PageSpeed Insights**
   - 監控廣告對載入速度的影響
   - 確保 Core Web Vitals 達標

### 測試 Checklist

- [ ] Cookie 同意橫幅正常顯示
- [ ] 拒絕 Cookie 後廣告不載入
- [ ] 接受 Cookie 後廣告正常顯示
- [ ] 廣告響應式設計正確（手機/桌面）
- [ ] 聯盟連結正確追蹤
- [ ] rel="sponsored" 標籤正確
- [ ] 隱私政策內容完整
- [ ] 頁面載入速度 < 3 秒

---

## ⚠️ 注意事項與最佳實踐

### AdSense 政策遵守

1. **禁止行為**：
   - ❌ 自己點擊廣告
   - ❌ 鼓勵他人點擊廣告
   - ❌ 使用誤導性標籤（例如：「點這裡」指向廣告）
   - ❌ 過多廣告（影響用戶體驗）

2. **內容要求**：
   - ✅ 原創內容
   - ✅ 無成人內容
   - ✅ 無暴力內容
   - ✅ 無版權侵權內容

### 用戶體驗優先

1. **廣告密度**：
   - 建議廣告占頁面比例 < 30%
   - 避免過多插頁式廣告

2. **效能優化**：
   - 延遲載入廣告
   - 使用 Lazy Loading
   - 避免阻塞主要內容載入

3. **行動友善**：
   - 使用響應式廣告單元
   - 避免廣告與內容重疊
   - 確保按鈕與廣告有足夠間距

---

## 🔮 Future Enhancements

### 延後實作的功能

- ⏸️ **Header Bidding**
  - 多家廣告平台競價
  - 提升廣告收益

- ⏸️ **原生廣告**
  - 整合到內容中
  - 更自然的廣告體驗

- ⏸️ **影片廣告**
  - 更高的 CPM
  - 需要影片內容支援

- ⏸️ **訂閱制（去廣告）**
  - 提供無廣告體驗
  - 額外收益來源

---

## ✅ Completion Criteria

### Definition of Done

- [ ] AdSense 帳號已建立並通過審核
- [ ] 廣告單元已正確整合在至少 3 個位置
- [ ] Cookie 同意機制已實作且符合 GDPR
- [ ] 隱私政策已更新
- [ ] 聯盟連結已整合（至少 1 個夥伴）
- [ ] 廣告顯示正常（通過測試）
- [ ] 頁面載入速度符合標準（< 3 秒）
- [ ] 收益追蹤正常運作
- [ ] 所有測試通過

---

## 🔗 References

### Google AdSense 官方文件
- [AdSense 快速入門](https://support.google.com/adsense/answer/6084409)
- [AdSense 政策](https://support.google.com/adsense/answer/48182)
- [廣告單元指南](https://support.google.com/adsense/answer/9183460)
- [Consent Mode 整合](https://support.google.com/adsense/answer/10532670)

### GDPR/CCPA 合規
- [Google 隱私要求](https://support.google.com/adsense/answer/7670013)
- [Cookie 同意最佳實踐](https://support.google.com/adsense/answer/9005435)

### 聯盟行銷
- [Amazon Associates](https://affiliate-program.amazon.com/)
- [Affiliate Marketing 最佳實踐](https://blog.hubspot.com/marketing/beginner-guide-affiliate-marketing)

---

**Last Updated**: 2026-01-23
**Next Steps**:
1. 決定是否申請 AdSense（評估流量與內容是否符合要求）
2. 閱讀完整的 `advertisement_integration.feature` 規格檔
3. 評估聯盟行銷合作夥伴選項
4. 決定實作 MVP 或完整功能
