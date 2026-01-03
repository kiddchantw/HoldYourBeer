# HoldYourBeer

A simple application to track every beer you drink. Record the brand, the specific series/name, and automatically count how many times you've tasted each one.

This project is developed using a Spec-driven development approach.

---

## Technology Stack

- **Backend Framework**: Laravel 12
- **Web Frontend**: Livewire
- **Mobile App**: Flutter
- **Database**: PostgreSQL 17
- **PHP Version**: 8.3
- **Development Environment**: Laradock
- **Authentication**: Laravel Sanctum (email/password)

---

## API Endpoints

### API Versioning

This API uses **URL-based versioning**:

- **v1** (Current Stable): `/api/v1/*` - All core features
- **v2** (Enhanced): `/api/v2/*` - Enhanced features (e.g., brand pagination/search)

### V1 Endpoints

| Category | Endpoint | Description |
|----------|----------|-------------|
| Auth | `POST /api/v1/register` | User registration |
| Auth | `POST /api/v1/login` | Authentication |
| Auth | `POST /api/v1/logout` | Logout |
| Beers | `GET /api/v1/beers` | List user's tracked beers |
| Beers | `POST /api/v1/beers` | Add new beer |
| Beers | `POST /api/v1/beers/{id}/count_actions` | Increment/decrement count |
| Beers | `GET /api/v1/beers/{id}/tasting_logs` | View tasting history |
| Brands | `GET /api/v1/brands` | List all brands |
| Shops | `GET /api/v1/shops/suggestions` | Auto-complete suggestions |

### V2 Endpoints

All v1 endpoints plus:
- `GET /api/v2/brands?search=query&per_page=20` - Enhanced brand listing with pagination and search

---

## API Documentation

**Interactive documentation** is available via Laravel Scribe:

| Resource | URL |
|----------|-----|
| API Docs | http://localhost/docs |
| Postman Collection | http://localhost/docs.postman |
| OpenAPI Spec | http://localhost/docs.openapi |

**Features**:
- Interactive "Try It Out" functionality
- Complete request/response examples
- Bearer token authentication support
- Code examples in Bash and JavaScript

**Regenerate docs** after API changes:
```bash
php artisan scribe:generate
```

---

## Documentation

| Document | Description |
|----------|-------------|
| [API Usage Guide](docs/api-usage-guide.md) | Complete usage examples |
| [API Migration Guide](docs/api-migration-guide.md) | Migration to v1 guide |
| [API Versioning Strategy](docs/api-versioning.md) | Versioning best practices |
| [Cache Keys](docs/cache-keys.md) | All cache keys used |

---

## Development

For development setup and contribution guidelines, please refer to the project's internal documentation.

---

## License

This project is open-sourced software.
