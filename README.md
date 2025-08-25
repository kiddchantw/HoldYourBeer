# HoldYourBeer

A simple application to track every beer you drink. Record the brand, the specific series/name, and automatically count how many times you've tasted each one.

This project is developed using a Spec-driven development approach.

---

## Technology Stack

- **Backend Framework**: Laravel 12
- **Web Frontend**: Livewire
- **Database**: PostgreSQL 17
- **PHP Version**: 8.3
- **Development Environment**: Laradock

---

## Local Development Setup

This project uses Laradock for a consistent development environment. Follow these steps to get started.

### 1. Clone the Repository

```bash
git clone <your-repository-url>
cd HoldYourBeer
```

### 2. Setup Laradock

We will add Laradock as a git submodule.

```bash
git submodule add https://github.com/Laradock/laradock.git
```

### 3. Configure Laradock Environment

Navigate into the `laradock` directory and create your environment file.

```bash
cd laradock
cp env-example .env
```

Now, **edit the `.env` file** with your preferred editor and set the following versions:

```env
# Set the PHP version
PHP_VERSION=8.3

# Set the database to PostgreSQL
DB_CONNECTION=pgsql

# Set the PostgreSQL version
POSTGRES_VERSION=17
```

### 4. Start Docker Containers

From within the `laradock` directory, run the following command to build and start the necessary containers.

```bash
docker-compose up -d nginx postgres workspace
```

### 5. Setup Laravel Application

接下來，我們需要設定 Laravel 應用程式本身。請使用 `laradock_setting.md` 中定義的佔位符來執行所有指令。

1.  **安裝依賴套件**:
    ```bash
    # From your project root, run:
    docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace composer install
    ```

2.  **設定環境變數**:
    -   從專案根目錄複製範例檔: `cp .env.example .env`
    -   執行以下指令來產生應用程式金鑰:
        ```bash
        docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan key:generate
        ```
    -   **重要**: 請手動編輯 `.env` 檔案，並根據 `laradock_setting.md` 中的指南填入您的資料庫設定。`DB_HOST` 應設為 `postgres`。

3.  **執行資料庫遷移**:
    ```bash
    docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan migrate
    ```

> **日常開發指令**: 對於所有日常開發指令（如執行測試、進入 Tinker 等），請參考 `laradock_setting.md` 中的指令模板與範例。

### 6. Access The Application

