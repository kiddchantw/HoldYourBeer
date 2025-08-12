# 產品需求文件 (Product Requirements Document)

## 1. 概述 (Overview)

### 1.1 描述為何 (Why)

本產品「HoldYouBeer」旨在提供一個簡潔有效的平台，幫助啤酒愛好者輕鬆追蹤他們的啤酒品飲體驗。用戶可以記錄他們喝過的每一款啤酒，管理個人啤酒庫存，並記錄詳細的品嚐筆記，從而更好地了解自己的喜好與品飲習慣。

### 1.2 做什麼 (What)

HoldYourBeer 應用程式提供以下核心功能：

*   **用戶註冊與登入**：安全地建立和管理用戶帳戶。
*   **啤酒追蹤**：記錄已品飲啤酒的數量，並可增減計數。
*   **新增啤酒品項**：允許用戶新增其個人庫存中尚未記錄的啤酒品牌和系列。
*   **品嚐筆記管理**：為每款啤酒添加和查看詳細的品嚐日誌。
*   **個人啤酒列表**：顯示用戶所有已追蹤啤酒的列表，包含品牌、名稱和品飲次數。
*   **API 介面**：提供穩定的 API 供前端應用程式（iOS 和 Web）使用。

## 2. 目標與業務場景 (Goals & Business Scenarios)

### 2.1 目標 (Goals)

*   提供直觀且易於使用的啤酒追蹤體驗。
*   幫助用戶建立和維護個人化的啤酒品飲歷史。
*   透過數據化管理，提升用戶對啤酒品類的探索樂趣。
*   確保數據的一致性和安全性。

### 2.2 業務場景 (Business Scenarios)

以下是本產品支援的主要業務場景：

*   **用戶註冊**：新用戶透過應用程式註冊帳戶，成為 HoldYourBeer 的一員。
*   **查看個人啤酒列表**：用戶登入後，可以瀏覽他們所有已追蹤的啤酒，並查看每款啤酒的品飲次數。
*   **增加/減少啤酒品飲計數**：用戶品飲某款啤酒後，可以快速更新其品飲計數；若有誤操作，也可減少計數。
*   **新增未記錄的啤酒**：用戶遇到新的啤酒品項時，可以將其新增到自己的追蹤列表中，並可選擇建立新的品牌資訊。
*   **記錄品嚐筆記**：用戶可以為特定的啤酒添加詳細的品嚐日誌，記錄風味、場合等資訊。

## 3. 功能規格 (Functional Specifications)

### 3.1 用戶註冊 (User Registration)

詳見 `/spec/features/user-registration.feature` 中定義的用戶註冊流程與行為。

### 3.2 啤酒追蹤 (Beer Tracking)

啤酒追蹤功能涵蓋以下主要方面，詳細規格請參考對應的 `.feature` 檔案：

*   **查看啤酒列表**：`/spec/features/beer_tracking/viewing_the_list.feature`
*   **新增啤酒**：`/spec/features/beer_tracking/adding_a_beer.feature`
*   **管理品嚐記錄**：`/spec/features/beer_tracking/managing_tastings.feature`

### 3.3 API 規格 (API Specifications)

所有後端 API 介面均遵循 OpenAPI 3.0 規範，詳細定義請參考 `/spec/api/api.yaml`。主要 API 端點包括：

*   用戶認證 (註冊、登入)
*   啤酒列表與管理
*   啤酒計數操作
*   品嚐日誌查詢
*   品牌資訊查詢

### 3.4 錯誤處理 (Error Handling)

API 錯誤回應遵循標準化 JSON 格式，詳細結構定義請參考 `/spec/format/error-response.json`。

## 4. 驗收標準 (Acceptance Criteria)

所有功能和用戶故事的完成標準，請參考 `/spec/acceptance/definition-of-done.md`。該文件定義了通用準則，涵蓋了規格、程式碼實現、測試和用戶體驗設計等方面。

## 5. 參考文件 (References)

*   **API 規格**：`/spec/api/api.yaml`
*   **驗收標準**：`/spec/acceptance/definition-of-done.md`
*   **錯誤回應格式**：`/spec/format/error-response.json`
*   **用戶註冊功能**：`/spec/features/user-registration.feature`
*   **啤酒追蹤功能**：
    *   `/spec/features/beer_tracking/adding_a_beer.feature`
    *   `/spec/features/beer_tracking/managing_tastings.feature`
    *   `/spec/features/beer_tracking/viewing_the_list.feature`
*   **系統流程圖**：
    *   `/docs/diagrams/flow-user-view-and-increment.md`
    *   `/docs/diagrams/flow-user-add-new-beer.md`
    *   `/docs/diagrams/flow-backend-count-action.md`
