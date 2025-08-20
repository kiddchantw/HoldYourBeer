# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

HoldYourBeer is a Laravel-based beer tracking application that follows a **Spec-driven development** approach. Users can register, track beers they've consumed, increment/decrement consumption counts, and manage tasting notes.

## Technology Stack

- **Backend**: Laravel 12 with PHP 8.3
- **Frontend**: Livewire for web interface
- **Database**: PostgreSQL 17 (Development), SQLite memory (Testing)
- **Development Environment**: Laradock (Docker-based)
- **Testing & Coverage**: PHPUnit with PCOV for code coverage
- **Design**: Mobile-first responsive design using Tailwind CSS

## Development Environment Setup

This project uses Laradock for containerized development with **dual environment configuration**:

### Environment Configuration
- **Development Environment (.env)**: Uses **PostgreSQL** for persistent data storage
- **Testing Environment (.env.testing)**: Uses **SQLite memory database** for fast, isolated tests

### Initial Setup Commands

```bash
# Initial setup (from project root)
git submodule add https://github.com/Laradock/laradock.git
cd laradock
cp env-example .env
# Edit .env to set PHP_VERSION=8.3, DB_CONNECTION=pgsql, POSTGRES_VERSION=17

# Start containers
docker-compose up -d nginx postgres

# Access workspace container for Laravel commands
docker-compose exec workspace bash

# Inside workspace container ({YOUR_CONTAINER_BASE_PATH})
composer install
cp .env.example .env
php artisan key:generate
# Configure .env with: DB_HOST=postgres, DB_PORT=5432, DB_DATABASE={YOUR_DB_NAME}
php artisan migrate
```

### Environment Files Setup

### 開發環境設定

> **本地開發設定**: 實際的資料庫帳密請參考 `my_dev_notes.md` → **「### Development Environment (.env)」** 區塊（此檔案不會上傳至雲端）
> - `{YOUR_DB_NAME}` = `DB_DATABASE` 值
> - `{YOUR_DB_USER}` = `DB_USERNAME` 值  
> - `{YOUR_DB_PASSWORD}` = `DB_PASSWORD` 值

**.env (Development)**
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

**.env.testing (Testing)**
```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
```

Access application at: http://localhost

## Common Development Commands

Since this is a Laravel project without package.json, use these Laravel/PHP commands:

**IMPORTANT: All Laravel/PHP commands must be run inside the Laradock workspace container using this template:**

```bash
# Command Template (run from project root)
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace <YOUR_COMMAND_HERE>

# Examples:
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace composer install
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan migrate
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace php artisan test

# Alternative: Enter container interactively
docker-compose -f {YOUR_LARADOCK_PATH}/docker-compose.yml exec -w {YOUR_PROJECT_PATH} workspace bash
```

> **實際路徑設定**: 具體的路徑請參考 `my_dev_notes.md` → **「## 本地路徑」** 區塊
> - `{YOUR_PROJECT_PATH}` = 專案在 Docker 容器內的絕對路徑
> - `{YOUR_LARADOCK_PATH}` = Laradock docker-compose.yml 的相對路徑

**Standard Laravel Commands (to be run inside workspace container):**

```bash
composer install                    # Install PHP dependencies
php artisan migrate                 # Run database migrations
php artisan migrate:fresh --seed    # Reset and seed database
php artisan key:generate           # Generate application key
php artisan serve                  # Development server (if not using Docker)

# Testing (based on Definition of Done requirements)
php artisan test                   # Run all tests with PHPUnit
php artisan test --coverage       # Run tests with PCOV code coverage report
vendor/bin/pest                   # Unit tests (Pest/PHPUnit) - alternative
vendor/bin/behat                  # Behavior tests for .feature files
dredd                             # API contract tests
```

## Architecture & Spec-Driven Development

This project follows a comprehensive specification-first approach:

### Core Business Logic
- **Users**: Registration and authentication via Laravel Sanctum
- **Brands**: Beer brand entities (e.g., "Guinness")  
- **Beers**: Specific beer variants (e.g., "Guinness Draught")
- **Tasting Count**: Optimized count tracking per user/beer
- **Tasting Logs**: Audit trail of increment/decrement actions

### Specification Structure
- `/spec/api/api.yaml` - OpenAPI 3.0 specification for all endpoints
- `/spec/features/` - Gherkin behavior specifications
  - `user-registration.feature`
  - `beer_tracking/adding_a_beer.feature`
  - `beer_tracking/viewing_the_list.feature` 
  - `beer_tracking/managing_tastings.feature`
  - `beer_tracking/viewing_tasting_history.feature`
- `/spec/acceptance/definition-of-done.md` - Universal completion criteria
- `/spec/format/error-response.json` - Standardized API error format

### API Design Patterns
- RESTful endpoints with `/api/` prefix
- Bearer token authentication via Sanctum
- Count modifications use dedicated `count_actions` endpoint with `increment`/`decrement` actions
- All responses follow consistent JSON structure defined in OpenAPI spec

## Key API Endpoints

- `POST /api/register` - User registration
- `POST /api/sanctum/token` - Authentication (login)
- `GET /api/beers` - List user's tracked beers (supports sorting and brand filtering)
- `POST /api/beers` - Add new beer to tracking
- `POST /api/beers/{id}/count_actions` - Increment/decrement tasting count
- `GET /api/beers/{id}/tasting_logs` - View tasting history
- `GET /api/brands` - List all available brands

## Definition of Done Requirements

Every feature must meet these criteria:
- Corresponding `.feature` file exists and reviewed
- API specification updated in `api.yaml` if needed
- Unit tests (Pest/PHPUnit) written and passing
- Behavior tests (Behat) for feature file passing
- API contract tests (Dredd) passing
- Responsive design working on mobile/tablet/desktop
- CI/CD pipeline green
- Code peer-reviewed and merged to main

## Development Guidelines

- **Mobile-First Design**: All UI must be fully functional on mobile devices first
- **Transaction Safety**: Count modifications and log entries must be handled atomically
- **Performance**: Use dedicated count tables rather than aggregating logs for display
- **Error Handling**: Follow standardized JSON error response format
- **Authentication**: All protected endpoints require Bearer token authentication

## File Organization

- Core Laravel structure with additional spec-driven documentation
- Documentation in `/docs/` including system flow diagrams
- Complete behavioral specifications in `/spec/` directory
- Mobile-responsive Livewire components for web interface