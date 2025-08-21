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
docker-compose up -d nginx postgres
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
- **Transaction Safety**: Count modifications and log entries must be handled atomically
- **Performance**: Use dedicated count tables rather than aggregating logs for display
- **Error Handling**: Follow standardized JSON error response format
- **Authentication**: All protected endpoints require Bearer token authentication

---

## Application Specification

>用途：描述產出。它定義了這個專案有哪些規格文件、它們在哪裡、以及它們的內容是什麼。它回答的是「What」的問題（我們有哪些規格？）

This project follows a Spec-driven development methodology. All specifications for behavior, APIs, and design are located in the `/spec` and `/docs` directories.

- **Design & Documentation (`/docs`)**: The `/docs` directory contains all high-level project documentation, including product requirements (`prd.md`), detailed feature designs (e.g., `loading_states_design.md`), and architectural diagrams located in the `docs/diagrams` subdirectory. This serves as the central repository for understanding the project's goals and architecture.

- **Technical Specifications (`/spec`)**: This directory contains detailed technical specifications, broken down as follows:
    - `acceptance/`: Defines the criteria for when a feature is considered complete.
    - `api/`: Contains the OpenAPI contract (`api.yaml`) and related API test cases.
    - `database/`: Holds the database schema definition (`schema.yaml`).
    - `errors/`: Defines standardized error codes and formats (`error-codes.yaml`).
    - `features/`: Contains user-facing feature descriptions written in Gherkin (`.feature` files).
    - `format/`: Specifies standard data structures, such as the format for error responses.
    - `validation/`: Contains all data validation rules for different models (e.g., `beer-validation.yaml`).

---

## Contribution Guidelines

>用途：規範流程。它定義了開發者在為這個專案貢獻(contribute) 時，應該遵循的步驟、慣例和規則。它回答的是「How」的問題（我們該如何開發？）

### Commit-First Checks

To ensure the stability of the codebase, all developers should adhere to the following process before committing code:

1.  **Run All Tests Locally with Coverage**: Before every `git commit`, run the entire automated test suite with code coverage reporting to ensure changes are well-tested and have not broken existing functionality.

    ```bash
    # From the HoldYourBeer project root:
    docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test --coverage
    ```

2.  **Ensure All Tests Pass**: Only commit your changes if all tests are reporting a `PASS` status. A high coverage percentage is encouraged.

> **Note**: This project uses **PCOV** for code coverage analysis. The `--coverage` flag will generate a text report directly in your terminal after the tests run.

> **Advanced Tip**: Consider setting up a Git pre-commit hook to automate this testing process. A hook can run the test suite automatically whenever you attempt to commit, and only allow the commit to be created if the tests pass. This is a great way to enforce code quality automatically.

### Definition of Done Requirements

Every feature must meet these criteria:
- Corresponding `.feature` file exists and reviewed
- API specification updated in `api.yaml` if needed
- Unit tests (Pest/PHPUnit) written and passing
- Behavior tests (Behat) for feature file passing
- API contract tests (Dredd) passing
- Responsive design working on mobile/tablet/desktop
- CI/CD pipeline green
- Code peer-reviewed and merged to main

### Feature 狀態更新

當您開始或完成一個功能時，請務必更新對應的 `.feature` 檔案中，相關 `Scenario` 上方的狀態註解。

使用以下格式來追蹤每個功能的進度：

```gherkin
# Status: TODO | IN_PROGRESS | DONE
# Design: docs/diagrams/your-feature-flow.md
# Test: tests/Feature/YourSpecificTest.php
# UI: TODO | DONE
# Backend: DONE
```

-   **`# Status`**: 標示功能的整體狀態 (TODO, IN_PROGRESS, DONE)。
-   **`# Design`**: 連結到 `/docs` 目錄中對應的設計文件 (e.g., `docs/your-design-doc.md`) 或架構圖 (e.g., `docs/diagrams/your-feature-flow.md`)。
-   **`# Test`**: 連結到主要的測試檔案，例如 `tests/Feature/YourSpecificTest.php`。
-   **`# UI`**: 標示前端 UI 的開發進度。
-   **`# Backend`**: 標示後端邏輯的開發進度。

> **重要提示**: 新增 `Scenario` 時，請務必在上方加上一行 `# 場景: ...` 的中文註解，以方便團隊成員快速理解。

### Feature Development Process

To maintain clarity and avoid duplicate work, please follow these steps when developing a new feature.

1.  **Check for Existing Scenarios**: Before writing a new `.feature` file or adding a `Scenario`, please review the existing specifications in the `spec/features/` directory to ensure a similar scenario does not already exist.
2.  **Consult the Schema**: Before implementation, always refer to the database schema defined in `spec/database/schema.yaml` to understand the required data structures.
3.  **Create/Update Migrations**: Ensure that a corresponding database migration file exists in `database/migrations/` to reflect any new tables or columns required for your feature. This keeps the database synchronized with the application's needs.
