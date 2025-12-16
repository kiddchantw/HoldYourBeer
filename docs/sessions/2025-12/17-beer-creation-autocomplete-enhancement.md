# Session: 新增啤酒頁面自動填入功能強化

**Date**: 2025-12-17
**Version**: 2.0 - Updated with Hybrid Approach (beer_shop + tasting_logs.shop_id)
**Status**: 🔄 In Progress
**Duration**: 預估 2.5-3 小時
**Contributors**: @kiddchan, Claude AI

**Tags**: #product #architecture #api

**Development Approach**: Test-Driven Development (TDD)

**Categories**: Beer Tracking, User Experience, Autocomplete

---

## 📋 Overview

### Goal
強化「新增啤酒」頁面的使用者體驗，新增「購入店家」欄位並提供自動填入功能，同時修正頁面語系支援問題。

### Related Documents
- **Feature Spec**: `spec/features/beer_tracking/adding_a_beer.feature`
- **API Spec**: `spec/api/api.yaml`
- **相關頁面**: http://local.holdyourbeers.com/zh-TW/beers/create

### Commits
- 待實作後填寫

---

## 🎯 Context

### Problem
目前「新增啤酒」頁面存在以下問題：

1. **品牌名稱自動填入驗證需求**：用戶希望確認新增的品牌能夠成為下一位用戶的自動填入選項
2. **啤酒名稱自動填入驗證需求**：需要確認現有實作是否符合使用者期望
3. **缺少購入店家欄位**：用戶想記錄啤酒的購入地點，但目前沒有相關功能
4. **語系支援不完整**：頁面沒有針對多語言環境做完整調整，部分文字未翻譯

### User Story

> As a beer enthusiast, I want to record where I purchased each beer when adding it to my collection, so that I can remember and share my favorite stores with others.

> As a user, I want the autocomplete suggestions to include shops that other users have entered, so that I don't need to type the full store name every time.

> As a bilingual user, I want all interface elements to be properly translated when I switch languages, so that I can use the app comfortably in my preferred language.

### Current State

**已存在的功能**：
- ✅ 品牌名稱自動填入（使用 `Brand::firstOrCreate()`）
- ✅ 啤酒名稱自動填入（根據品牌過濾）
- ✅ Livewire 即時搜尋機制

**缺少的功能**：
- ❌ 購入店家欄位
- ❌ Shop 模型和 shops 資料表
- ❌ 店家自動填入功能
- ❌ TastingLog 與 Shop 的關聯

**待改善的問題**：
- ⚠️ 部分 placeholder 文字未使用多語言翻譯
- ⚠️ 自動填入查詢效能可優化（使用 `LIKE '%value%'`）

**Gap**: 需要新增完整的店家管理架構，並改善語系支援。

---

## 💡 Planning

### Approach Analysis

#### Option A: 店家關聯設計 - 外鍵關聯 [✅ CHOSEN]

**設計**：
- 建立獨立的 `shops` 資料表
- `tasting_logs.shop_id` 外鍵關聯到 `shops.id`
- 使用 `nullable()` 和 `onDelete('set null')`

**Pros**:
- 符合 Laravel 最佳實踐和正規化設計
- 資料一致性高，避免重複資料
- 可建立索引優化自動填入查詢效能
- 未來易於擴展（如店家詳細資訊、地址、評分）
- 便於統計分析（如「哪家店買最多啤酒」）

**Cons**:
- 需要額外建立資料表和 Model
- 需要額外的 Migration 和測試

#### Option B: 店家關聯設計 - JSON 欄位 [❌ REJECTED]

**設計**：
```php
$table->json('shop_info')->nullable();  // {"name": "全聯", "location": "台北"}
```

**Pros**:
- 實作簡單快速
- 單一欄位包含所有店家資訊

**Cons**:
- 無法建立有效索引，自動填入效能差
- 資料重複，無法統一管理店家資訊
- 難以統計分析
- JSON 查詢語法複雜

#### Option C: 店家關聯設計 - 簡單文字欄位 [❌ REJECTED]

**設計**：
```php
$table->string('shop_name')->nullable();
```

**Pros**:
- 最簡單的實作方式
- 不需要額外資料表

**Cons**:
- 無法避免重複和拼寫錯誤（如 "全聯" vs "全聯福利中心"）
- 未來擴展困難
- 無法提供準確的自動填入建議
- 統計分析困難

**Decision Rationale**:

選擇 Option A（外鍵關聯）是因為：
1. **資料完整性**：外鍵約束確保參照完整性
2. **效能優化**：可在 `shops.name` 建立索引，加速自動填入查詢
3. **可維護性**：集中管理店家資料，避免重複和拼寫錯誤
4. **可擴展性**：未來可輕鬆新增店家詳細資訊
5. **符合現有架構**：與 Brand 和 Beer 的設計模式一致

### Design Decisions

#### D1: 資料庫 Schema 設計
- **Options**: 外鍵關聯 vs JSON 欄位 vs 文字欄位
- **Chosen**: 外鍵關聯
- **Reason**: 最符合 Laravel 最佳實踐，效能和擴展性最佳
- **Trade-offs**: 需要額外的資料表和 Migration，但換來更好的資料結構

#### D2: Shop 資料架構設計 - 混合方案
- **Options**:
  - A: 只有 tasting_logs.shop_id（簡單但查詢困難）
  - B: 只有 beer_shop 多對多（無個人記錄）
  - C: 混合方案 - beer_shop + tasting_logs.shop_id（完整）
- **Chosen**: C - 混合方案
- **Reason**:
  - **beer_shop（眾包資料）**：記錄「哪些店家有賣這瓶啤酒」，所有用戶共享
  - **tasting_logs.shop_id（個人記錄）**：記錄「我這次在哪買的」，支援歷史記錄
  - 解決不同用戶在不同店家購買同一啤酒的情況
  - 查詢「某啤酒可以在哪買」簡單快速（`$beer->shops`）
  - 查詢「我的購買歷史」也很簡單（`$tastingLog->shop`）
  - 支援信心度機制（report_count）
