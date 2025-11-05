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

### API Versioning

This API uses **URL-based versioning**. All endpoints are prefixed with a version number:

- **v1** (Current Stable): `/api/v1/*` - All core features
- **v2** (Enhanced): `/api/v2/*` - Enhanced features (e.g., brand pagination/search)

⚠️ **Legacy non-versioned endpoints** (e.g., `/api/beers`) are **deprecated** and will be removed on **2026-12-31**. Please migrate to versioned endpoints.

📖 For detailed versioning information, see [API Versioning Guide](docs/api-versioning.md)

### V1 Endpoints (Current Stable)

- `POST /api/v1/register` - User registration
- `POST /api/v1/login` - Authentication (login)
- `POST /api/v1/logout` - Logout
- `GET /api/v1/beers` - List user's tracked beers (supports sorting and brand filtering)
- `POST /api/v1/beers` - Add new beer to tracking
- `POST /api/v1/beers/{id}/count_actions` - Increment/decrement tasting count
- `GET /api/v1/beers/{id}/tasting_logs` - View tasting history
- `GET /api/v1/brands` - List all available brands

### V2 Endpoints (Enhanced Features)

All v1 endpoints are available in v2, plus:

- `GET /api/v2/brands?search=query&per_page=20&page=1` - Enhanced brand listing with pagination and search

### API Documentation

**Interactive documentation** is available via Laravel Scribe:

- **View docs**: http://localhost/docs
- **Postman Collection**: http://localhost/docs.postman
- **OpenAPI Spec**: http://localhost/docs.openapi

**Features**:
- 🔍 Interactive "Try It Out" functionality
- 📝 Complete request/response examples
- 🔐 Bearer token authentication support
- 🌐 Code examples in Bash and JavaScript

**Regenerate docs** after API changes:
```bash
php artisan scribe:generate
```

**📚 API Documentation Resources**:
- 📖 [API Documentation Guide](docs/api-documentation.md) - Scribe 設定與使用
- 💡 [API Usage Guide](docs/api-usage-guide.md) - 完整使用範例、業務邏輯說明
- 🔄 [API Migration Guide](docs/api-migration-guide.md) - 從舊版遷移至 v1 的完整指南
- 🔖 [API Versioning Strategy](docs/api-versioning.md) - 版本控制策略與最佳實踐

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

### 0. Feature Proposal - 功能提案

#### Before Starting Development
- **Discuss**: Share your idea in team chat/meeting first
  - 討論：先在團隊聊天/會議中分享你的想法
- **Spec Review**: Ensure the feature aligns with project goals
  - 規格審查：確保功能符合專案目標
- **Scope Definition**: Define clear acceptance criteria
  - 範圍定義：定義清楚的驗收標準
- **Approval**: Get team consensus before starting implementation
  - 批准：在開始實作前獲得團隊共識

#### Proposal Template
```markdown
## Feature Request: [簡潔的功能名稱]

### Problem Statement
What problem does this feature solve?

### Proposed Solution
Brief description of the proposed solution

### Acceptance Criteria
- [ ] Criterion 1
- [ ] Criterion 2
- [ ] Criterion 3

### Impact Assessment
- **Effort**: Low/Medium/High
- **Priority**: Low/Medium/High
- **Dependencies**: Any blocking items?

### Alternative Solutions Considered
What other approaches were considered?
```

### 1. Development Process - 開發流程

#### Spec Automation Tools - 規格自動化工具

本專案提供自動化工具來維護規格與測試的同步，大幅減少手動維護的工作量：

**Available Commands - 可用指令**:
```bash
# 檢查規格與測試的一致性
php artisan spec:check

# 自動同步規格文件與測試文件
php artisan spec:sync

# 在 Laradock 環境中使用
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan spec:check
```

**Key Features - 主要功能**:
- 🔍 **自動檢查**: 驗證 `.feature` 文件與測試文件的對應關係
- 🔄 **智能同步**: 根據測試文件自動更新狀態追蹤表格
- 📊 **覆蓋率報告**: 產生規格覆蓋率統計
- 🏃 **安全預覽**: `--dry-run` 模式安全檢視變更
- ⚙️ **CI/CD 整合**: `--strict` 和 `--ci` 模式支援自動化流程

