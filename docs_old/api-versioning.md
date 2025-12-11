# API Version Control Implementation

This document describes the API versioning strategy implemented in the HoldYourBeer application.

## Overview

The HoldYourBeer API uses **URL-based versioning** to maintain backward compatibility while allowing the API to evolve with new features and improvements.

## Versioning Strategy

### URL Structure

All API endpoints are versioned using a URL prefix:

```
/api/v{version}/{endpoint}
```

Examples:
- `/api/v1/beers` - Version 1 beer endpoint
- `/api/v2/brands` - Version 2 brand endpoint

### Current Versions

#### Version 1 (v1) - Current Stable
**Status**: ✅ Active
**Base URL**: `/api/v1`

Core features:
- User authentication (register, login, logout)
- Beer tracking (add, list, count actions)
- Tasting logs (view history)
- Brand management (list brands)

#### Version 2 (v2) - Enhanced
**Status**: 🚀 Example Implementation
**Base URL**: `/api/v2`

Enhanced features:
- All v1 features (inherited)
- **Enhanced brand endpoint**: Pagination and search support
- Additional features can be added as needed

### Legacy Non-Versioned Routes
**Status**: ⚠️ Deprecated
**Sunset Date**: 2026-12-31

Non-versioned routes (e.g., `/api/beers`) are maintained for backward compatibility but are **deprecated**. They currently redirect to v1 endpoints and include deprecation warning headers.

All clients should migrate to versioned endpoints before the sunset date.

## Implementation Details

### Directory Structure

```
app/Http/Controllers/Api/
├── V1/
│   ├── AuthController.php
│   ├── BeerController.php
│   └── BrandController.php
└── V2/
    └── BrandController.php (enhanced with pagination)
```

### Route Configuration

Routes are organized in `routes/api.php`:

```php
// Version 1 routes (current stable)
Route::prefix('v1')->name('v1.')->group(function () {
    // All v1 endpoints...
});

// Version 2 routes (enhanced)
Route::prefix('v2')->name('v2.')->group(function () {
    // V2 endpoints (inherits from v1 where unchanged)
});

// Legacy routes (deprecated)
Route::middleware('api.deprecation')->group(function () {
    // Non-versioned routes for backward compatibility
});
```

### Deprecation Middleware

The `ApiDeprecation` middleware adds warning headers to legacy non-versioned requests:

```
X-API-Deprecation: true
X-API-Deprecation-Info: Non-versioned API endpoints are deprecated. Please use /api/v1/* endpoints.
X-API-Sunset-Date: 2026-12-31
X-API-Current-Version: v1
Link: <https://example.com/docs>; rel="deprecation"
```

Location: `app/Http/Middleware/ApiDeprecation.php`

### Version Inheritance

New API versions can inherit unchanged endpoints from previous versions:

```php
// V2 inherits authentication from V1
Route::post('/register', [V1AuthController::class, 'register']);
Route::post('/login', [V1AuthController::class, 'token']);

// V2 has enhanced brand endpoint
Route::get('/brands', [V2BrandController::class, 'index']);
```

This allows:
- **Code reuse**: No duplication for unchanged features
- **Selective enhancement**: Only override endpoints that change
- **Easier maintenance**: Bug fixes in v1 automatically apply to v2

## Adding a New API Version

### Step 1: Create Controller Namespace

```bash
mkdir app/Http/Controllers/Api/V3
```

### Step 2: Create or Copy Controllers

For new features:
```php
<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
// Your implementation
```

For modified features, copy from previous version and enhance:
```bash
cp app/Http/Controllers/Api/V2/BrandController.php \
   app/Http/Controllers/Api/V3/BrandController.php
```

Then update namespace and add new features.

### Step 3: Add Routes

In `routes/api.php`:

```php
use App\Http\Controllers\Api\V3\BrandController as V3BrandController;

Route::prefix('v3')->name('v3.')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        // Inherit from V2 or create new endpoints
        Route::get('/brands', [V3BrandController::class, 'index']);
    });
});
```

### Step 4: Update Scribe Documentation

In `config/scribe.php`, add the new version to group ordering:

```php
'groups' => [
    'order' => [
        'V1 - Authentication',
        'V1 - Beer Tracking',
        // ...
        'V3 - Beer Brands', // Add new version groups
    ],
],
```

Update the intro text to document the new version.

### Step 5: Generate Documentation

```bash
php artisan scribe:generate
```

## Migration Guide for API Clients

