# 手機版與桌面版導航列重設計 (2026-01-26)

## 概述

重新設計了 HoldYourBeer 應用的導航結構，統一手機版和桌面版的導航菜單，並調整了教學按鈕的位置以優化用戶體驗。

## 主要變更

### 1. 登入頁面 Logo 背景移除
**檔案**: `resources/views/layouts/guest.blade.php`

**變更內容**:
- 移除 logo 的白色圓形背景 (`bg-white p-4 rounded-full shadow-xl ring-4 ring-orange-100/50`)
- logo 圖片現在直接顯示，背景完全透明
- 保留 `drop-shadow-lg` 以維持視覺深度

**前後對比**:
```
❌ 舊版：<a class="block bg-white p-4 rounded-full shadow-xl ring-4 ring-orange-100/50">
✅ 新版：<a class="block">
```

---

### 2. 手機版底部導航列寬度平均化
**檔案**: `resources/views/layouts/bottom-navbar.blade.php`

**變更內容**:
- 移除容器的 `justify-around px-4` 約束
- 為每個導航項目新增 `flex-1` class，使其平均分配寬度
- 三個 icon 區塊現在平均分配整個底部導航列的寬度

**代碼變化**:
```blade
❌ 舊版：<div class="h-full flex items-center justify-around px-4">
✅ 新版：<div class="h-full flex items-center">
         <a class="... flex-1 ...">...</a>
```

---

### 3. Bottom Sheet 導航時自動關閉
**檔案**: `resources/views/components/bottom-sheet.blade.php`

**變更內容**:
新增三個事件監聽器，防止在頁面導航時新增啤酒 dialog 短暫閃現：

1. `x-on:beforeunload.window` - 頁面卸載前關閉
2. `popstate` 事件監聽 - 瀏覽器前進/後退時關閉
3. `livewire:navigating` 事件監聽 - Livewire 導航開始時關閉

**代碼變化**:
```blade
<div
    x-data="{ open: false }"
    x-on:open-{{ $name }}.window="open = true"
    x-on:close-{{ $name }}.window="open = false"
    x-on:keydown.escape.window="open = false"
    x-on:beforeunload.window="open = false"  <!-- 新增 -->
    x-init="
        window.addEventListener('popstate', () => { open = false });  <!-- 新增 -->
        document.addEventListener('livewire:navigating', () => { open = false });  <!-- 新增 -->
    "
>
```

---

### 4. 導航列路由修正
**檔案**:
- `resources/views/layouts/navigation.blade.php` (桌面版)
- `resources/views/layouts/bottom-navbar.blade.php` (手機版)

#### 問題：
- 「統計」按鈕連結到 dashboard 而非 charts 頁面
- 「我的啤酒」按鈕缺少高亮狀態判斷

#### 解決方案：

**統計按鈕**:
- ❌ 舊路由：`route('localized.dashboard')`
- ✅ 新路由：`route('charts')`
- ✅ 新增高亮狀態：`request()->routeIs('charts')`

**我的啤酒按鈕**:
- ✅ 路由正確：`route('localized.dashboard')`
- ✅ 新增高亮狀態：`request()->routeIs('localized.dashboard')`
- ✅ 新增底部指示線（active indicator）

---

### 5. 導航菜單結構重組
**檔案**:
- `resources/views/layouts/navigation.blade.php` (桌面版)
- `resources/views/layouts/bottom-navbar.blade.php` (手機版)

#### 新導航結構：

| 項目 | 桌面版 | 手機版 | 路由 | 圖示 |
|------|--------|--------|------|------|
| **News** | ✅ 文字 | ✅ icon + 文字 | `news.index` | `article` |
| **我的啤酒** | ✅ 文字 | ✅ icon + 文字 | `localized.dashboard` | `local_bar` |
| **統計** | ✅ 文字 | ✅ icon + 文字 | `charts` | `bar_chart` |
| **個人檔案** | ✅ 文字 | ✅ icon + 文字 | `profile.edit` | `person` |
| **教學** | ✅ 文字 | ❌ 頂部 icon | `onboarding.restart` | `help_outline` |