- **Trade-offs**: 資料庫結構稍複雜，但查詢和使用體驗大幅改善

#### D3: 自動填入查詢優化
- **Options**: `LIKE '%value%'` vs `LIKE 'value%'` vs 全文檢索
- **Chosen**: 先使用 `LIKE 'value%'`（前綴匹配）
- **Reason**:
  - 可使用索引，效能較好
  - 實作簡單，符合一般使用情境
  - 未來可升級為 Laravel Scout
- **Trade-offs**: 無法搜尋中間字元，但對於店家名稱通常不需要

#### D4: 語系翻譯實作方式
- **Options**: JSON 檔案 vs PHP 檔案 vs 資料庫
- **Chosen**: JSON 檔案（`lang/en.json`, `lang/zh-TW.json`）
- **Reason**:
  - Laravel 12 推薦做法
  - 簡潔易維護
  - 效能好
- **Trade-offs**: 無法動態新增翻譯（但本專案不需要）

#### D5: 店家欄位在表單中的位置
- **Options**: 在 Brand 之後 vs 在 Beer Name 之後 vs 在 Tasting Note 之後
- **Chosen**: 在 Tasting Note 之後
- **Reason**:
  - 邏輯上屬於「品嘗相關資訊」
  - 與 Tasting Note 一起，形成完整的品嘗記錄
  - 必填欄位（Brand, Beer Name）在前，選填欄位在後
- **Trade-offs**: 無

---

## 🔄 TDD 開發流程

### 為什麼採用 TDD？

本次實作採用 **Test-Driven Development (TDD)** 方法論，遵循「紅-綠-重構」循環：

```
🔴 紅燈（Red）      撰寫測試，測試失敗
   ↓
🟢 綠燈（Green）    實作功能，測試通過
   ↓
🔵 重構（Refactor）  優化程式碼，測試持續通過
   ↓
   回到紅燈（下一個功能）
```

### TDD 的優勢

1. **需求明確**：先寫測試，強迫思考功能需求
2. **設計驅動**：測試驅動 API 設計，程式碼更易用
3. **即時回饋**：每次修改都能立即知道是否破壞功能
4. **重構安全**：有測試保護，可放心優化
5. **文檔功能**：測試即文檔，展示如何使用功能

### 本專案的 TDD 流程

#### Phase 1-2: 準備基礎設施
```bash
# 建立資料庫和 Model（這是必要的基礎）
php artisan make:model Shop -m
php artisan migrate
```

#### Phase 3: 🔴 紅燈階段 - 撰寫測試

**目標**：定義期望的行為，測試應該全部失敗

```php
// tests/Feature/CreateBeerWithShopTest.php
public function test_can_create_beer_with_new_shop(): void
{
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateBeer::class)
        ->set('brand_name', 'Asahi')
        ->set('name', 'Super Dry')
        ->set('shop_name', '全聯福利中心')  // 這個功能還不存在
        ->call('save');

    $this->assertDatabaseHas('shops', ['name' => '全聯福利中心']);
    // ❌ 失敗：CreateBeer 沒有 shop_name 屬性
}
```

**執行測試**：
```bash
php artisan test --filter=CreateBeerWithShop
# 預期結果：全部失敗 ❌（這是好事！）
```

#### Phase 4: 🟢 綠燈階段 - 實作功能

**目標**：實作最小可行功能，讓測試通過

```php
// app/Livewire/CreateBeer.php
public $shop_name = '';  // 新增屬性

public function save()
{
    // 處理店家邏輯
    $shop = null;
    if (!empty($this->shop_name)) {
        $shop = Shop::firstOrCreate(['name' => trim($this->shop_name)]);
    }

    // 建立 TastingLog 時加入 shop_id
    TastingLog::create([
        'shop_id' => $shop?->id,
        // ...
    ]);
}
```

**執行測試**：
```bash
php artisan test --filter=CreateBeerWithShop
# 預期結果：測試開始通過 ✅
```

#### Phase 5: 前端視圖

**繼續實作**，讓更多測試通過：

```php
// resources/views/livewire/create-beer.blade.php
<x-text-input wire:model.debounce.300ms="shop_name" />
```

**執行測試**：
```bash
php artisan test
# 預期結果：所有測試通過 ✅
```

#### Phase 7: 🔵 重構階段 - 優化效能

**目標**：優化程式碼，測試持續通過

```php
// 優化前
->where('name', 'like', '%' . $value . '%')  // 無法使用索引

// 優化後
->where('name', 'like', $value . '%')        // 可使用索引
->limit(10)
```

**執行測試**：
```bash
php artisan test
# 預期結果：測試仍然全部通過 ✅（確保優化沒有破壞功能）
```

### TDD 關鍵原則

1. **先寫測試，後寫程式碼**
   - ❌ 不要先寫功能再補測試
   - ✅ 先寫測試，用測試驅動設計

2. **只寫必要的程式碼**
   - ❌ 不要過度設計（"未來可能需要..."）
   - ✅ 只實作讓測試通過的最小程式碼

3. **紅燈是正常的**
   - ❌ 不要因為測試失敗而沮喪
   - ✅ 紅燈表示你正確地定義了需求

4. **持續執行測試**
   - 每次修改後都執行測試
   - 確保沒有破壞現有功能

5. **重構是安全的**
   - 有測試保護，可放心優化
   - 重構時測試應該持續通過

### 實作檢查點

在每個 Phase 結束時，執行測試確認狀態：

