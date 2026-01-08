# Session: Fix Mobile Dashboard View

**Date**: 2026-01-08
**Status**: � Planning Complete
**Duration**: TBD
**Issue**: USER_REQUEST
**Contributors**: Claude AI
**Tags**: #ui, #mobile, #dashboard, #navigation, #bottom-sheet

---

## 📋 Overview

### Goal
Optimize the mobile dashboard view with the following improvements:
1. Remove footer from the dashboard page.
2. Add "HoldYourBeers" text button to the center of the navbar that navigates back to dashboard.
3. Reduce the vertical spacing between navbar and "My Beer Collection" section.
4. **Convert "Add" to Bottom Sheet Dialog** - 點擊 Beer Icon 後，從底部滑出表單，背景仍可看到 My Beer Collection。

---

## 🎯 Context

### Problem
The user reported several UX issues on the mobile dashboard:
1. The footer takes up vertical space unnecessarily on the dashboard.
2. There's no easy way to return to the dashboard from other pages (charts, profile).
3. There's excessive spacing between the navbar and the main content ("My Beer Collection").
4. **Adding a new beer requires a full page navigation, which feels "disconnected" from the collection.**

### Root Cause Analysis (問題 3: 為什麼距離這麼遠)
經過分析 `dashboard.blade.php`，發現距離由以下 CSS 屬性疊加：
- `pt-12` (Line 17) — 整個內容區的 padding-top 為 **48px**
- `mt-6` (Line 19) — 內部 container 的 margin-top 為 **24px**
- **Page Header** (Line 2-15) — 上方有 "Welcome, {name}" 的 header 區塊

總計約 **72px + header 高度** 的垂直空間。

### Solution
1. Conditionally hide the footer on the dashboard page.
2. Add a centered "HoldYourBeers" link/button in the navigation bar.
3. Reduce `pt-12` to `pt-4` and `mt-6` to `mt-2`, 並考慮移除或簡化 header。
4. **Implement Bottom Sheet (方案 A)**: 用戶點擊 Beer Icon 後，表單從底部滑出，背景的 Dashboard 仍然可見（半透明遮罩），給人「我還在這裡」的感覺。

---

## 🎨 Phase 4 Design Decision

**選擇方案**: A - Mobile-First Bottom Sheet

**設計重點**:
- 背景保留 Dashboard 可見性（使用半透明遮罩 `bg-black/50`）
- 表單從底部滑出，高度約 70-80% 螢幕
- 保留完整 2 步驟表單功能
- 成功後自動關閉 + 刷新列表

**UI Mockup**:
```
┌─────────────────────────────────────┐
│  🍺  HoldYourBeers           ≡     │  <-- Navbar
├─────────────────────────────────────┤
│                                     │
│  My Beer Collection                 │  <-- 背景可見 (dimmed)
│  ┌───────────────────────────────┐  │
│  │ 台灣啤酒 金牌        - 5 +   │  │
│  └───────────────────────────────┘  │
│                                     │
├─────────────────────────────────────┤  <-- 半透明遮罩開始
│ ━━━━━━━ (拖曳把手)                   │
│                                     │
│  🍺 Add New Beer                    │
│  ──────────────────────────         │
│  Step 1 of 2                        │
│  [Brand Input]                      │
│  [Beer Name Input]                  │
│                                     │
│  [Next Step →]                      │
└─────────────────────────────────────┘
```

---

## ✅ Implementation Checklist

### Phase 1: Remove Footer from Dashboard [✅ Completed]
**Goal**: Dashboard 頁面不顯示 footer
**Files**:
- `resources/views/layouts/app.blade.php` — 新增 `$hideFooter` slot 支援
- `resources/views/dashboard.blade.php` — 傳入 `hideFooter` 參數

**Implementation**:
```blade
{{-- app.blade.php --}}
@unless(isset($hideFooter) && $hideFooter)
    <x-footer class="!bg-transparent !backdrop-blur-none !border-none" />
@endunless

{{-- dashboard.blade.php --}}
<x-app-layout :with-footer-padding="false" :hide-footer="true">
```

---

### Phase 2: Add Navbar Center Button [✅ Completed]
**Goal**: Navbar 中間顯示 "HoldYourBeers" 文字按鈕，點擊回到 Dashboard
**Files**:
- `resources/views/layouts/navigation.blade.php`

**Implementation**:
```blade
{{-- 在 Logo 和 Hamburger 之間新增 --}}
<div class="flex-1 flex justify-center sm:hidden">
    <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en']) }}" 
       class="text-lg font-bold text-amber-600 hover:text-amber-700">
        HoldYourBeers
    </a>
</div>
```

---

### Phase 3: Reduce Top Spacing [✅ Completed]
**Goal**: 減少 My Beer Collection 與 Navbar 的距離
**Files**:
- `resources/views/dashboard.blade.php`

**Current** → **Target**:
| Property | Current | Target | Savings |
|----------|---------|--------|---------|
| `pt-12` | 48px | `pt-4` (16px) | 32px |
| `mt-6` | 24px | `mt-2` (8px) | 16px |
| **Total** | 72px | 24px | **48px** |

**考慮**: 是否移除或簡化 "Welcome, {name}" header slot

---

### Phase 4: Bottom Sheet Dialog for Add [✅ Completed]
**Goal**: 點擊 Beer Icon 開啟 Bottom Sheet，背景可見 Dashboard

**Implementation Notes**:
- Created `bottom-sheet.blade.php` component with Alpine.js
- Modified navigation to dispatch `open-add-beer` event on Beer Icon click
- Added centered "HoldYourBeers" text link for mobile navigation
- Wrapped `create-beer` Livewire component in Bottom Sheet on dashboard
- Modified `CreateBeer.php` to dispatch events and reset form state
- Fixed PHP 7.x compatibility issue (nullsafe operator)

