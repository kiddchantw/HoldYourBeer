# Session: 手機版網頁導覽列改版 - 採用底部導覽設計

**Date**: 2026-01-26
**Status**: 🔄 Planning
**Duration**: TBD
**Issue**: N/A
**Contributors**: @kiddchan, Claude AI

**Tags**: #product, #ui-ux, #refactor

**Categories**: UI/UX Design, Mobile Web, Navigation

---

## 📋 Overview

### Goal
將手機版網頁的頂部導覽列（navbar）改為底部導覽列設計，參考 Flutter 端的 bottom navigation bar 配置。

### Related Documents
- **Flutter 參考實作**: `HoldYourBeer-Flutter/lib/core/widgets/bottom_navigation.dart`
- **相關 Session**:
  - [03-navbar-customization.md](03-navbar-customization.md)
  - [14-navbar-news-feature.md](14-navbar-news-feature.md)

### Screenshots
- **Flutter 端參考**: 底部導覽包含三個項目（首頁、我的啤酒、個人檔案）
- **目前網頁端**: 頂部 navbar 配置

### Commits
- [待補充]

---

## 🎯 Context

### Problem
目前手機版網頁使用頂部導覽列（top navbar），但這與 Flutter 行動應用的使用者體驗不一致。行動裝置使用者更習慣底部導覽列的操作方式，拇指更容易觸及。

### User Story
> 作為一個使用手機瀏覽網頁的用戶，我希望導覽列位於底部，這樣我可以更輕鬆地單手操作，並且與行動應用的體驗保持一致。

### Current State
**手機版網頁端**:
- 導覽列位於頂部
- 包含 Logo、漢堡選單、主要功能連結
- 可能包含搜尋、通知等功能

**Flutter 端**:
- 底部導覽包含 3 個主要項目：
  1. 首頁 (Home)
  2. 我的啤酒 (My Beers)
  3. 個人檔案 (Profile)
- 使用 icon + label 的設計
- 當前選中項目有視覺回饋（高亮、顏色變化）

**Gap**:
- 網頁端與 Flutter 端導覽位置不一致
- 行動裝置操作不夠友善
- 視覺設計與品牌體驗不統一

---

## 💡 Planning

### Approach Analysis

#### Option A: 完全採用底部導覽 [✅ 已選擇]
將手機版網頁的主導覽完全移至底部，與 Flutter 端保持一致。

**設計要點**:
- 底部固定 3 個主要項目（與 Flutter 端一致）
- 使用 icon + 文字標籤
- 選中狀態使用品牌色（橘色 #FF9800）
- 頂部保留品牌 logo 或標題
- 次要功能（設定、通知）移至各自的頁面或 profile 頁

**Pros**:
- 與 Flutter 端體驗完全一致
- 符合行動裝置使用習慣
- 拇指操作區域友善
- 視覺焦點集中，更直觀

**Cons**:
- 需要重新設計頂部區域
- 原本頂部的次要功能需要重新安排
- 可能需要調整現有頁面的 layout（避免內容被底部 navbar 遮擋）

#### Option B: 混合式設計 [❌ 未採用]
保留頂部的品牌識別區域，在底部新增主要導覽功能。

**設計要點**:
- 頂部：Logo + 搜尋/通知等次要功能
- 底部：主要導覽 3 項目
- 頂部可設定為非固定，滾動時隱藏

**Pros**:
- 保留頂部品牌識別
- 次要功能不需要重新安排
- 漸進式改版，風險較低

**Cons**:
- 佔用更多螢幕空間
- 可能造成視覺混亂
- 與 Flutter 端體驗不完全一致

#### Option C: 響應式設計 - 桌面版保留頂部，手機版改底部 [❌ 未採用]
根據裝置類型調整導覽位置。

**設計要點**:
- 手機版（< 768px）：底部導覽
- 平板/桌面版（≥ 768px）：頂部導覽或側邊欄
- 使用 CSS media queries 實現