| Phase | 測試狀態 | 說明 |
|-------|---------|------|
| Phase 1-2 | N/A | 準備基礎設施，尚未撰寫測試 |
| Phase 3 | ❌ 全部失敗 | 正常！測試定義了期望行為 |
| Phase 4 | 🟡 部分通過 | Livewire 功能實作後開始通過 |
| Phase 5 | 🟢 大部分通過 | 前端完成後更多測試通過 |
| Phase 6 | 🟢 全部通過 | 語系翻譯完成，所有功能完整 |
| Phase 7 | 🟢 持續通過 | 優化後測試仍然通過 |

---

## ✅ Implementation Checklist

### Phase 1: 資料庫層準備 [⏳ Pending]

#### 1a. 建立 Shop Model 和資料表
- [ ] 建立 Shop Model (`php artisan make:model Shop -m`)
- [ ] 編輯 shops migration：
  - [ ] `id` (primary key)
  - [ ] `name` (string, index)
  - [ ] `timestamps`, `softDeletes`

#### 1b. 建立 beer_shop 多對多關聯（眾包資料）
- [ ] 建立 beer_shop migration (`php artisan make:migration create_beer_shop_table`)
- [ ] 編輯 beer_shop migration：
  - [ ] `id` (primary key)
  - [ ] `beer_id` (foreign key)
  - [ ] `shop_id` (foreign key)
  - [ ] `first_reported_at` (timestamp, nullable)
  - [ ] `last_reported_at` (timestamp, nullable)
  - [ ] `report_count` (unsigned integer, default 1)
  - [ ] `timestamps`
  - [ ] `unique(['beer_id', 'shop_id'])`
  - [ ] indexes: `beer_id`, `shop_id`, `report_count`

#### 1c. 修改 tasting_logs 加入 shop_id（個人記錄）
- [ ] 建立 tasting_logs 關聯 migration（`add_shop_id_to_tasting_logs_table`）
- [ ] 編輯 migration：
  - [ ] `shop_id` (foreign key, nullable)
  - [ ] `onDelete('set null')`

#### 1d. 執行 migrations
- [ ] 執行 migrations（`php artisan migrate`）
- [ ] 驗證三個資料表結構正確：
  - [ ] `shops` - 店家資料
  - [ ] `beer_shop` - 多對多關聯
  - [ ] `tasting_logs` - 已新增 shop_id

### Phase 2: Model 層調整 [⏳ Pending]

#### 2a. 完善 Shop Model
- [ ] 新增 `$fillable = ['name']`
- [ ] 新增 `use SoftDeletes`
- [ ] 新增 `beers()` 多對多關聯（返回 BelongsToMany）
- [ ] 新增 `tastingLogs()` HasMany 關聯

#### 2b. 調整 Beer Model
- [ ] 新增 `shops()` 多對多關聯（返回 BelongsToMany）
- [ ] 配置 pivot 欄位：`withPivot(['first_reported_at', 'last_reported_at', 'report_count'])`

#### 2c. 調整 TastingLog Model
- [ ] 新增 `shop_id` 到 `$fillable`
- [ ] 新增 `shop()` BelongsTo 關聯

#### 2d. 驗證關聯
- [ ] 使用 Tinker 驗證：
  - [ ] `$beer->shops` 能取得相關店家
  - [ ] `$shop->beers` 能取得相關啤酒
  - [ ] `$tastingLog->shop` 能取得購買地點
  - [ ] Pivot 欄位 `report_count` 正確

### Phase 3: 測試撰寫（TDD 紅燈階段）[⏳ Pending]

#### 3a. 準備測試基礎設施
- [ ] 建立 ShopFactory（`php artisan make:factory ShopFactory`）
- [ ] 配置 ShopFactory：生成隨機店家名稱

#### 3b. 撰寫眾包資料相關測試（beer_shop）
- [ ] Test：新增啤酒 + 新店家 → beer_shop 應自動建立（Phase 3 失敗 ❌，Phase 4 後通過 ✅）
  - 失敗原因：`syncBeerShop()` 方法還沒實作
- [ ] Test：新增啤酒 + 現有店家 → beer_shop 的 report_count 應增加（Phase 3 失敗 ❌，Phase 4 後通過 ✅）
  - 失敗原因：多對多同步邏輯還沒實作
- [ ] Test：同一啤酒 + 不同店家 → 應建立多個 beer_shop 記錄（Phase 3 失敗 ❌，Phase 4 後通過 ✅）
  - 失敗原因：Beer Model 還沒有 `shops()` 關聯
- [ ] Test：不同用戶同時記錄相同啤酒相同店家 → beer_shop report_count 應正確累加（Phase 3 失敗 ❌，Phase 4 後通過 ✅）
  - 失敗原因：Pivot 更新邏輯還沒實作

#### 3c. 撰寫個人記錄相關測試（tasting_logs.shop_id）
- [ ] Test：新增啤酒 + 選擇店家 → tasting_log.shop_id 應記錄（Phase 3 失敗 ❌）
  - 最終實作後應該 ✅ 通過
- [ ] Test：新增啤酒 + 不選擇店家（Skip 階段二） → tasting_log.shop_id 應為 null（Phase 3 失敗 ❌）
  - 最終實作後應該 ✅ 通過（這是正常的成功情況）
  - 失敗原因：`skip()` 方法還沒實作
- [ ] Test：同一啤酒多次品嘗 + 不同店家 → 每筆 tasting_log 應記錄不同店家（Phase 3 失敗 ❌）
  - 最終實作後應該 ✅ 通過

#### 3d. 撰寫自動填入建議測試
- [ ] Test：店家自動填入建議（Phase 3 失敗 ❌）
  - 最終實作後應該 ✅ 通過
- [ ] Test：建議按 report_count 排序（Phase 3 失敗 ❌）
  - 最終實作後應該 ✅ 通過

#### 3e. 執行測試並確認紅燈
- [ ] 執行測試：`php artisan test --filter=CreateBeerWithShop`
- [ ] 確認全部失敗（紅燈 ❌）

**TDD 重點**：
此階段測試應該全部失敗，是因為功能還沒實作。「Phase 3 失敗」≠「最終會失敗」，而是「現在會失敗，因為程式碼還沒寫」。

