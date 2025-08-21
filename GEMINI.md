# GEMINI.md

## Project Overview

This project is a web application called "HoldYourBeer" for tracking beer consumption. Users can record the brand and name of beers they've tried, and the application automatically counts the number of times each beer has been tasted.

The project is built on the Laravel framework (version 12) for the backend and uses a Livewire frontend. It utilizes **dual database environment**:
- **Development**: PostgreSQL (version 17) for persistent data storage
- **Testing**: SQLite memory database for fast, isolated tests

The application requires PHP 8.3, uses PHPUnit for testing with PCOV for code coverage, and the development environment is managed using Laradock.

The application follows a spec-driven development approach, with API specifications in OpenAPI format and feature descriptions in Gherkin.

## Building and Running

The project uses Laradock for a consistent development environment. The following are the key commands for setting up and running the application:

**1. Start the Docker Containers:**

```bash
# From the laradock directory
docker-compose up -d nginx postgres
```

**2. Install Dependencies:**

```bash
# Enter the workspace container
docker-compose exec workspace bash

# Install PHP dependencies
composer install

# Install frontend dependencies
npm install
```

**3. Run the Application:**

```bash
# From the workspace container
# Run the development server
composer run dev
```

**4. Environment Configuration:**

This project uses dual environment configuration:

### 開發環境設定

> **📋 變數對映**: 請參考 `my_dev_notes.md` → **「🔧 變數對映表 (Placeholder Variables)」** 來查找 `{YOUR_DB_NAME}`、`{YOUR_DB_USER}` 和 `{YOUR_DB_PASSWORD}` 的實際值（此檔案不會上傳至雲端）

**.env (Development Environment)**
```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE={YOUR_DB_NAME}
DB_USERNAME={YOUR_DB_USER}
DB_PASSWORD={YOUR_DB_PASSWORD}
```

### 測試環境設定

> **測試環境設定**: 使用固定參數，不需修改（參考 `my_dev_notes.md` → **「### Testing Environment (.env.testing)」**）

**.env.testing (Testing Environment)**
```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
```

**5. Run Tests:**

All `php artisan` or test commands **must** be run inside the Laradock `workspace` container.

Use the following command template from the project root:

```bash
# Command Template
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace <YOUR_COMMAND_HERE>
```

> **📋 變數對映**: 請參考 `my_dev_notes.md` → **「🔧 變數對映表 (Placeholder Variables)」** 來查找 `{YOUR_PROJECT_PATH}` 和 `{YOUR_LARADOCK_PATH}` 的實際值

### Examples:

```bash
# Run all automated tests (automatically uses .env.testing with SQLite)
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test

# Run tests with PCOV code coverage report
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test --coverage

# Run tests in a specific file
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test --filter=BeerCreationTest

# Run development commands (uses .env with PostgreSQL)
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan migrate
```

## Development Conventions

*   **Language Preference:** Whenever possible, respond in Traditional Chinese.
*   **Spec-Driven Development:** All features are defined in the `/spec` directory before implementation.
*   **Commit-First Checks:** Before committing any code, all tests must be run and pass.
*   **Feature Status Updates:** The status of features should be updated in the corresponding `.feature` files.
*   **Mobile-First Responsive Design:** The UI is built with a mobile-first approach using Tailwind CSS.