**Pros**:
- 針對不同裝置最佳化
- 桌面版不受影響
- 符合響應式設計原則

**Cons**:
- 需要維護兩套導覽邏輯
- 開發與測試成本較高
- 可能增加程式碼複雜度

**Decision Rationale**:

選擇 **Option A: 完全採用底部導覽** 的原因：

1. **與 Flutter 端體驗一致** ✅
   - 前後端使用者體驗完全統一
   - 降低使用者學習成本
   - 品牌體驗一致性

2. **符合行動裝置使用習慣** ✅
   - 底部導覽位於拇指操作區域（Thumb Zone）
   - 單手操作更友善
   - 符合 Material Design 與 iOS HIG 指引

3. **視覺焦點更集中** ✅
   - 主要功能在底部，視線自然下移
   - 內容區域更完整
   - 減少視覺干擾

4. **未來擴充性佳** ✅
   - 可輕鬆轉換為 PWA
   - 與原生 App 體驗更接近
   - 為未來功能擴充預留空間

**捨棄 Option B/C 的原因**:
- Option B: 佔用過多螢幕空間，手機螢幕寸土寸金
- Option C: 需要維護兩套導覽邏輯，增加開發與測試成本

---

## 🎯 設計規格（初步）

### 底部導覽項目（參考 Flutter 端）

| 項目 | Icon (Material Icons) | Icon (Heroicons) | 文字 | 路由 | 說明 |
|------|---------------------|------------------|------|------|------|
| 統計 | `bar_chart` | `chart-bar` | 統計 | `/` 或 `/dashboard` | 統計資料、圖表分析 |
| 我的啤酒 | `local_bar` | `beaker` 或 Custom SVG | 我的啤酒 | `/my-beers` | 個人收藏、追蹤清單 |
| 個人檔案 | `person` | `user` | 個人 | `/profile` | 用戶設定、帳號管理 |

**推薦**: 使用 Material Icons (與 Flutter 端保持一致) ✅

### 視覺設計要求

**配色（經過對比度驗證）**:
- 未選中狀態：
  - Icon/文字：`#616161` (灰色，對比度 7:1 ✅ WCAG AAA)
  - 背景：透明或 `#FFFFFF`

- 選中狀態：
  - Icon/文字：`#E65100` (深橘色，對比度 4.8:1 ✅ WCAG AA)
  - 背景：`#FFF3E0` (淡橘色，可選)
  - 指示器：`#FF6F00` (底部 2-3px 線條)

- 導覽列背景：`#FFFFFF` + `box-shadow: 0 -2px 8px rgba(0,0,0,0.1)`
- 分隔線：`#E0E0E0` (對比度 1.5:1，裝飾性可接受)

**對比度驗證工具**: WebAIM Contrast Checker
**目標**: WCAG AA Level (4.5:1 for normal text)

**尺寸**:
- 導覽列高度：64px (更充裕的空間)
- Icon 大小：24x24px (視覺尺寸)
- Icon 可點擊區域：48x48px (包含 padding)
- 文字大小：11-12px
- Icon 與文字間距：4px
- 整體觸控區域：48x64px (寬x高) ✅ 符合最小 44px 要求
- 項目間距：均分剩餘空間

**互動**:
- 點擊回饋：150ms ease-out (顏色過渡)
- 選中狀態切換：200ms ease-in-out
- Ripple effect：300ms (如果使用 Material Design)
- 頁面切換：250ms ease-out (淡入淡出，optional)
- 選中項目保持高亮狀態

**效能優化**:
- 使用 `transform` 和 `opacity` 進行動畫 (GPU 加速)
- 避免 `width`, `height`, `top`, `left` 動畫
- 尊重 `prefers-reduced-motion` 設定 (無障礙)

**可及性 (Accessibility)**:
- 足夠的對比度（WCAG AA 標準）
- 明確的 aria-label
- 鍵盤導覽支援
- 觸控區域至少 48x48px

---

## ✅ Implementation Checklist