當進入 Phase 4-5 實作功能後，這些測試會逐漸變綠 ✅。

### Phase 4: Livewire Component 調整（TDD 綠燈階段）[⏳ Pending]

#### 4a. 新增屬性
- [ ] `$currentStep = 1`（用於兩階段流程）
- [ ] `$brand_name = ''`（已存在，確認保留）
- [ ] `$name = ''`（已存在，確認保留）
- [ ] `$shop_name = ''`（新增）
- [ ] `$note = ''`（新增）
- [ ] `$brand_suggestions = []`（已存在，確認保留）
- [ ] `$beer_suggestions = []`（已存在，確認保留）
- [ ] `$shop_suggestions = []`（新增）

#### 4b. 實作兩階段流程邏輯
- [ ] 新增 `nextStep()` 方法：驗證第一階段資料後進入第二階段
- [ ] 新增 `skip()` 方法：跳過第二階段直接儲存
- [ ] 修改現有自動填入方法（updatedBrandName, updatedName）

#### 4c. 實作店家自動填入和同步邏輯
- [ ] 新增 `updatedShopName()` 方法：自動填入建議，按 report_count 排序
- [ ] 新增 `selectShop()` 方法：選擇店家
- [ ] 修改 `save()` 方法：
  - [ ] 驗證第一階段必填欄位（brand_name, name）
  - [ ] 驗證第二階段選填欄位（shop_name, note）
  - [ ] 建立/取得 Brand
  - [ ] 建立/取得 Beer
  - [ ] 如果填寫店家，建立/取得 Shop
  - [ ] **同步到 beer_shop**（核心邏輯）：
    - [ ] 如果是新的 (beer, shop) 組合 → attach with report_count=1
    - [ ] 如果已存在 → 更新 report_count 和 last_reported_at
  - [ ] 建立/更新 UserBeerCount
  - [ ] 建立 TastingLog（包含 shop_id）

#### 4d. 測試部分功能
- [ ] 執行測試：`php artisan test --filter=CreateBeerWithShop`
- [ ] 確認眾包資料相關測試開始通過（綠燈 ✅）
- [ ] 確認個人記錄相關測試開始通過（綠燈 ✅）

### Phase 5: 前端視圖調整（兩階段 UI）[⏳ Pending]

#### 5a. 階段一視圖（必填）
- [ ] 修改 `create-beer.blade.php`：
  - [ ] 新增條件判斷 `@if($currentStep === 1)`
  - [ ] 顯示品牌欄位（自動填入）
  - [ ] 顯示啤酒名稱欄位（自動填入）
  - [ ] 新增「Next Step」按鈕（觸發 `nextStep()`）

#### 5b. 階段二視圖（選填）
- [ ] 修改 `create-beer.blade.php`：
  - [ ] 新增條件判斷 `@if($currentStep === 2)`
  - [ ] 顯示購買店家欄位（自動填入，按 report_count 排序）
  - [ ] 顯示品嘗筆記欄位
  - [ ] 新增「← Back」按鈕（回到第一階段）
  - [ ] 新增「Skip」按鈕（跳過直接儲存，調用 `skip()`）
  - [ ] 新增「Save」按鈕（完整儲存，調用 `save()`）

#### 5c. UI 優化
- [ ] 複製自動填入建議清單樣式（參考品牌和啤酒）
- [ ] 店家建議顯示 report_count（信心度指示）
- [ ] 視覺驗證兩個階段的切換
- [ ] 測試所有操作流程（complete, skip, back）

#### 5d. 執行測試
- [ ] 執行測試：`php artisan test --filter=CreateBeerWithShop`
- [ ] 確認 UI 相關測試通過（更多綠燈 ✅）

### Phase 6: 語系翻譯 [⏳ Pending]
- [ ] 更新 `lang/en.json`：
  - [ ] "Brand", "Beer Name", "Style", "Tasting Note", "Where to Buy?"
  - [ ] "Find Your Beer", "Tasting Details"
  - [ ] "Next Step", "Skip", "Save", "Back"
- [ ] 更新 `lang/zh-TW.json`：
  - [ ] "品牌", "啤酒名稱", "風格", "品嘗筆記", "購入店家"
  - [ ] "找到你的啤酒", "品嘗細節"
  - [ ] "下一步", "跳過", "儲存", "返回"
- [ ] 修正 `create-beer.blade.php` 所有硬編碼 placeholder
- [ ] 測試切換語系（繁體、英文）
- [ ] 驗證 `{{ __('key') }}` 正確運作

### Phase 7: 效能優化（TDD 重構階段）[⏳ Pending]
- [ ] 將 Brand 自動填入改為 `LIKE 'value%'`（前綴匹配）
- [ ] 將 Beer 自動填入改為 `LIKE 'value%'`
- [ ] 將 Shop 自動填入使用 `LIKE 'value%'`
- [ ] 所有自動填入查詢加入 `limit(10)`
- [ ] 使用 Laravel Debugbar 驗證查詢效能
- [ ] 執行測試確認優化後測試仍全部通過（保持綠燈 ✅）

**TDD 重點**：重構時測試應該持續通過，確保優化沒有破壞功能。

### Phase 8: 文檔更新 [⏳ Pending]
- [ ] 更新 OpenAPI spec（`/spec/api/api.yaml`）新增 Shop schema
- [ ] 更新 TastingLog schema 包含 `shop_id` 和 `shop` 關聯
- [ ] 更新 README 或 CHANGELOG 記錄功能新增

---

## 🚧 Blockers & Solutions

### Blocker 1: 現有 TastingLog 資料相容性 [✅ RESOLVED]
- **Issue**: 現有的 TastingLog 沒有 `shop_id`，新增欄位後會是 `null`
- **Impact**: 可能影響顯示邏輯
- **Solution**:
  - Migration 使用 `nullable()`，舊資料自動為 `null`
  - 前端顯示時處理 null 情況：`{{ $log->shop?->name ?? __('Not recorded') }}`
  - 使用 `onDelete('set null')` 確保刪除店家不影響記錄