### Checking Current Version Usage

Clients can identify if they're using deprecated endpoints by checking response headers:

```bash
curl -I https://api.example.com/api/beers
```

Look for:
```
X-API-Deprecation: true
X-API-Sunset-Date: 2026-12-31
```

### Updating to Versioned Endpoints

**Before (deprecated):**
```javascript
fetch('/api/beers')
```

**After (v1 stable):**
```javascript
fetch('/api/v1/beers')
```

**Using v2 (enhanced features):**
```javascript
// V2 brand endpoint with pagination and search
fetch('/api/v2/brands?search=Guinness&per_page=20&page=1')
```

### Version Selection Strategy

1. **Use v1** for production stability
2. **Use v2** if you need enhanced features (pagination, search)
3. **Never use non-versioned** endpoints in new code

## Best Practices

### For API Developers

1. **Semantic Versioning**: Increment version for breaking changes
2. **Backward Compatibility**: Maintain previous versions for deprecation period
3. **Clear Documentation**: Document all changes between versions
4. **Deprecation Warnings**: Always add deprecation headers before removal
5. **Sufficient Notice**: Provide at least 12 months deprecation period

### For API Consumers

1. **Always Use Versioned URLs**: Never rely on non-versioned endpoints
2. **Monitor Deprecation Headers**: Check for `X-API-Deprecation` in responses
3. **Test Before Migrating**: Verify new version behavior before switching
4. **Update Gradually**: Migrate endpoints one at a time
5. **Document Version Dependencies**: Track which API version your app uses

## Version Lifecycle

```
Development → Beta → Stable → Deprecated → Sunset
    ↓           ↓       ↓         ↓          ↓
   v3.0    →  v3.0  →  v3.0  →   v2.0   →  v1.0
                                (12 months) (removed)
```

1. **Development**: New version in active development
2. **Beta**: Feature complete, available for testing
3. **Stable**: Recommended for production use
4. **Deprecated**: Replaced by newer version, sunset date announced
5. **Sunset**: Version removed from API

## Testing Versioned APIs

### Testing All Versions

```bash
# Test v1 endpoints
curl -H "Authorization: Bearer {token}" https://api.example.com/api/v1/beers

# Test v2 endpoints
curl -H "Authorization: Bearer {token}" https://api.example.com/api/v2/brands?search=test

# Test deprecated endpoints (should have deprecation headers)
curl -I -H "Authorization: Bearer {token}" https://api.example.com/api/beers
```

### Automated Testing

Create version-specific tests:

```php
// tests/Feature/Api/V1/BeerControllerTest.php
// tests/Feature/Api/V2/BrandControllerTest.php
```

## Monitoring

### Key Metrics to Track

1. **Usage by version**: Which versions are most used
2. **Deprecated endpoint usage**: Track legacy endpoint calls
3. **Migration progress**: Percentage of clients on current version
4. **Error rates by version**: Monitor for version-specific issues

### Logging

All API requests are logged with version information in `storage/logs/api.log`:

```
[2025-11-05 10:00:00] API Request: GET /api/v1/beers
[2025-11-05 10:00:01] API Request: GET /api/beers (DEPRECATED)
```

## FAQ

### Q: Can I use multiple versions simultaneously?
**A**: Yes, clients can call different versions for different endpoints. However, we recommend standardizing on one version per client application.

### Q: How long are versions supported?
**A**: Stable versions are supported for at least 12 months after deprecation is announced.

### Q: What happens after sunset date?
**A**: After the sunset date, the deprecated version will return 410 Gone errors.

### Q: Can breaking changes be made within a version?
**A**: No. Breaking changes require a new version number. Within a version, only backward-compatible changes are allowed.

### Q: How do I know which version to use?
**A**: Use the latest stable version (currently v1) unless you need specific features from newer versions.

## References

- API Documentation: `/docs`
- Scribe Configuration: `config/scribe.php`
- Route Definitions: `routes/api.php`
- Controller Implementations: `app/Http/Controllers/Api/V{X}/`
- Deprecation Middleware: `app/Http/Middleware/ApiDeprecation.php`

## Changelog

### 2025-11-05
- ✅ Implemented URL-based versioning
- ✅ Created v1 namespace (current stable)
- ✅ Created v2 example with enhanced brand endpoint
- ✅ Added deprecation middleware for legacy routes
- ✅ Updated Scribe configuration for version support
- ✅ Set sunset date: 2026-12-31 for non-versioned endpoints