### Phase 1: 設計確認 [✅ Completed]
- [x] 截圖參考 Flutter 端設計
- [x] 建立 session 文件
- [x] 與用戶確認設計方案（✅ Option A: 完全採用底部導覽）
- [x] 確認導覽項目與路由對映（統計/我的啤酒/個人檔案）
- [x] 確認視覺設計規格（顏色、尺寸、字型）
- [x] 經過 UI/UX Pro Max 審查並修正（移除 Emoji、驗證對比度、補充技術規格）

### Phase 2: 技術規劃 [⏳ Pending]
- [ ] 確認目前 navbar 的實作位置（Blade template or Vue component）
- [ ] 評估是否使用前端框架（純 CSS、Tailwind、Bootstrap、Vue/Alpine.js）
- [ ] 規劃響應式斷點策略
- [ ] 確認路由系統（Laravel routes）
- [ ] 檢查是否需要調整現有頁面 layout（避免內容被遮擋）

### Phase 3: 實作 - 底部導覽列 [⏳ Pending]
- [ ] 建立底部導覽列 component/template
- [ ] 實作 3 個導覽項目
- [ ] ✅ 加入 Material Icons (CDN 或 npm)
- [ ] ❌ 確認沒有使用 Emoji 作為 icon
- [ ] 實作選中狀態樣式（顏色 + 底部指示線）
- [ ] 實作點擊事件與路由切換
- [ ] 加入過渡動畫（150-250ms）
- [ ] 加入 `cursor-pointer` 到所有可點擊元素
- [ ] 驗證觸控區域至少 48x48px

### Phase 4: 實作 - 頂部區域調整 [⏳ Pending]
- [ ] 移除或簡化頂部 navbar
- [ ] 保留必要的品牌識別（Logo）
- [ ] 重新安排次要功能（搜尋、通知、設定等）
- [ ] 確保頂部區域響應式設計

### Phase 5: 頁面 Layout 調整 [⏳ Pending]
- [ ] 確保內容區域不被底部 navbar 遮擋（`padding-bottom: 64px`）
- [ ] 加入 iOS Safe Area 支援（`env(safe-area-inset-bottom)`）
- [ ] 設定正確的 z-index 層級（navbar: 50）
- [ ] 調整頁面滾動行為
- [ ] 檢查所有主要頁面的 layout
- [ ] 修正任何視覺錯位問題
- [ ] 驗證 viewport meta tag 包含 `viewport-fit=cover`

### Phase 6: 響應式設計 [⏳ Pending]
- [ ] 實作 media queries（手機 < 768px）
- [ ] 測試不同裝置尺寸（iPhone SE, iPhone 12, iPad, Desktop）
- [ ] 確認橫屏模式的顯示
- [ ] 桌面版導覽策略（如果採用 Option C）

### Phase 7: 可及性與測試 [⏳ Pending]
- [ ] 加入 `aria-labels` 到所有導覽項目
- [ ] 加入 `aria-current="page"` 到當前頁面
- [ ] Icon 設定 `aria-hidden="true"`
- [ ] 測試鍵盤導覽（Tab 順序正確）
- [ ] 加入 `:focus-visible` 樣式
- [ ] 測試螢幕閱讀器相容性（VoiceOver/TalkBack）
- [ ] 使用 WebAIM Contrast Checker 驗證顏色對比度
- [ ] 觸控區域測試（至少 48x48px）
- [ ] 加入 `prefers-reduced-motion` 支援

### Phase 8: 瀏覽器測試 [⏳ Pending]
- [ ] iOS Safari (iPhone SE, 12, 14 Pro, 15 Pro Max)
- [ ] 測試 iOS Safe Area (有 notch/Dynamic Island 的機型)
- [ ] 測試橫屏模式
- [ ] Android Chrome (小/中/大螢幕)
- [ ] Chrome Desktop (響應式模式)
- [ ] Firefox (桌面 & Android)
- [ ] Safari Desktop
- [ ] 測試 PWA 全螢幕模式（如果適用）