- **Resolved**: 規劃階段已考慮並設計解決方案

### Blocker 2: 效能考量 - 三個欄位自動填入 [✅ RESOLVED]
- **Issue**: 品牌、啤酒、店家三個欄位都需要即時查詢資料庫
- **Impact**: 可能影響使用者體驗（延遲）
- **Solution**:
  - 使用前綴匹配 `LIKE 'value%'` 可使用索引
  - 加入 `limit(10)` 限制結果數量
  - 在 `shops.name` 建立索引
  - 短期內影響不大（資料量 < 10,000）
- **Resolved**: 規劃階段已設計優化方案

---

## 📊 Outcome

### What Was Built
待實作後填寫

### Files Created/Modified
```
HoldYourBeer/
├── app/
│   ├── Models/
│   │   ├── Shop.php (new)
│   │   └── TastingLog.php (modified)
│   └── Livewire/
│       └── CreateBeer.php (modified)
├── database/
│   ├── factories/
│   │   └── ShopFactory.php (new)
│   └── migrations/
│       ├── YYYY_MM_DD_HHMMSS_create_shops_table.php (new)
│       └── YYYY_MM_DD_HHMMSS_add_shop_id_to_tasting_logs_table.php (new)
├── resources/
│   └── views/
│       └── livewire/
│           └── create-beer.blade.php (modified)
├── lang/
│   ├── en.json (modified)
│   └── zh-TW.json (modified)
├── tests/
│   └── Feature/
│       └── CreateBeerWithShopTest.php (new)
├── spec/
│   └── api/
│       └── api.yaml (modified)
└── docs/
    └── sessions/2025-12/
        └── 16-beer-creation-autocomplete-enhancement.md (this file)
```

### Metrics
待實作後填寫

---

## 🎓 Lessons Learned

### 1. 外鍵關聯 vs JSON 欄位的選擇

**Learning**: 對於需要自動填入建議的欄位，應該使用獨立的資料表而非 JSON 欄位。

**Reason**:
- JSON 欄位無法建立索引，查詢效能差
- 資料重複，難以統一管理
- 統計分析困難

**Solution/Pattern**: 使用外鍵關聯 + 索引優化

**Future Application**: 未來如有類似需求（如啤酒風格、評分標籤），都應考慮獨立資料表。

### 2. 向後相容性設計原則

**Learning**: 新增欄位時必須考慮現有資料的相容性。

**Solution/Pattern**:
- 使用 `nullable()` 讓舊資料自動為 `null`
- 使用 `onDelete('set null')` 避免刪除關聯資料導致錯誤
- 使用 SoftDeletes 避免意外刪除

**Future Application**: 所有新增的關聯欄位都應遵循此原則。

### 3. 自動填入效能優化

**Learning**: `LIKE '%value%'` 無法使用索引，應改用 `LIKE 'value%'`。

**Pattern**:
```php
// ❌ 無法使用索引
->where('name', 'like', '%' . $value . '%')

// ✅ 可使用索引（前綴匹配）
->where('name', 'like', $value . '%')
->limit(10)
```

**Future Application**: 所有自動填入功能都應使用前綴匹配。

### 4. Livewire 屬性命名一致性

**Learning**: Livewire 組件的屬性命名應保持一致性。

**Current Pattern**:
- 輸入欄位：`$brand_name`, `$name`, `$shop_name`
- 建議清單：`$brand_suggestions`, `$beer_suggestions`, `$shop_suggestions`
- 更新方法：`updatedBrandName()`, `updatedName()`, `updatedShopName()`
- 選擇方法：`selectBrand()`, `selectBeer()`, `selectShop()`

**Future Application**: 新增自動填入欄位時遵循此命名模式。

### 5. 混合方案：眾包資料 + 個人記錄

**Learning**: 對於「購買地點」這類資訊，簡單的關聯設計不足以支撐複雜查詢需求，需要採用「混合方案」同時保留眾包資料和個人記錄。

**混合方案的核心概念**:
- **beer_shop（多對多）**：累積所有用戶的回報，回答「這瓶啤酒可以在哪買？」
- **tasting_logs.shop_id**：個人購買歷史，回答「我在哪些地方買過這瓶啤酒？」

**支援的查詢場景**:
1. ✅ 「Asahi 可以在哪些店家買到？」→ `$beer->shops` + `report_count`
2. ✅ 「我買過這瓶啤酒在哪些地方？」→ `$beer->tastingLogs()->where('user_id', ...)`
3. ✅ 「全聯有哪些熱門啤酒？」→ `$shop->beers()->withCount()`
4. ✅ 「不同用戶在不同店家購買同一啤酒」→ 完美支援

**設計亮點**:
- **信心度機制**：`report_count` 代表有多少人回報此 (beer, shop) 組合，自動排序
- **自動同步**：新增品嘗時自動同步到 beer_shop，無需額外操作
- **資料一致性**：使用外鍵和 unique constraint 確保資料完整

**Pattern**:
```
對於「可多人回報、需要聚合統計」的資訊：
→ 使用多對多 pivot table（眾包資料）

對於「個人的、有時間序列」的資訊：
→ 使用 nullable 外鍵（個人記錄）

兩者結合 = 完整、強大、可查詢
```

**Future Application**: 未來如有類似需求（如「評分」、「推薦度」），可考慮採用混合方案。

### 6. TDD 驅動開發的價值

**Learning**: 採用 TDD 方法論，先寫測試再實作功能，可以提升程式碼品質和設計。

**TDD 帶來的好處**:
- **需求明確化**：撰寫測試時強迫思考「功能應該如何運作」
- **設計改善**：測試驅動 API 設計，讓介面更易用
- **信心提升**：有測試保護，可以放心重構優化
- **文檔作用**：測試展示如何使用功能，比註解更可靠
- **即時回饋**：每次修改都能立即知道是否破壞功能