**Usage Examples - 使用範例**:
```bash
# 每日開發流程
php artisan spec:check                    # 檢查當前狀況
php artisan spec:sync --dry-run           # 預覽同步變更
php artisan spec:sync                     # 執行同步

# CI/CD 整合
php artisan spec:check --strict           # 嚴格檢查，不一致時返回錯誤碼
php artisan spec:check --ci               # 輸出 JSON 格式報告
```

> **📖 詳細使用指南**: 請參考 [`docs/spec-automation.md`](docs/spec-automation.md) 獲得完整的使用說明、疑難排解和最佳實踐。

#### Feature Development Workflow

To maintain clarity, code quality, and avoid duplicate work, please follow this comprehensive process for every feature.
為維持清晰、品質並避免重工，請依此流程開發每個功能。

**Quick Reference - 快速參考**:
```
0. 💡 Propose → 1. 📋 Plan & Spec → 2. 🧪 Write Test → 3. 💻 Write Code → 4. ✅ Refactor → 5. 📝 Update Status → 6. 🚀 Commit & PR
```

**Detailed Steps - 詳細步驟**:

**1. Before You Start: Specification & Planning**
開始前請先確認規格並完成規劃。

Before writing any code, ensure the groundwork is laid out.
在動手寫程式前，先把基礎準備好。

-   **Check for Existing Scenarios**: Review `spec/features/` to ensure a similar feature or scenario doesn't already exist.
    - 檢查 `spec/features/`，避免重複現有情境或功能。
-   **Consult the Schema**: Refer to `spec/database/schema.yaml` to understand the required data structures.
    - 參考 `spec/database/schema.yaml` 理解資料結構需求。
-   **Prepare Migrations**: Ensure a corresponding database migration file exists in `database/migrations/` for any new tables or columns.
    - 為新表或欄位準備對應的遷移檔於 `database/migrations/`。

**2. During Development: Test-Driven Development**
開發過程中採用測試驅動開發。

Follow TDD principles: write tests first, then implement the minimal code to pass.
遵循 TDD 原則：先寫測試，再實作最小程式碼通過測試。

- **Red Phase**: Write a failing test that describes the desired behavior
  - 紅燈階段：撰寫描述期望行為的失敗測試
- **Green Phase**: Write minimal code to make the test pass
  - 綠燈階段：撰寫最小程式碼讓測試通過
- **Refactor Phase**: Clean up the code while keeping tests green
  - 重構階段：清理程式碼但保持測試通過

**3. During Development: Track Your Progress**
開發過程中要持續追蹤並更新進度。

As you work on the feature, keep the team informed by updating its status.
開發時請即時更新狀態，讓團隊知悉進度。

-   **Automated Status Tracking**: 使用自動化工具來維護狀態追蹤：
    ```bash
    # 開發過程中檢查狀態
    php artisan spec:check
    
    # 完成測試後自動同步狀態
    php artisan spec:sync --dry-run    # 先預覽
    php artisan spec:sync              # 執行同步
    ```

-   **Status Table Format**: The automation tools maintain this standard format:
    - 自動化工具會維護以下標準格式：

    ```gherkin
    # 1. Status: TODO | IN_PROGRESS | DONE
    # 2. Design: docs/diagrams/your-feature-flow.md
    # 3. Test: tests/Feature/YourSpecificTest.php
    # 4. Scenario Status Tracking:
    # | Scenario Name                    | Status        | Test Method                    | UI  | Backend |
    # |----------------------------------|---------------|--------------------------------|-----|---------|
    # | Scenario 1 description          | DONE          | test_scenario_1                | DONE| DONE    |
    # | Scenario 2 description          | IN_PROGRESS   | test_scenario_2                | DONE| TODO    |
    # | Scenario 3 description          | TODO          | test_scenario_3                | TODO| TODO    |
    ```
    -   **`# Status`**: The overall status of the feature (auto-inferred from tests)
        - 整體狀態：根據測試結果自動推斷
    -   **`# Design`**: Link to the design document or diagram (auto-generated path)
        - 設計文件連結：自動生成路徑
    -   **`# Test`**: Link to the primary test file (auto-detected or inferred)
        - 測試檔連結：自動偵測或推斷
    -   **Scenario Status Tracking**: Auto-updated based on test methods
        - 情境狀態追蹤：根據測試方法自動更新