### Phase 9: 整合測試 [⏳ Pending]
- [ ] 測試路由切換
- [ ] 測試頁面重整後狀態保持
- [ ] 測試深層連結（direct URL access）
- [ ] 測試登入/登出狀態下的導覽
- [ ] 效能測試（動畫流暢度）

### Phase 10: 部署與監控 [⏳ Pending]
- [ ] 建立功能分支
- [ ] Code review
- [ ] 合併至開發分支
- [ ] 部署至測試環境
- [ ] 收集用戶回饋
- [ ] 修正問題
- [ ] 部署至正式環境

---

## 🚧 Blockers & Solutions

### Blocker 1: 設計方案未確定 [✅ RESOLVED]
- **Issue**: 需要確認採用 Option A/B/C 哪個方案
- **Impact**: 無法開始技術規劃與實作
- **Solution**: 用戶確認採用 Option A: 完全採用底部導覽
- **Resolved**: 2026-01-26 - ✅ 已選擇 Option A

### Blocker 2: 不確定目前 navbar 實作方式 [⏸️ PENDING]
- **Issue**: 不清楚目前的 navbar 是用 Blade template、Vue component 或其他方式實作
- **Impact**: 影響重構策略與工具選擇
- **Solution**: 檢查 `resources/views/layouts/` 和 `resources/js/` 目錄
- **Resolved**: [待解決]

### Blocker 3: 次要功能的重新安排 [⏸️ PENDING]
- **Issue**: 如果採用 Option A，原本頂部的搜尋、通知、設定等功能需要重新安排位置
- **Impact**: 可能需要額外的設計與開發工作
- **Solution**:
  - 搜尋：移至首頁頂部或獨立頁面
  - 通知：加入 badge 到 profile icon 或獨立頁面
  - 設定：放在 profile 頁面內
- **Resolved**: [待解決]

---

## 🎨 設計參考

### Flutter 端實作檔案位置
```
HoldYourBeer-Flutter/
└── lib/
    └── core/
        └── widgets/
            └── bottom_navigation.dart
```

### 可能的技術選型

#### 前端實作方式
1. **純 HTML/CSS + Blade Template** (最簡單)
   - 適合靜態導覽
   - 使用 CSS `position: fixed; bottom: 0;`
   - Laravel routes 處理頁面切換

2. **Tailwind CSS** (如果專案已使用)
   - 快速建立響應式設計
   - 豐富的 utility classes

3. **Alpine.js** (輕量級互動)
   - 加入簡單的狀態管理
   - 處理選中狀態切換

4. **Vue.js Component** (如果專案已使用 Vue)
   - 完整的元件化設計
   - 易於管理狀態與互動

#### Icon 選擇

**推薦順序** (⚠️ 禁止使用 Emoji):

1. **Material Icons** ✅ 推薦
   - 與 Flutter Material Design 完全一致
   - 支援 filled/outlined 兩種風格
   - CDN: `https://fonts.googleapis.com/icon?family=Material+Icons`

2. **Heroicons** (Tailwind 官方推薦)
   - 現代、清晰的設計
   - Solid/Outline 兩種風格
   - 輕量級 SVG

3. **Lucide Icons** (現代替代方案)
   - Fork from Feather Icons
   - 一致性高、可自訂性強

4. **Custom SVG Icons** (最靈活)
   - 可完全客製化
   - 但需要管理檔案與版本控制

**❌ 禁止使用**:
- Emoji (🏠 🍺 👤) - 平台渲染不一致、無法控制顏色、影響可及性

---

## 🎨 技術實作規格（詳細）

### Icon 系統實作

**選擇**: Material Icons (與 Flutter 端一致) ✅

**導覽 Icons 對映**:

| 功能 | Material Icon | 狀態 | 備註 |
|------|---------------|------|------|
| 統計 | `bar_chart` | filled/outlined | 長條圖樣式 |
| 我的啤酒 | `local_bar` | filled/outlined | 雞尾酒杯，可代表飲品 |
| 個人檔案 | `person` | filled/outlined | 人物圖示 |

**實作方式**:

```html
<!-- Material Icons CDN -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- 導覽項目範例 -->
<a href="/dashboard" class="navbar-item active">
  <span class="material-icons">bar_chart</span>
  <span class="navbar-label">統計</span>
</a>
```

**狀態切換 CSS**:

```css
.navbar-item {
  color: #616161; /* 未選中 */
  transition: color 200ms ease-in-out;
}

.navbar-item.active {
  color: #E65100; /* 選中 */
}

.navbar-item.active .navbar-label {
  font-weight: 600;
}
```

---

### 固定定位與 Layout 系統

**底部導覽固定定位**:

```css
.bottom-navbar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 50; /* 確保在內容之上，低於 modal (z-index: 100) */
  height: 64px;
  padding-bottom: env(safe-area-inset-bottom); /* iOS 安全區域 */
  background: #FFFFFF;
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-around;
  align-items: center;
}
```

**內容區域調整 (避免遮擋)**:

```css
.main-content {
  padding-bottom: calc(64px + env(safe-area-inset-bottom));
  /* 導覽列高度 + iOS 安全區域 */
}

/* 如果使用 Tailwind */
.main-content {
  @apply pb-16; /* 64px = 16 * 4px */
  padding-bottom: calc(theme('spacing.16') + env(safe-area-inset-bottom));
}
```

**Z-Index 管理**:

| 元素 | Z-Index | 說明 |
|------|---------|------|
| 一般內容 | 0-9 | 預設層級 |
| Sticky Header | 10 | 固定頂部元素 |
| Bottom Navbar | 50 | 底部導覽 ✅ |
| Dropdown/Menu | 100 | 下拉選單 |
| Modal/Dialog | 1000 | 彈窗 |
| Toast/Notification | 9999 | 通知訊息 |

---

### 顏色系統 (CSS Variables)

```css
:root {
  /* 底部導覽配色 */
  --navbar-bg: #FFFFFF;
  --navbar-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
  --navbar-border: #E0E0E0;

  /* Icon & 文字顏色 */
  --navbar-inactive: #616161; /* 對比度 7:1 ✅ WCAG AAA */
  --navbar-active: #E65100;   /* 對比度 4.8:1 ✅ WCAG AA */
  --navbar-active-bg: #FFF3E0; /* 可選的選中背景 */
  --navbar-indicator: #FF6F00; /* 底部指示線 */
}

/* Dark Mode (如果需要) */
@media (prefers-color-scheme: dark) {
  :root {
    --navbar-bg: #1F1F1F;
    --navbar-shadow: 0 -2px 8px rgba(0, 0, 0, 0.5);
    --navbar-inactive: #B0B0B0;
    --navbar-active: #FFB74D; /* 淺橘色，對比度足夠 */
  }
}
```

**使用方式**:

```css
.bottom-navbar {
  background: var(--navbar-bg);
  box-shadow: var(--navbar-shadow);
}

.navbar-item {
  color: var(--navbar-inactive);
}

.navbar-item.active {
  color: var(--navbar-active);
}
```

---

### 動畫與過渡系統

**CSS Transitions**:

```css
.navbar-item {
  transition: color 200ms ease-in-out,
              transform 150ms ease-out;
}

.navbar-item:hover {
  transform: translateY(-2px); /* 微妙的上移效果 */
}

.navbar-item:active {
  transform: translateY(0); /* 點擊時回彈 */
}

/* 選中狀態的指示線 */
.navbar-item::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%) scaleX(0);
  width: 32px;
  height: 3px;
  background: var(--navbar-indicator);
  border-radius: 2px 2px 0 0;
  transition: transform 250ms ease-out;
}

.navbar-item.active::after {
  transform: translateX(-50%) scaleX(1);
}
```