**TDD 流程**:
```
Phase 1-2: 準備基礎設施（資料庫 + Model）
    ↓
Phase 3: 🔴 撰寫測試（紅燈，測試失敗）
    ↓
Phase 4-5: 🟢 實作功能（綠燈，測試通過）
    ↓
Phase 7: 🔵 優化重構（測試持續通過）
```

**關鍵原則**:
- 先寫測試，後寫程式碼（不是反過來）
- 測試失敗是正常的（紅燈表示正確定義需求）
- 只寫讓測試通過的最小程式碼（避免過度設計）
- 重構時測試應持續通過（確保優化沒有破壞功能）

**Future Application**: 所有新功能開發都應遵循 TDD 流程，特別是複雜的業務邏輯。符合專案的開發哲學「可測試性 > 可讀性 > 一致性 > 簡單性」。

---

## ✅ Completion

**Status**: 🔄 In Progress
**Completed Date**: 待完成
**Session Duration**: 待完成

> ℹ️ **Next Steps**: 詳見 [Session Guide](GUIDE.md)
> 1. 更新上方狀態與日期
> 2. 根據 Tags 更新 INDEX 檔案
> 3. 運行 `./scripts/archive-session.sh`

---

## 🔮 Future Improvements

### Not Implemented (Intentional)
- ⏳ **進階搜尋功能**: 未實作模糊比對或智慧建議（如 "全聯" 能找到 "全聯福利中心"）
  - Reason: 先觀察用戶行為，再決定是否需要

- ⏳ **店家詳細資訊**: 未實作地址、電話、營業時間等
  - Reason: 目前只需要店家名稱即可滿足需求

### Potential Enhancements
- 📌 **店家評分或筆記**: 讓用戶對店家留下評價
- 📌 **統計分析**: 顯示「最常購買的店家」、「各店家啤酒種類分佈」
- 📌 **店家地圖整合**: 在地圖上標示購買地點
- 📌 **價格追蹤**: 記錄在不同店家的購買價格
- 📌 **附近店家推薦**: 根據地理位置推薦附近的店家
- 📌 **Laravel Scout 整合**: 如果資料量大，升級為全文檢索

### Technical Debt
- 🔧 **現有品牌大小寫不一致**: 可能有 "Suntory" 和 "suntory" 共存
  - 需要資料清理和統一化
  - 可考慮實作 Accessor/Mutator 統一處理

- 🔧 **N+1 查詢檢查**: 需確保所有顯示 TastingLog 的地方使用 `with('shop')`
  - 建議在 TastingLog Model 加入 Global Scope 或使用 `$with` 屬性

---

## 🔗 References

### Related Work
- [Session: 品牌 CRUD 管理](2025-11/20-brand-crud-management.md) - 品牌管理功能實作參考
- [Session: 密碼驗證規則強化](16-password-validation-enhancement.md) - Session 文檔範本參考

