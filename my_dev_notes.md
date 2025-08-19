# My Local Development Notes

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