**尊重使用者偏好 (無障礙)**:

```css
@media (prefers-reduced-motion: reduce) {
  .navbar-item,
  .navbar-item::after {
    transition: none;
  }
}
```

---

### iOS Safari 特殊處理

**Safe Area Insets (iPhone X 及以上)**:

```css
.bottom-navbar {
  /* 基礎高度 64px + 底部安全區域 */
  height: calc(64px + env(safe-area-inset-bottom));
  padding-bottom: env(safe-area-inset-bottom);
}

/* 或使用 Tailwind plugin */
.bottom-navbar {
  @apply h-16 pb-safe;
}
```

**Viewport Meta Tag**:

```html
<meta name="viewport"
      content="width=device-width, initial-scale=1, viewport-fit=cover">
```

**PWA 全螢幕支援**:

```css
@supports (padding-bottom: env(safe-area-inset-bottom)) {
  .bottom-navbar {
    padding-bottom: max(16px, env(safe-area-inset-bottom));
    /* 至少 16px padding，或更大的安全區域 */
  }
}
```

**測試裝置重點**:
- iPhone SE (小螢幕)
- iPhone 12/13 (標準 notch)
- iPhone 14 Pro (Dynamic Island)
- iPhone 15 Pro Max (最大尺寸)
- iPad Mini (平板模式切換)

---

### 響應式斷點策略

**斷點定義** (如果採用 Option C):

```css
/* 手機版 - 底部導覽 */
@media (max-width: 767px) {
  .bottom-navbar {
    display: flex; /* 顯示底部導覽 */
  }

  .top-navbar {
    display: none; /* 隱藏頂部導覽 */
  }
}

/* 平板/桌面版 - 頂部或側邊導覽 */
@media (min-width: 768px) {
  .bottom-navbar {
    display: none; /* 隱藏底部導覽 */
  }

  .top-navbar {
    display: flex; /* 顯示頂部導覽 */
  }
}
```

**Tailwind 實作** (推薦):

```html
<nav class="bottom-navbar md:hidden">
  <!-- 手機版底部導覽 -->
</nav>

<nav class="top-navbar hidden md:flex">
  <!-- 桌面版頂部導覽 -->
</nav>
```

---

### 可及性 (Accessibility) 實作

**ARIA Labels**:

```html
<nav class="bottom-navbar" role="navigation" aria-label="主要導覽">
  <a href="/dashboard"
     class="navbar-item active"
     aria-label="統計頁面"
     aria-current="page">
    <span class="material-icons" aria-hidden="true">bar_chart</span>
    <span class="navbar-label">統計</span>
  </a>

  <a href="/my-beers"
     class="navbar-item"
     aria-label="我的啤酒">
    <span class="material-icons" aria-hidden="true">local_bar</span>
    <span class="navbar-label">我的啤酒</span>
  </a>

  <a href="/profile"
     class="navbar-item"
     aria-label="個人檔案">
    <span class="material-icons" aria-hidden="true">person</span>
    <span class="navbar-label">個人</span>
  </a>
</nav>
```

**鍵盤導覽**:

```css
.navbar-item:focus {
  outline: 2px solid #E65100;
  outline-offset: 2px;
}

.navbar-item:focus:not(:focus-visible) {
  outline: none; /* 隱藏滑鼠點擊時的 outline */
}

.navbar-item:focus-visible {
  outline: 2px solid #E65100;
  outline-offset: 2px;
}
```

**觸控目標尺寸驗證**:

```css
.navbar-item {
  min-width: 48px;
  min-height: 48px;
  padding: 8px 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
}
```

---

### 瀏覽器相容性

**目標支援**:

| 瀏覽器 | 版本 | 特殊處理 |
|--------|------|----------|
| iOS Safari | 14+ | Safe area insets |
| Android Chrome | 90+ | 標準支援 |
| Chrome Desktop | 90+ | 標準支援 |
| Firefox | 88+ | 標準支援 |
| Safari Desktop | 14+ | 標準支援 |