### Laravel 文件
- [Eloquent Relationships](https://laravel.com/docs/11.x/eloquent-relationships)
- [Database Migrations](https://laravel.com/docs/11.x/migrations)
- [Livewire Properties](https://livewire.laravel.com/docs/properties)
- [Localization](https://laravel.com/docs/11.x/localization)

### 設計參考
- [Laravel Scout](https://laravel.com/docs/11.x/scout) - 全文檢索解決方案
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - 效能監控工具

### 最佳實踐
- [Database Indexing Best Practices](https://use-the-index-luke.com/)
- [LIKE Query Optimization](https://www.percona.com/blog/optimizing-queries-mysql-like-operator/)

---

## 📝 Technical Details

### 資料庫 Schema

#### shops 資料表
```sql
CREATE TABLE shops (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_name (name)
);
```

#### tasting_logs 新增欄位
```sql
ALTER TABLE tasting_logs
ADD COLUMN shop_id BIGINT UNSIGNED NULL AFTER note,
ADD CONSTRAINT fk_tasting_logs_shop_id
    FOREIGN KEY (shop_id)
    REFERENCES shops(id)
    ON DELETE SET NULL;
```

### Model 關聯定義

```php
// Beer.php - 多對多關聯（眾包資料）
public function shops(): BelongsToMany
{
    return $this->belongsToMany(Shop::class)
                ->withPivot(['first_reported_at', 'last_reported_at', 'report_count'])
                ->withTimestamps();
}

// Shop.php
public function beers(): BelongsToMany
{
    return $this->belongsToMany(Beer::class)
                ->withPivot(['first_reported_at', 'last_reported_at', 'report_count'])
                ->withTimestamps();
}

public function tastingLogs(): HasMany
{
    return $this->hasMany(TastingLog::class);
}

// TastingLog.php - 個人記錄
public function shop(): BelongsTo
{
    return $this->belongsTo(Shop::class);
}
```

### 業務邏輯核心 - 同步 beer_shop

```php
// CreateBeer.php 中的 save() 方法的核心部分
protected function syncBeerShop(Beer $beer, Shop $shop): void
{
    // 檢查 (beer, shop) 組合是否已存在
    $exists = $beer->shops()
        ->where('shop_id', $shop->id)
        ->exists();

    if (!$exists) {
        // 第一次記錄這個 (beer, shop) 組合
        $beer->shops()->attach($shop->id, [
            'first_reported_at' => now(),
            'last_reported_at' => now(),
            'report_count' => 1,
        ]);
    } else {
        // 已存在，更新回報資訊（信心度提升）
        $beer->shops()->updateExistingPivot($shop->id, [
            'last_reported_at' => now(),
            'report_count' => DB::raw('report_count + 1'),
        ]);
    }
}
```

### Livewire 自動填入實作

```php
// CreateBeer.php
public function updatedShopName($value)
{
    if (strlen($value) < 2) {
        $this->shop_suggestions = [];
        return;
    }

    // 獲取建議，按 report_count 排序（信心度排序）
    $this->shop_suggestions = Shop::where('name', 'like', $value . '%')
        ->withCount(['beers as total_appearances' => function($query) {
            // 可選：計算該店家出現在多少種啤酒中
        }])
        ->orderByDesc('total_appearances')  // 先顯示出現最多的店家
        ->limit(10)
        ->get()
        ->map(function($shop) {
            return [
                'id' => $shop->id,
                'name' => $shop->name,
                'appearances' => $shop->total_appearances ?? 0,
            ];
        })
        ->toArray();
}

public function selectShop($name)
{
    $this->shop_name = $name;
    $this->shop_suggestions = [];
}
```

### 查詢範例 - 眾包資料和個人記錄

#### 查詢 1：某啤酒可以在哪買？（眾包資料 - beer_shop）
```php
$beer = Beer::with(['shops' => function($query) {
    // 按信心度（回報次數）排序
    $query->orderByDesc('report_count');
}])->find($beerId);

// 顯示所有販售地點
foreach ($beer->shops as $shop) {
    echo "{$shop->name}";
    echo " (" . $shop->pivot->report_count . " 人回報)";
    echo " [最後更新: " . $shop->pivot->last_reported_at->diffForHumans() . "]\n";
}

// 輸出範例：
// 全聯福利中心 (25 人回報) [最後更新: 2 days ago]
// 家樂福 (18 人回報) [最後更新: 1 week ago]
// 7-11 (12 人回報) [最後更新: 3 days ago]
```

#### 查詢 2：我的品嘗記錄（個人記錄 - tasting_logs.shop_id）
```php
$userBeerCount = UserBeerCount::with([
    'tastingLogs' => function($query) {
        $query->with('shop')->orderByDesc('tasted_at');
    }
])
->where('user_id', Auth::id())
->where('beer_id', $beerId)
->first();

// 顯示個人品嘗歷史（包含購買地點）
foreach ($userBeerCount->tastingLogs as $log) {
    echo "{$log->tasted_at->format('Y-m-d H:i')}\n";
    echo "購買地點: " . ($log->shop?->name ?? '未記錄') . "\n";
    echo "筆記: " . ($log->note ?: '無') . "\n\n";
}

// 輸出範例：
// 2025-02-15 18:30
// 購買地點: 家樂福
// 筆記: 順口好喝
//
// 2025-02-01 20:00
// 購買地點: 全聯
// 筆記: 很棒的啤酒
//
// 2025-01-15 19:30
// 購買地點: 未記錄
// 筆記: 無
```

#### 查詢 3：店家熱門啤酒
```php
$shop = Shop::where('name', '全聯')->first();

$popularBeers = $shop->beers()
    ->withCount(['userBeerCounts as total_tastings' => function($query) {
        $query->select(DB::raw('sum(count)'));
    }])
    ->orderByDesc('total_tastings')
    ->limit(10)
    ->get();

// 顯示店家的熱門啤酒
foreach ($popularBeers as $beer) {
    echo "{$beer->brand->name} {$beer->name}";
    echo " ({$beer->total_tastings} 次品嘗)\n";
}

// 輸出範例：
// Asahi Super Dry (1,250 次品嘗)
// Kirin Ichiban (980 次品嘗)
// Sapporo Premium (756 次品嘗)
```

#### 查詢 4：我可以在哪些店家買到某啤酒？（個人視角）
```php
$beer = Beer::find($beerId);
$myTastingLocations = $beer->tastingLogs()
    ->whereHas('userBeerCount', function($query) {
        $query->where('user_id', Auth::id());
    })
    ->with('shop')
    ->distinct('shop_id')
    ->orderByDesc('tasted_at')
    ->get();

// 顯示我個人在哪些地點買過這瓶啤酒
echo "你在這些地點購買過 {$beer->brand->name} {$beer->name}:\n";
foreach ($myTastingLocations as $location) {
    echo "- " . ($location->shop?->name ?? '未記錄') . "\n";
}
```

### 前端自動填入 UI

```php
<div>
    <x-input-label for="shop_name" :value="__('Purchase Location (Optional)')" />
    <x-text-input
        id="shop_name"
        type="text"
        class="mt-1 block w-full"
        wire:model.debounce.300ms="shop_name"
        placeholder="{{ __('Enter shop name...') }}"
    />
    @if(count($shop_suggestions) > 0)
        <ul class="mt-2 bg-gray-50 border border-gray-200 rounded-md shadow-sm max-h-40 overflow-y-auto">
            @foreach($shop_suggestions as $suggestion)
                <li
                    wire:click="selectShop('{{ $suggestion['name'] }}')"
                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
                >
                    {{ $suggestion['name'] }}
                </li>
            @endforeach
        </ul>
    @endif
</div>
```

---

## 🔍 Implementation Guide

### 資料完整性檢查清單

完成實作後，請確認以下項目：

**資料庫層**
- [ ] `shops` 資料表已建立
- [ ] `shops.name` 欄位有索引
- [ ] `tasting_logs.shop_id` 欄位已新增且為 nullable
- [ ] 外鍵約束正確設定 (`onDelete('set null')`)
- [ ] Migration 可正確 rollback

**Model 層**
- [ ] Shop Model 已建立且有 SoftDeletes
- [ ] Shop Model 有 `tastingLogs()` 關聯
- [ ] TastingLog Model `$fillable` 包含 `shop_id`
- [ ] TastingLog Model 有 `shop()` 關聯

**Livewire Component**
- [ ] `$shop_name` 和 `$shop_suggestions` 屬性已新增
- [ ] `updatedShopName()` 方法正確實作
- [ ] `selectShop()` 方法正確實作
- [ ] `save()` 方法處理店家邏輯
- [ ] Validation rules 包含 `shop_name`

**前端視圖**
- [ ] 店家欄位在正確位置（Tasting Note 之後）
- [ ] 自動填入建議清單正確顯示
- [ ] 所有 placeholder 使用 `__()` 翻譯
- [ ] 欄位標示為 "(選填)"

**語系翻譯**
- [ ] `en.json` 包含所有新 key
- [ ] `zh-TW.json` 包含所有新 key
- [ ] 切換語系測試通過

**測試**
- [ ] Shop Factory 已建立
- [ ] 測試涵蓋「新增啤酒 + 新店家」
- [ ] 測試涵蓋「新增啤酒 + 現有店家」
- [ ] 測試涵蓋「新增啤酒 + 不填店家」
- [ ] 測試涵蓋「自動填入建議」
- [ ] 所有測試通過

**效能與安全**
- [ ] 使用前綴匹配 `LIKE 'value%'`
- [ ] 查詢有 `limit(10)` 限制
- [ ] 沒有 N+1 查詢問題
- [ ] 沒有 SQL Injection 風險

**向後相容性**
- [ ] 現有 TastingLog 資料不受影響
- [ ] 現有測試全部通過
- [ ] API 回應格式相容（如果有）

---

## 🔮 Future Improvements

### Not Implemented (Intentional)
- ⏳ **進階搜尋功能**: 未實作模糊比對或智慧建議（如 "全聯" 能找到 "全聯福利中心"）
  - Reason: 先觀察用戶行為，再決定是否需要

- ⏳ **店家詳細資訊**: 未實作地址、電話、營業時間等
  - Reason: MVP 階段只需要店家名稱即可滿足需求

- ⏳ **修改或刪除購買地點**: 未實作修改已記錄的購買地點功能
  - Reason: 作為歷史紀錄，應該保持不變以維持資料完整性

### Potential Enhancements
- 📌 **店家評分或筆記**: 讓用戶對店家留下評價（使用類似 beer_shop 的 pivot table）
- 📌 **統計分析頁面**:
  - 「最常購買的店家」分析
  - 「各店家啤酒種類分佈」
  - 「店家購買頻率趨勢」
- 📌 **店家詳情頁面**: 顯示該店家的熱門啤酒、購買趨勢
- 📌 **店家地圖整合**: 在地圖上標示購買地點、距離提示
- 📌 **價格追蹤**: 在 beer_shop pivot table 加入 `price` 欄位
- 📌 **附近店家推薦**: 根據地理位置推薦附近的店家
- 📌 **Yelp/Google Places 整合**: 補充店家詳細資訊（營業時間、評分、地址）
- 📌 **Laravel Scout 整合**: 如果資料量大，升級為全文檢索
- 📌 **Smart Suggestions**: 根據用戶歷史購買地點自動推薦店家

### Technical Debt
- 🔧 **現有品牌大小寫不一致**: 可能有 "Suntory" 和 "suntory" 共存
  - 需要資料清理和統一化
  - 可考慮實作 Accessor/Mutator 統一處理

- 🔧 **N+1 查詢檢查**: 需確保所有顯示 TastingLog 的地方使用 `with('shop')`
  - 建議在 TastingLog Model 加入 Global Scope 或使用 `$with` 屬性

- 🔧 **兩階段流程記憶**: 目前未實作「記住用戶最後選擇的店家」功能
  - 未來可加入 local storage 或 user preference 記憶

---

## 📈 Success Metrics

### 功能完成指標

#### 資料結構層面
- ✅ `shops` 資料表已建立
- ✅ `beer_shop` 多對多關聯已建立（眾包資料）
- ✅ `tasting_logs.shop_id` 欄位已新增（個人記錄）
- ✅ 所有索引和外鍵約束正確

#### 功能層面
- ✅ 用戶可以在兩階段流程中新增啤酒：
  - 階段一（必填）：品牌 + 啤酒名稱
  - 階段二（選填）：購買店家 + 品嘗筆記（可跳過）
- ✅ 店家欄位提供自動填入建議（輸入 2 字元以上），按信心度排序
- ✅ 新增的店家立即成為其他用戶的選項（眾包資料同步）
- ✅ 不同用戶可以在不同店家購買同一啤酒，`report_count` 正確累加
- ✅ 個人品嘗記錄保留購買地點歷史（可查詢「我在哪些地方買過這瓶啤酒」）

#### 使用者體驗層面
- ✅ 兩階段 UI 清晰易用（Next Step, Skip, Back, Save 按鈕）
- ✅ 切換語系時所有文字正確翻譯（繁體、英文）
- ✅ 自動填入建議顯示信心度指示（report_count）
- ✅ 頁面載入時間 < 200ms（包含自動填入查詢）

#### 測試與品質層面
- ✅ 所有測試通過（眾包資料 + 個人記錄）
- ✅ 涵蓋率 > 80%
- ✅ 沒有 N+1 查詢問題
- ✅ 現有測試不受影響（向後相容）

### 使用者體驗指標（待觀察）
- 📊 兩階段流程完成率（有多少用戶會進入階段二）
- 📊 店家欄位填寫率（有多少比例的用戶會填寫店家）
- 📊 自動填入選中率（用戶是否使用建議，而非自己輸入完整名稱）
- 📊 店家資料重複率（是否有多個相似名稱的店家）
- 📊 信心度分佈（report_count 的分佈情況）

---

## 📌 Next Steps After Completion

1. **監控使用情況**
   - 使用 Laravel Telescope 監控自動填入查詢效能
   - 收集用戶回饋，了解功能是否滿足需求

2. **資料清理**
   - 定期檢查是否有重複或拼寫錯誤的店家名稱
   - 可考慮建立管理後台供管理員合併重複店家

3. **功能優化**
   - 根據用戶回饋評估是否需要進階功能
   - 如果資料量大，考慮升級為 Laravel Scout

4. **文檔維護**
   - 更新 README 說明新功能
   - 更新 OpenAPI spec 供前端開發者參考
