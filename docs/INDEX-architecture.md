# 架構演進索引

> 本檔案索引系統架構的重要變更和設計模式。

## 🎯 如何使用

- **了解當前架構**：查看「當前架構概覽」區段
- **查看設計模式**：瀏覽「設計模式」區段
- **追蹤演進歷史**：查看「架構演進歷史」區段
- **查看完整說明**：點擊連結到完整 session

---

## 🏗️ 當前架構概覽

### 系統架構

```
[Project Name]
│
├─ Presentation Layer
│  └─ ...
│
├─ Application Layer
│  └─ ...
│
├─ Domain Layer
│  └─ ...
│
└─ Data Layer
   └─ ...
```

### 技術棧

| 層級 | 技術選擇 | 決策記錄 |
|------|---------|---------|
| 狀態管理 | [Technology] | _（待補充）_ |
| API 整合 | [Technology] | _（待補充）_ |
| 本地儲存 | [Technology] | _（待補充）_ |
| 路由 | [Technology] | _（待補充）_ |

---

## 🎨 設計模式

### [Pattern Name]

_（待開發時補充）_

#### 範例格式

```markdown
#### [YYYY-MM-DD] [Pattern Name] 實作
- **模式**：[Description]
- **適用場景**：[Context]
- **實作細節**：[Session](sessions/YYYY-MM/XX-session.md)
```

---

## 📈 架構演進歷史

_按時間倒序記錄架構變更_

### 範例格式

```markdown
#### [YYYY-MM-DD] [Change Title]
- **變更**：[What changed]
- **原因**：[Why]
- **影響範圍**：[Scope]
- **完整記錄**：[Session](sessions/YYYY-MM/XX-session.md)
```

---

## 🏷️ 按標籤查看

### #state-management
_（待補充）_

### #api-integration
_（待補充）_

### #data-persistence
_（待補充）_

---

## 📝 維護說明

### 何時添加條目

使用以下檢查清單判斷是否應該添加到此索引：

- [ ] **引入了新的設計模式**
- [ ] **改變了系統架構**（layers, modules）
- [ ] **定義了可重用的模式**
- [ ] **新成員需要理解「系統是如何組織的」**

✅ **應該添加**：
- Riverpod Provider 組織模式
- Feature-based 目錄結構
- API Client 封裝模式
- 錯誤處理統一模式

❌ **不應該添加**：
- 單一按鈕的樣式（不是模式）
- 特定頁面的佈局（不是架構）
- 單一功能的實作細節

### 條目格式

#### 設計模式條目

```markdown
#### [YYYY-MM-DD] [模式名稱]
- **模式**：[簡短描述]
- **適用場景**：[何時使用]
- **完整說明**：[Session](sessions/YYYY-MM/XX-session-name.md)
```

#### 架構變更條目

```markdown
#### [YYYY-MM-DD] [變更標題]
- **變更**：[什麼改變了]
- **原因**：[為什麼改變]
- **影響範圍**：[影響哪些部分]
- **完整記錄**：[Session](sessions/YYYY-MM/XX-session-name.md)
```