**菜單順序**:
```
News → My Beers → Statistics → Profile → (Desktop Only: Tutorial)
```

---

### 6. 教學按鈕位置安排

#### 桌面版：
- **位置**: 在「個人檔案」之後
- **顯示**: 文字 "教學"
- **路由**: `onboarding.restart`
- **樣式**: 與其他項目一致，hover 時變色

#### 手機版：
- **位置**: 頂部導航列，logo 右邊（`md:hidden` 隱藏於桌面）
- **顯示**: 圖示按鈕 `help_outline` (問號圓圈)
- **樣式**: 圓形按鈕 (w-9 h-9)，橘色圖示 (text-amber-600)
- **路由**: `onboarding.restart`
- **Aria Label**: `{{ __('教學') }}`

**代碼實現**:
```blade
<!-- Desktop: Text Link -->
<a href="{{ route('onboarding.restart', ...) }}">{{ __('教學') }}</a>

<!-- Mobile: Icon Button (md:hidden) -->
<a href="{{ route('onboarding.restart', ...) }}" class="md:hidden">
    <span class="material-icons help_outline"></span>
</a>
```

---

## 受影響的檔案

1. ✅ `resources/views/layouts/guest.blade.php` - Logo 背景移除
2. ✅ `resources/views/layouts/navigation.blade.php` - 導航結構、教學按鈕
3. ✅ `resources/views/layouts/bottom-navbar.blade.php` - 導航結構、寬度平均化
4. ✅ `resources/views/components/bottom-sheet.blade.php` - 自動關閉邏輯

## 測試清單

- [ ] 手機版：logo 背景透明顯示正確
- [ ] 手機版：底部導航 4 個按鈕平均寬度
- [ ] 手機版：點擊導航按鈕不會閃現新增啤酒 dialog
- [ ] 手機版：點擊 News 按鈕能跳轉到 news.index
- [ ] 手機版：點擊 My Beers 按鈕能跳轉到 localized.dashboard
- [ ] 手機版：點擊 Statistics 按鈕能跳轉到 charts
- [ ] 手機版：點擊 Profile 按鈕能跳轉到 profile.edit
- [ ] 手機版：頂部 logo 右邊教學按鈕顯示正確
- [ ] 手機版：點擊教學按鈕能跳轉到 onboarding.restart
- [ ] 桌面版：導航列順序正確 (News → My Beers → Statistics → Profile → Tutorial)
- [ ] 桌面版：統計按鈕連結到 charts 頁面
- [ ] 桌面版：所有按鈕高亮狀態判斷正確
- [ ] 桌面版：點擊教學按鈕能跳轉到 onboarding.restart
- [ ] 兩個版本：active 狀態顯示橘色 (#E65100) 和底部指示線

## 注意事項

1. **教學按鈕不同位置**:
   - 桌面版：導航菜單中的文字連結
   - 手機版：頂部導航列的圖示按鈕
   - 保持底部導航列為 4 個項目，不引入視覺擁擠

2. **Icon 使用**:
   - 所有 icon 使用 Material Icons (`material-icons` class)
   - News: `article`
   - My Beers: `local_bar`
   - Statistics: `bar_chart`
   - Profile: `person`
   - Tutorial: `help_outline`

3. **路由狀態判斷**:
   - 使用 `request()->routeIs()` 判斷當前頁面
   - News: `news.index`
   - My Beers: `localized.dashboard`
   - Statistics: `charts`
   - Profile: `profile.edit`
   - Tutorial: (無 active 狀態判斷)

## 後續改進建議

1. 考慮為教學按鈕新增 tooltip 說明
2. 監測用戶在教學按鈕的點擊率
3. 評估手機版頂部按鈕的可發現性
4. 考慮在首次使用時自動開啟教學，而不需點擊按鈕

---

**更新時間**: 2026-01-26
**異動人員**: Claude Code Assistant
**優先級**: Medium