> **重要提示**: 新增 `Scenario` 時，請務必在上方加上一行 `# 場景: ...` 的中文註解，以方便團隊成員快速理解。

**4. During Development: Add Test Coverage Documentation**
開發過程中要加上測試覆蓋文件。

As you write tests, document the relationship between test classes and spec scenarios.
撰寫測試時，請記錄測試類別與規格場景的對應關係。

-   **Add Test Class Documentation**: In each test class, add comprehensive documentation above the class declaration using the following format:
    - 在每個測試類別上方，使用以下格式加上完整的文件註解：

    ```php
    /**
     * @covers \spec\features\feature_name\scenario_name.feature
     * 
     * Scenarios covered:
     * - Scenario 1 description
     * - Scenario 2 description
     * 
     * Test coverage:
     * - Specific functionality tested
     * - API endpoints covered
     * - Database operations verified
     */
    class YourTestClass extends TestCase
    ```

    This documentation helps developers understand:
    - 此文件註解幫助開發者理解：
    -   **Which spec scenarios are covered** by the test class
        - 測試類別覆蓋了哪些規格場景
    -   **What specific functionality** is being tested
        - 測試了哪些具體功能
    -   **The relationship** between tests and specifications
        - 測試與規格之間的關係

**5. Before You Finish: Definition of Done & Quality Checks**
完成前請確認 DoD 與品質檢查是否滿足。

Before a feature can be considered complete and ready for review, it must meet all the following criteria.
功能在送審前必須符合以下所有條件。

-   **Automated Spec-Test Validation**: 使用自動化工具驗證規格與測試的一致性：
    ```bash
    # 嚴格檢查 - CI/CD 整合必備
    php artisan spec:check --strict
    
    # 如果檢查失敗，先同步規格狀態
    php artisan spec:sync
    
    # 在 Laradock 環境中
    docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan spec:check --strict
    ```

-   **Run All Tests**: Before every `git commit`, run the entire automated test suite with code coverage.
    - 每次 `git commit` 前都要執行完整自動化測試並產生覆蓋率。
    - **Minimum Coverage**: 80% for new features, 90% for critical paths
    - **最低覆蓋率**: 新功能 80%，關鍵路徑 90%
    ```bash
    # From the HoldYourBeer project root:
    php artisan test --coverage
    
    # For specific test files:
    php artisan test --coverage --filter=YourTestClass
    
    # For coverage report in Laradock (推薦使用 PHPUnit):
    docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace ./vendor/bin/phpunit --coverage-text
    
    # 或使用 artisan test (可能無法顯示詳細覆蓋率):
    docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test --coverage
    ```
    > **注意**: 在 Laradock 環境中，請參考 `laradock_setting.md` 了解完整的指令執行方式。
    
-   **Ensure All Tests Pass**: Only commit if all tests report a `PASS` status and spec validation passes.
    - 僅在所有測試皆通過且規格驗證成功時才進行提交。
-   **Git Workflow**: Follow conventional commit format and branch naming.
    - **Git 工作流程**: 遵循常規提交格式與分支命名。
    ```bash
    # Branch naming convention:
    git checkout -b feature/beer-tracking
    git checkout -b bugfix/login-validation
    git checkout -b hotfix/security-patch
    
    # Commit message format:
    git commit -m "feat: add beer tasting history tracking"
    git commit -m "fix: resolve authentication token expiration issue"
    git commit -m "docs: update API documentation for beer endpoints"
    ```

