# Session: Brand CRUD 管理功能

**Date**: 2025-11-20
**Status**: 🔄 In Progress
**Duration**: [Estimated] 4-6 hours
**Issue**: N/A
**Contributors**: @kiddchan, Gemini AI

**Tags**: 
#product, #architecture, #api

**Categories**: Admin Interface, CRUD Operations, Livewire Components

---

## 📋 Overview

### Goal
為管理者建立完整的 Brand CRUD (Create, Read, Update, Delete) 功能，使用 Livewire 實作互動式管理介面。

### Related Documents
- **Brand Model**: [app/Models/Brand.php](file:///Users/kiddchan/Desktop/testVirtualization/laraDock/beer/HoldYourBeer/app/Models/Brand.php)
- **Existing API**: [app/Http/Controllers/Api/V1/BrandController.php](file:///Users/kiddchan/Desktop/testVirtualization/laraDock/beer/HoldYourBeer/app/Http/Controllers/Api/V1/BrandController.php)
- **Admin Dashboard**: [resources/views/admin/dashboard.blade.php](file:///Users/kiddchan/Desktop/testVirtualization/laraDock/beer/HoldYourBeer/resources/views/admin/dashboard.blade.php)

### Commits
- [Will be filled during development]

---

## 🎯 Context

### Problem
目前系統中的 Brand 資料只能透過 API 或在建立 Beer 時自動建立（firstOrCreate），缺乏管理介面讓管理員直接管理品牌資料。這導致：
1. 無法批量管理品牌
2. 無法修正錯誤的品牌名稱
3. 無法刪除不再使用的品牌
4. 缺乏品牌資料的可視化管理

### User Story
> As a **系統管理員**, I want to **在管理後台管理 Brand 資料** so that **我可以維護品牌資料的正確性和完整性**。

### Current State
- ✅ Brand Model 已存在，包含基本的 name 欄位和 beers 關聯
- ✅ Brand API (V1/V2) 提供 index 端點（僅讀取）
- ✅ AdminMiddleware 已實作，檢查 user.role === 'admin'
- ✅ 管理後台已有基本架構（admin/dashboard.blade.php）
- ✅ Livewire 已整合（參考 CreateBeer 元件）

**Gap**: 缺少管理介面讓管理員進行 Brand 的 CRUD 操作

---

## 💡 Planning

### Approach Analysis

#### Option A: Livewire 元件 [✅ CHOSEN]
使用 Livewire 建立互動式管理介面，所有 CRUD 操作在同一頁面完成。

**Pros**:
- 使用者體驗佳，無需頁面跳轉
- 與現有 CreateBeer 元件一致的技術棧
- 即時搜尋和分頁功能容易實作
- Modal 彈窗提供流暢的操作體驗

**Cons**:
- 需要撰寫較多的前端互動邏輯
- Livewire 的學習曲線（但團隊已熟悉）

#### Option B: 傳統 CRUD 路由 [❌ REJECTED]
使用傳統的 RESTful 路由，每個操作都有獨立頁面。

**Pros**:
- 實作簡單直接
- 符合 Laravel 標準慣例

**Cons**:
- 使用者體驗較差，需要多次頁面跳轉
- 無法提供即時搜尋功能
- 與現有管理介面風格不一致

**Decision Rationale**: 選擇 Option A，因為專案已經使用 Livewire，且互動式介面能提供更好的使用者體驗。

### Design Decisions

#### D1: 刪除 Brand 時的資料處理策略
- **Options**: 
  - A: 禁止刪除（如果有關聯 Beer）
  - B: 級聯刪除
  - C: 軟刪除
- **Chosen**: C（軟刪除）✅
- **Reason**: 
  - 可以恢復誤刪的資料
  - 保留歷史記錄用於審計
  - 不會破壞資料完整性
  - 未來可以新增「已刪除品牌」管理介面
- **Trade-offs**: 
  - 需要在 Brand Model 加入 SoftDeletes trait
  - 需要建立 migration 新增 deleted_at 欄位
  - 查詢時需要注意是否包含已刪除資料

#### D2: 搜尋功能實作方式
- **Options**: 
  - A: 即時搜尋（Livewire）
  - B: 表單提交搜尋
- **Chosen**: A
- **Reason**: 提供更好的使用者體驗
- **Trade-offs**: 每次輸入都會觸發查詢，但可透過 debounce 優化

#### D3: 分頁大小
- **Options**: 10, 15, 20, 25
- **Chosen**: 15
- **Reason**: 平衡可視性和效能
- **Trade-offs**: 可能需要根據實際使用情況調整

---

## ✅ Implementation Checklist

### Phase 1: 資料庫準備（軟刪除） [⏳ Pending]
- [ ] 建立 migration 新增 deleted_at 欄位到 brands 表
- [ ] 更新 Brand Model 加入 SoftDeletes trait
- [ ] 執行 migration

### Phase 2: Livewire 元件建立 [⏳ Pending]
- [ ] 建立 ManageBrands Livewire 元件
- [ ] 實作 render() 方法（列表、搜尋、分頁）
- [ ] 實作 create() 和 store() 方法
- [ ] 實作 edit() 和 update() 方法
- [ ] 實作 delete() 方法（軟刪除）
- [ ] 實作 restore() 方法（恢復已刪除）

### Phase 3: 視圖建立 [⏳ Pending]
- [ ] 建立 livewire/manage-brands.blade.php
  - [ ] 品牌列表表格
  - [ ] 搜尋輸入框
  - [ ] 新增按鈕
  - [ ] 編輯/刪除按鈕
  - [ ] Modal 彈窗（新增/編輯）
  - [ ] 刪除確認對話框
- [ ] 建立 admin/brands/index.blade.php
  - [ ] 使用 app-layout
  - [ ] 嵌入 Livewire 元件

### Phase 4: 路由和語言檔案 [⏳ Pending]
- [ ] 更新 routes/web.php（新增 admin/brands 路由）
- [ ] 更新 lang/zh-TW.json（新增繁體中文翻譯）
- [ ] 更新 lang/en.json（如果需要）

### Phase 5: 測試 [⏳ Pending]
- [ ] 建立 ManageBrandsTest.php
  - [ ] 權限測試（管理員/非管理員）
  - [ ] 列表顯示測試
  - [ ] 搜尋功能測試
  - [ ] 新增 Brand 測試
  - [ ] 編輯 Brand 測試
  - [ ] 刪除 Brand 測試（空/有關聯）
- [ ] 執行所有測試確保無破壞性變更

### Phase 6: 手動驗證 [⏳ Pending]
- [ ] 建立測試用管理員帳號
- [ ] 測試所有 CRUD 功能
- [ ] 測試權限控制
- [ ] 測試多語言切換
- [ ] 測試響應式設計（手機/平板/桌面）

---

## 🚧 Blockers & Solutions

### Blocker 1: 刪除策略未確定 [✅ RESOLVED]
- **Issue**: 需要確認刪除 Brand 時的資料處理策略
- **Impact**: 影響 delete() 方法的實作邏輯
- **Solution**: 用戶確認使用軟刪除（Option C）
- **Resolved**: 2025-11-20

---

## 📊 Outcome

### What Was Built
[List of deliverables - fill after completion]

### Files Created/Modified
```
[Will be filled during implementation]
```

### Metrics
- **Code Coverage**: TBD
- **Lines Added**: TBD
- **Lines Modified**: TBD
- **Test Files**: TBD

---

## 🎓 Lessons Learned

[Will be filled after completion]

---

## ✅ Completion

**Status**: 🔄 In Progress
**Completed Date**: TBD
**Session Duration**: TBD

> ℹ️ **Next Steps**: 詳見 [Session Guide](../GUIDE.md)
> 1. 更新上方狀態與日期
> 2. 根據 Tags 更新 INDEX 檔案
> 3. 運行 `../.agent/scripts/archive-session.sh`

---

## 🔮 Future Improvements

### Not Implemented (Intentional)
- ⏳ 批量操作（批量刪除、批量編輯）
- ⏳ 匯入/匯出功能
- ⏳ Brand Logo 上傳功能

### Potential Enhancements
- 📌 新增 Brand 詳細資訊欄位（國家、網站等）
- 📌 Brand 與 Beer 的關聯視覺化
- 📌 Brand 使用統計（被多少用戶使用）

### Technical Debt
- 🔧 目前僅支援簡單的名稱搜尋，未來可考慮進階搜尋

---

## 🔗 References

### Related Work
- [CreateBeer Livewire Component](file:///Users/kiddchan/Desktop/testVirtualization/laraDock/beer/HoldYourBeer/app/Livewire/CreateBeer.php)
- [Admin Dashboard](file:///Users/kiddchan/Desktop/testVirtualization/laraDock/beer/HoldYourBeer/resources/views/admin/dashboard.blade.php)

### External Resources
- [Livewire Documentation](https://livewire.laravel.com/)
- [Tailwind CSS](https://tailwindcss.com/)

### Team Discussions
- [To be added if any]
