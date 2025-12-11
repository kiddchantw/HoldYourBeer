# Session Management Guide

本指南詳細說明如何使用 Session 系統進行開發記錄。

## 🏷️ 標籤選擇指南

> 使用以下檢查清單決定標籤。一個 session 可以有多個標籤。

### #decisions - 重要技術決策

**何時使用**：
- [ ] 在多個方案中做了選擇（A vs B vs C）
- [ ] 有明確的 trade-offs（取捨）
- [ ] 未來開發者會問「為什麼用這個而不是那個？」
- [ ] 這個決策會影響未來的開發

**完成時添加到**：`INDEX-decisions.md`

---

### #architecture - 設計模式或架構變更

**何時使用**：
- [ ] 引入了新的設計模式
- [ ] 改變了系統架構（layers, modules）
- [ ] 定義了可重用的模式
- [ ] 新成員需要理解「系統是如何組織的」

**完成時添加到**：`INDEX-architecture.md`

---

### #api - API 變更

**何時使用**：
- [ ] 新增了 API endpoint
- [ ] 修改了現有 API 的 contract（request/response 格式變更）
- [ ] 改變了認證機制
- [ ] 前後端需要對齊的介面變更

**完成時添加到**：`INDEX-api.md`

---

### #product - 功能開發

**何時使用**：
- [ ] 這是一個「功能」（feature）
- [ ] 使用者可以感知到的變化
- [ ] PM 或產品經理會關心的

**完成時添加到**：`INDEX-product.md`

---

### #infrastructure - 基礎設施

**何時使用**：
- [ ] CI/CD 設定
- [ ] 開發工具配置
- [ ] 測試框架設定
- [ ] 部署流程改進

**完成時**：可選擇性添加到 INDEX（通常不需要）

---

### #refactor - 重構

**何時使用**：
- [ ] 程式碼重構
- [ ] 使用者無感的改進
- [ ] 技術債務償還

**完成時**：視情況添加到 INDEX-architecture.md（如果有模式）

---

**標籤使用提示**：
- 一個 session 可以有多個標籤
- 最常見的組合：`#decisions` + `#architecture` + `#product`
- 不確定時可以先不填，完成時再決定
- 運行 `./scripts/archive-session.sh` 會根據內容自動建議標籤

## 📝 Session 完成後步驟

完成 session 後，依照以下步驟：

### 1. 更新狀態

- 在上方更新：`**Status**: ✅ Completed`
- 填寫完成日期：`**Completed Date**: YYYY-MM-DD`

### 2. 更新 INDEX 檔案（根據 Tags）

根據上方的 Tags，添加摘要到相應的 INDEX：

| Tag | 目標 INDEX | 何時添加 |
|-----|-----------|---------|
| `#decisions` | `INDEX-decisions.md` | 包含重要技術決策 |
| `#architecture` | `INDEX-architecture.md` | 包含設計模式或架構變更 |
| `#api` | `INDEX-api.md` | 包含 API 新增或修改 |
| `#product` | `INDEX-product.md` | 功能開發記錄 |

### 3. 運行腳本（推薦）

```bash
./scripts/archive-session.sh
```

腳本會：
- 讀取 session 的 Tags
- 提示要更新哪些 INDEX
- 提供每個 INDEX 的模板
- 更新 session 狀態為 ✅

### 4. 批次更新 CHANGELOG（發布前）

```bash
./scripts/update-changelog.sh
```

準備發布新版本時執行，會掃描所有已完成的 sessions 並更新 CHANGELOG。

## 📋 INDEX Entry Templates

根據 Tags，使用以下模板添加到對應的 INDEX：

### INDEX-decisions.md

```markdown
#### [YYYY-MM-DD] [決策標題]
- **決策**：[選擇了什麼]
- **原因**：[為什麼這樣選擇]
- **替代方案**：[考慮過哪些其他選項]
- **完整內容**：[Session](sessions/YYYY-MM/XX-session-name.md)
```

### INDEX-architecture.md

```markdown
#### [YYYY-MM-DD] [模式/架構標題]
- **模式/變更**：[簡短描述]
- **適用場景**：[何時使用]
- **完整說明**：[Session](sessions/YYYY-MM/XX-session-name.md)
```

### INDEX-api.md

```markdown
### [YYYY-MM-DD] [API 變更標題]
- **新增/修改**：[具體變更，例如：POST /api/sync/batch]
- **Breaking Change**：是/否
- **完整說明**：[Session](sessions/YYYY-MM/XX-session-name.md)
```

### INDEX-product.md

```markdown
#### [功能名稱]
- **狀態**：✅ Production / 🚧 In Development
- **版本**：v1.x.0
- **功能描述**：[簡短描述]
- **相關**：
  - Session: [XX-session-name.md](sessions/YYYY-MM/XX-session-name.md)
  - Issue: [#XX](https://github.com/.../issues/XX)
```
