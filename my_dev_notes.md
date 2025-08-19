# My Local Development Notes

## 🔧 變數對映表 (Placeholder Variables)

以下是專案文件（如 `GEMINI.md`, `CLAUDE.md`）中使用的佔位符及其對應的實際值：

### 路徑變數
| 佔位符 | 實際值 | 說明 |
|-------|--------|------|
| `{YOUR_PROJECT_PATH}` | `/var/www/side/HoldYourBeer` | 專案在 Docker 容器內的絕對路徑 |
| `{YOUR_LARADOCK_PATH}` | `../../laradock` | 從專案根目錄到 Laradock docker-compose.yml 的相對路徑 |

### 資料庫變數
| 佔位符 | 實際值 | 說明 |
|-------|--------|------|
| `{YOUR_DB_NAME}` | `holdyourbeer` | 開發環境的資料庫名稱 |
| `{YOUR_DB_USER}` | `default` | 開發環境的資料庫使用者 |
| `{YOUR_DB_PASSWORD}` | `secret` | 開發環境的資料庫密碼 |

### 容器基礎路徑變數
| 佔位符 | 實際值 | 說明 |
|-------|--------|------|
| `{YOUR_CONTAINER_BASE_PATH}` | `/var/www/side/HoldYourBeer` | 同 `{YOUR_PROJECT_PATH}` |

> **💡 使用說明**: 當你在其他文件中看到 `{變數名稱}` 時，請參考上表來替換成對應的實際值。

---

## Environment Configuration

This project uses **dual environment configuration**:

### Development Environment (.env)
- **Database**: PostgreSQL (persistent storage)
- **Purpose**: Local development, data persists between sessions
- **Configuration**:
  ```env
  DB_CONNECTION=pgsql
  DB_HOST=postgres
  DB_PORT=5432
  DB_DATABASE=default
  DB_USERNAME=default
  DB_PASSWORD=secret
  ```

### Testing Environment (.env.testing)
- **Database**: SQLite memory database
- **Purpose**: Automated tests, fast and isolated
- **Code Coverage**: Uses PCOV extension for coverage reports
- **Configuration**:
  ```env
  APP_ENV=testing
  DB_CONNECTION=sqlite
  DB_DATABASE=:memory:
  CACHE_DRIVER=array
  SESSION_DRIVER=array
  QUEUE_CONNECTION=sync
  ```

**Note**: Laravel automatically loads `.env.testing` when running tests, so development and testing environments are completely separated.

## 本地路徑
- 專案路徑: `/var/www/side/HoldYourBeer`
- Laradock docker-compose.yml 路徑: `../../laradock`

## Running Commands in Laradock

All `php artisan` or test commands **must** be run inside the Laradock `workspace` container.

Use the following command template from the `HoldYourBeer` project root:

```bash
# Command Template
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace <YOUR_COMMAND_HERE>
```

### Examples:

```bash
# Development commands (uses .env with PostgreSQL)
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace php artisan migrate
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace php artisan tinker

# Testing commands (automatically uses .env.testing with SQLite)
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace php artisan test
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace php artisan test --coverage  # With PCOV coverage report
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace php artisan test --filter=BeerCreationTest
```

---

---

## 完整設定步驟

詳細的本地開發環境設定，請參考 `README.md` → **「## Local Development Setup」** 章節