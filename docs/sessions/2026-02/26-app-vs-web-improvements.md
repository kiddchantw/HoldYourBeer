# Session: App vs 手機版網頁差異改進（Web 端）

**Date**: 2026-02-26
**Status**: ✅ Completed
**Completed Date**: 2026-02-26
**Session Duration**: < 1 hour
**Issue**: -
**Contributors**: @kiddchan, Claude AI
**Branch**: -
**Tags**: #ui, #product, #refactor

**Categories**: Navigation, UI Consistency

---

## 📋 Overview

### Goal
將手機版網頁的 NavBar 行為與 App 對齊，統一使用者體驗：
1. 語系切換從 NavBar 移至 Profile 頁面
2. 統計頁 NavBar 行動端隱藏教學按鈕（左右留空）

### Related Documents
- **Flutter Session**: `HoldYourBeer-Flutter/docs/sessions/2026-02/26_app-vs-web-improvements.md`

### Commits
- [待填寫]

---

## 🎯 Context

### Problem
App 與手機版網頁的 NavBar 不一致：
- 網頁右上角有語系切換（🌐 EN），App 則放在 Profile 頁面
- 統計頁行動端 NavBar 有教學按鈕，App 則只顯示標題

### Current State（修改前）
- `navigation.blade.php`：NavBar 右側有 `<x-language-switcher />`
- `navigation.blade.php`：行動端左側教學按鈕在所有頁面都顯示
- `profile/edit.blade.php`：無語系切換選項

**Gap**: 語系切換應在 Profile，統計頁 NavBar 應簡化。

---

## ✅ Implementation Checklist

### Phase 1: 語系切換移至 Profile [✅ Completed]
- [x] `navigation.blade.php` 移除 `<x-language-switcher />`（右側）
- [x] `profile/edit.blade.php` 新增 Language Settings 卡片（右欄頂部）

### Phase 2: 統計頁 NavBar 簡化 [✅ Completed]
- [x] `navigation.blade.php` 教學按鈕加上 `@unless(request()->routeIs('charts'))` 條件

---

## 📊 Outcome

### Files Modified
```
resources/views/
├── layouts/navigation.blade.php     (modified)
│   - 移除語系切換組件
│   - 統計頁行動端隱藏教學按鈕
└── profile/edit.blade.php           (modified)
    - 新增 Language Settings 卡片（右欄頂部）
```

### 修改細節

#### navigation.blade.php
```blade
{{-- 教學按鈕：統計頁不顯示 --}}
@unless(request()->routeIs('charts'))
<a href="..." class="md:hidden ...">...</a>
@endunless

{{-- 語系切換：已移除 --}}
{{-- <x-language-switcher /> --}}
```

#### profile/edit.blade.php
```blade
<!-- Language Settings（新增，右欄頂部）-->
<section>
    <header>
        <h2>{{ __('Language Settings') }}</h2>
        <p>{{ __('Switch the application display language.') }}</p>
    </header>
    <x-language-switcher />
</section>
```

---

## ✅ Completion

**Status**: ✅ Completed
**Completed Date**: 2026-02-26

> **注意**: 需確認 i18n key `Language Settings` 和 `Switch the application display language.` 已在 `lang/en.json` 和 `lang/zh_TW.json` 中定義。

---

## 🔮 Future Improvements

### Potential Enhancements
- 📌 語系切換可考慮改為下拉選單樣式（支援未來新增更多語系）
- 📌 統計頁行動端可考慮完全隱藏 NavBar，改用 SliverAppBar 效果