-   **Final Checklist (Definition of Done)**:
    - 完成檢查清單：
    -   [ ] **Spec-Test Validation**: `php artisan spec:check --strict` passes without errors.
        - **規格測試驗證**：`php artisan spec:check --strict` 執行無錯誤。
    -   [ ] Corresponding `.feature` file exists and status is auto-synced.
        - 對應的 `.feature` 檔已存在且狀態已自動同步。
    -   [ ] Test files have proper `@covers` annotations linking to spec files.
        - 測試檔案具備正確的 `@covers` 註解連結到規格檔案。
    -   [ ] API specification in `spec/api/api.yaml` updated if needed.
        - 必要時已更新 `spec/api/api.yaml`。
    -   [ ] Design & documentation in `/docs` updated if needed.
        - 必要時已更新 `/docs` 內的設計與文件。
    -   [ ] Unit tests (Pest/PHPUnit) are written and passing.
        - 單元測試（Pest/PHPUnit）已撰寫並通過。
    -   [ ] Behavior tests (Behat) for the feature file are passing.
        - 行為測試（Behat）通過。
    -   [ ] API contract tests (Dredd) are passing.
        - API 合約測試（Dredd）通過。
    -   [ ] Responsive design works on mobile, tablet, and desktop.
        - 響應式設計在手機、平板、桌機皆正常。
    -   [ ] CI/CD pipeline is green.
        - CI/CD pipeline 全綠。
    -   [ ] Code has been peer-reviewed and merged to main.
        - 程式碼已完成同儕審查並合併至 main。

> **Note**: This project uses **PCOV** for code coverage analysis. 
> 本專案使用 **PCOV** 進行覆蓋率分析。
> 
> **重要提醒**: 由於 Laravel 12 的 `php artisan test --coverage` 指令可能無法正確顯示 PCOV 覆蓋率報告，建議直接使用 PHPUnit：
> ```bash
> # 推薦使用 - 直接 PHPUnit 指令
> docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace ./vendor/bin/phpunit --coverage-text
> 
> # 或者使用 artisan test（可能不會顯示覆蓋率詳細資訊）
> docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test --coverage
> ```

> **Advanced Tip**: Consider setting up a Git pre-commit hook to automate spec validation and testing:
> 建議設定 Git pre-commit hook 自動執行規格驗證與測試：
>
> ```bash
> #!/bin/bash
> # .git/hooks/pre-commit
> 
> # Run spec validation
> php artisan spec:check --strict
> if [ $? -ne 0 ]; then
>     echo "❌ Spec validation failed. Run 'php artisan spec:sync' to fix."
>     exit 1
> fi
> 
> # Run tests
> php artisan test --coverage --min=80
> ```

### 2. Code Standards - 程式碼標準

#### Coding Conventions
- **PHP Standards**: Follow PSR-12 coding standards
  - 遵循 PSR-12 程式碼標準
- **Laravel Conventions**: Use Laravel naming conventions and best practices
  - 使用 Laravel 命名慣例與最佳實踐
- **Database**: Use snake_case for table/column names, singular table names
  - 資料庫：表名/欄位名使用 snake_case，表名使用單數形式

#### File Organization
- **Controllers**: Place in appropriate namespace (`App\Http\Controllers\Api\` for API)
  - 控制器：放在適當的命名空間（API 放在 `App\Http\Controllers\Api\`）
- **Models**: Use singular form, place in `App\Models\`
  - 模型：使用單數形式，放在 `App\Models\`
- **Tests**: Mirror the application structure in `tests/Feature/` and `tests/Unit/`
  - 測試：在 `tests/Feature/` 和 `tests/Unit/` 中鏡像應用程式結構

### 3. Pull Request Guidelines - PR 指南

#### Before Submitting
- **Branch**: Ensure your branch is up-to-date with main
  - 分支：確保你的分支與 main 同步
- **Tests**: All tests must pass with coverage requirements met
  - 測試：所有測試必須通過且滿足覆蓋率要求
- **Documentation**: Update relevant documentation and specs
  - 文件：更新相關文件與規格

#### PR Template
```markdown
## Description
Brief description of the changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests added/updated
- [ ] Feature tests added/updated
- [ ] All tests passing
- [ ] Coverage requirements met

## Checklist
- [ ] Code follows project standards
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] Spec files updated if needed
```

### 4. Review Process - 審查流程

#### Code Review Requirements
- **Minimum Reviewers**: At least 1 team member approval required
  - **最少審查者**: 至少需要 1 位團隊成員批准
- **Response Time**: Reviewers should respond within 24 hours
  - **回應時間**: 審查者應在 24 小時內回應
- **Merge Policy**: No direct merges to main; all changes go through PR
  - **合併政策**: 禁止直接合併到 main；所有變更都需通過 PR

#### Review Checklist
- [ ] Code follows established patterns
- [ ] Tests are comprehensive and meaningful
- [ ] Error handling is appropriate
- [ ] Performance considerations addressed
- [ ] Security implications reviewed