#### Step 4.1: Create Bottom Sheet Component [✅]
**File**: `resources/views/components/bottom-sheet.blade.php`
```blade
@props(['name', 'maxHeight' => '80vh'])

<div 
    x-data="{ open: false }"
    x-on:open-{{ $name }}.window="open = true"
    x-on:close-{{ $name }}.window="open = false"
    x-on:keydown.escape.window="open = false"
>
    {{-- Backdrop --}}
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 z-40"
        @click="open = false"
    ></div>

    {{-- Sheet --}}
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="fixed bottom-0 left-0 right-0 z-50 bg-white rounded-t-2xl shadow-2xl overflow-hidden"
        style="max-height: {{ $maxHeight }}"
    >
        {{-- Handle --}}
        <div class="flex justify-center py-2">
            <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
        </div>
        
        {{-- Content --}}
        <div class="overflow-y-auto px-4 pb-8" style="max-height: calc({{ $maxHeight }} - 40px)">
            {{ $slot }}
        </div>
    </div>
</div>
```

#### Step 4.2: Modify Navigation (Beer Icon Click)
**File**: `resources/views/layouts/navigation.blade.php`
```blade
{{-- 將 <a> 改為 <button> 並加上 dispatch --}}
<button type="button" @click="$dispatch('open-add-beer')">
    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
</button>
```

#### Step 4.3: Modify Dashboard
**File**: `resources/views/dashboard.blade.php`
```blade
{{-- 在頁面底部新增 Bottom Sheet --}}
<x-bottom-sheet name="add-beer" max-height="85vh">
    <div class="max-w-md mx-auto">
        <header class="text-center mb-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Add New Beer') }}
            </h2>
        </header>
        @livewire('create-beer')
    </div>
</x-bottom-sheet>
```

#### Step 4.4: Modify CreateBeer Livewire
**File**: `app/Livewire/CreateBeer.php`
```php
// 在 save() 方法中，成功後改為:
$this->dispatch('close-add-beer');
$this->dispatch('beer-saved');

// 移除 redirect，改讓頁面透過 Livewire 刷新
return;
```

---

### Phase 5: Testing [⏳ Pending]
- [ ] Manual testing on mobile (iPhone 12 Pro viewport)
- [ ] Verify Bottom Sheet opens when clicking Beer Icon
- [ ] Verify Bottom Sheet closes on backdrop click / ESC key
- [ ] Verify form validation works inside Bottom Sheet
- [ ] Verify form submission works and list refreshes
- [ ] Verify Step 1 → Step 2 navigation works
- [ ] Test on real device (iOS Safari / Android Chrome)

---

## 📊 Outcome

### Files to be Modified
```
resources/views/
├── components/
│   └── bottom-sheet.blade.php (NEW)
├── layouts/
│   ├── app.blade.php (modified - hide footer support)
│   └── navigation.blade.php (modified - center button + icon click)
├── dashboard.blade.php (modified - spacing + bottom sheet)
└── beers/create.blade.php (可能不再需要，保留作為 fallback)

app/Livewire/
└── CreateBeer.php (modified - dispatch events instead of redirect)
```

### Estimated Effort
| Phase | Estimated Time |
|-------|----------------|
| Phase 1: Remove Footer | 15 分鐘 |
| Phase 2: Navbar Center Button | 15 分鐘 |
| Phase 3: Reduce Spacing | 10 分鐘 |
| Phase 4: Bottom Sheet | 1.5-2 小時 |
| Phase 5: Testing | 30 分鐘 |
| **Total** | **約 2.5-3 小時** |

---

## 🚧 Potential Blockers

### Blocker 1: Livewire State Reset [⚠️ POTENTIAL]
- **Issue**: Bottom Sheet 關閉後重新開啟，Livewire 元件狀態可能未重置
- **Impact**: 表單可能顯示上次填寫的內容
- **Solution**: 加入 `x-on:close-add-beer.window="$wire.$refresh()"` 或使用 `wire:key`

### Blocker 2: Keyboard Overlap [⚠️ POTENTIAL]
- **Issue**: 手機鍵盤彈出時可能遮擋輸入框
- **Impact**: 用戶無法看到正在輸入的內容
- **Solution**: 使用 `scroll-padding-bottom` 或監聽 `visualViewport` resize 事件

---

## 🎓 Lessons Learned

(To be filled after implementation)

---

## ✅ Completion

**Status**: � Planning Complete
**Completed Date**: YYYY-MM-DD
**Session Duration**: X hours

> ℹ️ **Next Steps**: 
> 1. 確認規劃無誤後開始實作
> 2. 依序完成 Phase 1-5
> 3. 測試完成後封存 Session

---

## 🔮 Future Improvements

### Not Implemented (Intentional)
- ⏳ 拖曳關閉手勢 (會增加開發複雜度約 30%)
- ⏳ 簡化版 Quick Add (1 Step Only) - 未來可評估

### Potential Enhancements
- 📌 成功新增後顯示 Toast 通知
- 📌 支援連續新增多筆 (不關閉 Bottom Sheet)
- 📌 新增啤酒後高亮顯示該筆資料

### Technical Debt
- 🔧 `beers/create.blade.php` 可能需要保留作為 fallback (非手機瀏覽時)

---

## 🔗 References

### Related Work
- [08-fix-mobile-login-view.md](./08-fix-mobile-login-view.md) - 同日的登入頁面優化

### External Resources
- [Alpine.js x-transition](https://alpinejs.dev/directives/transition)
- [Livewire Events](https://livewire.laravel.com/docs/events)

### Team Discussions
- (待補充)