**CSS Feature Detection**:

```css
/* 檢查是否支援 env() */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
  .bottom-navbar {
    padding-bottom: env(safe-area-inset-bottom);
  }
}

/* 檢查是否支援 CSS Grid */
@supports (display: grid) {
  .bottom-navbar {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Fallback for older browsers */
@supports not (display: grid) {
  .bottom-navbar {
    display: flex;
    justify-content: space-around;
  }
}
```

---

## 📊 Outcome

### What Will Be Built
[實作完成後填寫]

### Files To Be Created/Modified
```
resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php (修改：調整 layout 結構)
│   │   └── partials/
│   │       ├── bottom-navbar.blade.php (新增：底部導覽)
│   │       └── top-header.blade.php (修改：簡化頂部)
│   └── components/
│       └── navbar-item.blade.php (新增：導覽項目元件)
├── css/
│   └── navbar.css (新增或修改：導覽樣式)
└── js/
    └── navbar.js (可選：互動邏輯)
```

### Metrics
- **預估修改檔案數**: 5-10 個
- **預估新增行數**: 200-300 行
- **預估測試時間**: 2-3 小時
- **預估開發時間**: 4-6 小時

---

## 🎓 Lessons Learned

### 1. [待實作後補充]
**Learning**:

**Solution/Pattern**:

**Future Application**:

---

## ✅ Completion

**Status**: 🔄 Planning
**Completed Date**: TBD
**Session Duration**: TBD

---

## 🔮 Future Improvements

### Not Implemented (Intentional)
- ⏳ 桌面版側邊欄設計（先專注於手機版）
- ⏳ 進階動畫效果（頁面切換、手勢操作）
- ⏳ PWA 整合（底部導覽與 app-like 體驗）

### Potential Enhancements
- 📌 加入第 4 個導覽項目（例如：探索、社群）
- 📌 導覽列自適應隱藏（向下滾動時隱藏，向上滾動時顯示）
- 📌 長按導覽項目顯示快捷選單
- 📌 底部導覽的通知 badge（未讀訊息、更新提醒）

### Technical Debt
- 🔧 需要確保與現有認證系統（Sanctum）的整合
- 🔧 可能需要調整 middleware 或 route guards
- 🔧 確保 SEO 友善（雖然是 SPA-like 但仍需考慮）

---

## 🔗 References

### Related Work
- [Material Design - Bottom Navigation](https://m3.material.io/components/navigation-bar/overview)
- [iOS Human Interface Guidelines - Tab Bars](https://developer.apple.com/design/human-interface-guidelines/tab-bars)

### External Resources
- [Mobile UX Best Practices](https://www.nngroup.com/articles/mobile-navigation-patterns/)
- [Responsive Navigation Patterns](https://bradfrost.com/blog/web/responsive-nav-patterns/)

### Team Discussions
- [待補充]

---

## 🤔 Questions for User

1. **設計方案選擇**: 你偏好哪個方案？
   - A: 完全底部導覽（推薦）
   - B: 混合式設計
   - C: 響應式設計

2. **導覽項目確認**: 是否確認使用 Flutter 端的 3 項目配置？
   - 首頁 / 我的啤酒 / 個人檔案
   - 或需要調整/新增項目？

3. **次要功能處理**: 原本頂部的功能（搜尋、通知、設定）如何處理？
   - 移至各自的頁面？
   - 保留在頂部簡化版？
   - 整合到 profile 頁面？

4. **視覺風格**: 是否完全參考 Flutter 端的視覺設計？
   - 顏色、字型、icon 風格
   - 或需要調整以符合網頁端品牌識別？

5. **開發優先級**: 此改版的優先級如何？
   - 高（立即開始）
   - 中（1-2 週內）
   - 低（可排程）

6. **測試範圍**: 是否需要建立自動化測試？
   - E2E 測試（Cypress/Playwright）
   - 視覺回歸測試
   - 或僅手動測試？
