# 技術決策索引

> 本檔案記錄專案中的重要技術決策（ADR - Architecture Decision Records）。

## 🎯 如何使用

- **查看決策**：瀏覽「決策記錄」區段
- **了解背景**：點擊連結到完整 session
- **決策狀態**：✅ Accepted / ❌ Rejected / ⏳ Proposed / 🔄 Deprecated

---

## 📝 決策記錄

### [YYYY-MM]

#### [YYYY-MM-DD] [決策標題]
- **決策**：[選擇了什麼]
- **狀態**：✅ Accepted
- **原因**：[為什麼這樣選擇]
- **替代方案**：[考慮過哪些其他選項]
- **完整內容**：[Session](sessions/YYYY-MM/XX-session-name.md)

---

## 🏷️ 按分類查看

### #frontend
_（待補充）_

### #backend
_（待補充）_

### #infrastructure
_（待補充）_

---

## 📝 維護說明

### 何時添加條目

使用以下檢查清單判斷是否應該添加到此索引：

- [ ] **在多個方案中做了選擇**（A vs B vs C）
- [ ] **有明確的 trade-offs**（取捨）
- [ ] **未來開發者會問「為什麼用這個而不是那個？」**
- [ ] **這個決策會影響未來的開發**

✅ **應該添加**：
- 選擇 Riverpod vs Provider
- 選擇 Sync Queue vs CRDT
- 資料庫選型

❌ **不應該添加**：
- 使用 Google 官方 Logo（沒有選擇）
- 修正 Bug 的方式（除非涉及架構）

### 條目格式

```markdown
#### [YYYY-MM-DD] [決策標題]
- **決策**：[選擇了什麼]
- **狀態**：✅ Accepted / ❌ Rejected / 🔄 Deprecated
- **原因**：[為什麼這樣選擇]
- **替代方案**：[考慮過哪些其他選項]
- **完整內容**：[Session](sessions/YYYY-MM/XX-session-name.md)
```
