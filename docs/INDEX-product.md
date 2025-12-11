# 產品功能索引

> 記錄已完成功能的狀態、歷史和相關文檔。

## 🎯 如何使用

- **查看功能狀態**：瀏覽「功能狀態總覽」區段
- **追蹤功能演進**：查看「功能演進歷史」區段
- **對照規劃**：參考 GitHub Issues 和 Projects
- **查看實作細節**：點擊連結到完整 session

---

## 🚀 功能狀態總覽

### ✅ 已上線（Production）

#### [範例功能]
- **狀態**：✅ Production
- **版本**：v1.0.0
- **功能描述**：[簡短描述]
- **相關**：
  - Session: [XX-session-name.md](sessions/YYYY-MM/XX-session-name.md)

### 🚧 開發中（In Development）

#### [範例功能]
- **狀態**：🚧 In Development
- **預計版本**：v1.1.0
- **功能描述**：[簡短描述]
- **相關**：
  - GitHub Issue: [#XX](https://github.com/.../issues/XX)

### 📋 已規劃（Planned）

#### [範例功能]
- **狀態**：📋 Planned
- **預計版本**：v1.2.0
- **功能描述**：[簡短描述]
- **相關**：
  - GitHub Issue: [#XX](https://github.com/.../issues/XX)

---

## 📈 功能演進歷史

### [功能模組名稱]

#### v1.0.0 - [功能名稱]（YYYY-MM-DD）
- **功能**：[什麼功能]
- **Session**: [XX-session-name.md](sessions/YYYY-MM/XX-session-name.md)

---

## 🏷️ 按標籤查看

### #core-feature
_（待補充）_

### #ux-improvement
_（待補充）_

---

## 📝 維護說明

### 何時添加條目

使用以下檢查清單判斷是否應該添加到此索引：

- [ ] **這是一個「功能」**（feature）
- [ ] **使用者可以感知到的變化**
- [ ] **PM 或產品經理會關心的**

✅ **應該添加**：
- 離線同步功能（使用者可感知）
- Google 登入按鈕（使用者可見）
- 圓餅圖統計（新功能）

❌ **不應該添加**：
- Riverpod 重構（使用者無感）
- CI/CD 設定（開發工具）
- 程式碼重構（技術改進）

### 條目格式

#### 功能狀態條目

```markdown
#### [功能名稱]
- **狀態**：✅ Production / 🚧 In Development / 📋 Planned
- **版本**：v1.x.0
- **功能描述**：[簡短描述]
- **相關**：
  - Session: [XX-session-name.md](sessions/YYYY-MM/XX-session-name.md)
  - Issue: [#XX](https://github.com/.../issues/XX)
  - API: [INDEX-api.md#section](INDEX-api.md#section)
```

#### 功能演進條目

```markdown
#### v1.x.0 - [功能名稱]（YYYY-MM-DD）
- **功能**：[什麼功能]
- **Session**: [XX-session-name.md](sessions/YYYY-MM/XX-session-name.md)
```