You should now be able to access the application in your browser at [http://localhost](http://localhost).

---

## Key API Endpoints

- `POST /api/register` - User registration
- `POST /api/sanctum/token` - Authentication (login)
- `GET /api/beers` - List user's tracked beers (supports sorting and brand filtering)
- `POST /api/beers` - Add new beer to tracking
- `POST /api/beers/{id}/count_actions` - Increment/decrement tasting count
- `GET /api/beers/{id}/tasting_logs` - View tasting history
- `GET /api/brands` - List all available brands

---

## Development Guidelines

- **Mobile-First Responsive Design**: The web interface is built with a mobile-first approach using Tailwind CSS. All features must be fully functional and aesthetically pleasing on mobile, tablet, and desktop screen sizes
  - 中文：採用行動優先設計（Tailwind CSS）；功能在手機、平板、桌機皆需完整且美觀。
- **Transaction Safety**: Count modifications and log entries must be handled atomically
  - 中文：次數修改與日誌寫入需原子性處理，確保資料一致性。
- **Performance**: Use dedicated count tables rather than aggregating logs for display
  - 中文：顯示時使用專用計數表而非彙總日誌，以提升效能。
- **Error Handling**: Follow standardized JSON error response format
  - 中文：遵循標準化 JSON 錯誤回應格式。
- **Authentication**: All protected endpoints require Bearer token authentication
  - 中文：所有受保護端點需使用 Bearer Token 驗證。

---

## Application Specification

>用途：描述產出。它定義了這個專案有哪些規格文件、它們在哪裡、以及它們的內容是什麼。它回答的是「What」的問題（我們有哪些規格？）

This project follows a Spec-driven development methodology. All specifications for behavior, APIs, and design are located in the `/spec` and `/docs` directories.
  - 中文：本專案採用規格驅動開發；行為、API 與設計的規格皆位於 `/spec` 與 `/docs`。

- **Design & Documentation (`/docs`)**: The `/docs` directory contains all high-level project documentation, including product requirements (`prd.md`), detailed feature designs, and architectural diagrams. This serves as the central repository for understanding the project's goals and architecture.
  - 中文：`/docs` 包含產品需求、功能設計與架構圖，是理解專案目標與架構的中樞。

  為了讓結構更清晰，`/docs` 目錄的組織方式如下：

  - **產品需求文件 (位於 `/docs/prd.md`)**:
    - 作為整個專案的最高層級文件，定義了產品的目標、功能規格與業務場景，是理解專案「為何做」與「做什麼」的起點。

  - **高階設計文件 (位於 `/docs/designs`)**:
    - 針對一個完整、獨立的功能模組，內容較宏觀，描述整個功能的技術選型、實作策略、路由、中介軟體、前後端如何配合等。
    - 可視為某個大功能的「**總體設計藍圖**」。

  - **流程圖與實作細節 (位於 `/docs/diagrams`)**:
    - 針對一個更具體的流程或頁面，內容更聚焦於細節。
    - **流程圖 (`flow-*.md`)**: 使用 Mermaid.js 語法，描繪使用者操作的每一步或後端處理的環節。
    - **頁面設計 (`*_design.md`)**: 描述某個頁面的元件構成、數據傳遞與開發挑戰。
    - 可視為總體設計藍圖下的「**詳細施工圖**」。

- **Technical Specifications (`/spec`)**: This directory contains detailed technical specifications, broken down as follows:
  - 中文：`/spec` 存放技術規格，並依下列子目錄劃分：
    - `acceptance/`: Defines the criteria for when a feature is considered complete.
      - 中文：定義功能何時視為完成的驗收標準。
    - `api/`: Contains the OpenAPI contract (`api.yaml`) and related API test cases.
      - 中文：包含 OpenAPI 合約（`api.yaml`）與相關 API 測試案例。
    - `database/`: Holds the database schema definition (`schema.yaml`).
      - 中文：資料庫結構定義（`schema.yaml`）。
    - `errors/`: Defines standardized error codes and formats (`error-codes.yaml`).
      - 中文：標準化錯誤碼與格式（`error-codes.yaml`）。
    - `features/`: Contains user-facing feature descriptions written in Gherkin (`.feature` files).
      - 中文：以 Gherkin 撰寫的使用者情境（`.feature` 檔）。
    - `format/`: Specifies standard data structures, such as the format for error responses.
      - 中文：標準資料結構規格（例如錯誤回應格式）。
    - `validation/`: Contains all data validation rules for different models (e.g., `beer-validation.yaml`).
      - 中文：各模型的資料驗證規則（如 `beer-validation.yaml`）。

---

## Contribution Guidelines

>用途：規範流程。它定義了開發者在為這個專案貢獻(contribute) 時，應該遵循的步驟、慣例和規則。它回答的是「How」的問題（我們該如何開發？）

### Feature Development Workflow

To maintain clarity, code quality, and avoid duplicate work, please follow this comprehensive process for every feature.
中文：為維持清晰、品質並避免重工，請依此流程開發每個功能。

**1. Before You Start: Specification & Planning**
中文：開始前請先確認規格並完成規劃。

Before writing any code, ensure the groundwork is laid out.
中文：在動手寫程式前，先把基礎準備好。

-   **Check for Existing Scenarios**: Review `spec/features/` to ensure a similar feature or scenario doesn't already exist.
    - 中文：檢查 `spec/features/`，避免重複現有情境或功能。
-   **Consult the Schema**: Refer to `spec/database/schema.yaml` to understand the required data structures.
    - 中文：參考 `spec/database/schema.yaml` 理解資料結構需求。
-   **Prepare Migrations**: Ensure a corresponding database migration file exists in `database/migrations/` for any new tables or columns.
    - 中文：為新表或欄位準備對應的遷移檔於 `database/migrations/`。

**2. During Development: Track Your Progress**
中文：開發過程中要持續追蹤並更新進度。

As you work on the feature, keep the team informed by updating its status.
中文：開發時請即時更新狀態，讓團隊知悉進度。

-   **Update Feature Status**: In the corresponding `.feature` file, update the status header for the `Scenario`. Use the following format:
    - 中文：在對應的 `.feature` 檔，更新該 `Scenario` 的狀態標頭，格式如下：

    ```gherkin
    # Status: TODO | IN_PROGRESS | DONE
    # Design: docs/diagrams/your-feature-flow.md
    # Test: tests/Feature/YourSpecificTest.php
    # UI: TODO | DONE
    # Backend: DONE
    ```
    -   **`# Status`**: The overall status of the feature.
        - 中文：整體狀態。
    -   **`# Design`**: Link to the design document or diagram.
        - 中文：設計文件或流程圖連結。
    -   **`# Test`**: Link to the primary test file.
        - 中文：主要測試檔連結。
    -   **`# UI`**: The development status of the UI.
        - 中文：UI 開發狀態。
    -   **`# Backend`**: The development status of the backend logic.
        - 中文：後端開發狀態。

> **重要提示**: 新增 `Scenario` 時，請務必在上方加上一行 `# 場景: ...` 的中文註解，以方便團隊成員快速理解。

**3. Before You Finish: Definition of Done & Quality Checks**
中文：完成前請確認 DoD 與品質檢查是否滿足。

Before a feature can be considered complete and ready for review, it must meet all the following criteria.
中文：功能在送審前必須符合以下所有條件。

-   **Run All Tests**: Before every `git commit`, run the entire automated test suite with code coverage.
    - 中文：每次 `git commit` 前都要執行完整自動化測試並產生覆蓋率。
    ```bash
    # From the HoldYourBeer project root:
    docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test --coverage
    ```
-   **Ensure All Tests Pass**: Only commit if all tests report a `PASS` status.
    - 中文：僅在所有測試皆通過時才進行提交。

-   **Final Checklist (Definition of Done)**:
    - 中文：完成檢查清單：
    -   [ ] Corresponding `.feature` file exists and is reviewed.
        - 中文：對應的 `.feature` 檔已存在且完成審閱。
    -   [ ] API specification in `spec/api/api.yaml` updated if needed.
        - 中文：必要時已更新 `spec/api/api.yaml`。
    -   [ ] Design & documentation in `/docs` updated if needed.
        - 中文：必要時已更新 `/docs` 內的設計與文件。
    -   [ ] Unit tests (Pest/PHPUnit) are written and passing.
        - 中文：單元測試（Pest/PHPUnit）已撰寫並通過。
    -   [ ] Behavior tests (Behat) for the feature file are passing.
        - 中文：行為測試（Behat）通過。
    -   [ ] API contract tests (Dredd) are passing.
        - 中文：API 合約測試（Dredd）通過。
    -   [ ] Responsive design works on mobile, tablet, and desktop.
        - 中文：響應式設計在手機、平板、桌機皆正常。
    -   [ ] CI/CD pipeline is green.
        - 中文：CI/CD pipeline 全綠。
    -   [ ] Code has been peer-reviewed and merged to main.
        - 中文：程式碼已完成同儕審查並合併至 main。

> **Note**: This project uses **PCOV** for code coverage analysis. The `--coverage` flag will generate a text report directly in your terminal after the tests run.
> 中文：本專案使用 **PCOV** 進行覆蓋率分析；加入 `--coverage` 會在終端機輸出報告。

> **Advanced Tip**: Consider setting up a Git pre-commit hook to automate this testing process. This is a great way to enforce code quality automatically.
> 中文：建議設定 Git pre-commit hook 自動執行測試，以自動化維持程式碼品質。
