# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

HoldYourBeer is a Laravel-based beer tracking application that follows a **Spec-driven development** approach. Users can register, track beers they've consumed, increment/decrement consumption counts, and manage tasting notes.

## Technology Stack

> **📖 For detailed setup instructions, see [README.md](README.md#technology-stack)**

- **Backend**: Laravel 12 with PHP 8.3
- **Mobile App**: Flutter with Firebase Auth
- **Authentication**:
  - Laravel Sanctum (email/password)
  - Firebase Auth (Google Sign-In, Apple Sign-In)
- **Push Notifications**: Firebase Cloud Messaging (FCM)
- **Testing**: PHPUnit with PCOV for code coverage
- **Design**: Mobile-first responsive design using Tailwind CSS

## Development Environment Setup

This project uses Laradock. For all local development settings, placeholder values, and the exact `docker-compose` commands for running tests, migrations, etc., please refer to the single source of truth:

**➡️ `laradock_setting.md`**

This file contains the command templates and all necessary variables.

> **📝 Note for AI Assistants**: The `laradock_setting.md` file is available locally but excluded from git repository. Claude Code and Gemini CLI can access this file to get the actual path variables and command templates needed for development tasks.

## Architecture & Spec-Driven Development

This project follows a comprehensive specification-first approach:

### Core Business Logic
- **Users**: Hybrid authentication system
  - Traditional: Registration and authentication via Laravel Sanctum
  - Social: Firebase Auth with Google/Apple Sign-In
  - Account linking: Same email automatically linked across methods
- **Brands**: Beer brand entities (e.g., "Guinness")
- **Beers**: Specific beer variants (e.g., "Guinness Draught")
- **Tasting Count**: Optimized count tracking per user/beer
- **Tasting Logs**: Audit trail of increment/decrement actions
- **Push Notifications**: FCM token management for notifications

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
- RESTful endpoints with `/api/v1/` prefix (versioned)
- Hybrid authentication:
  - Bearer token via Sanctum (email/password)
  - Bearer token via Firebase ID Token (Google/Apple Sign-In)
- Middleware: `auth:sanctum` for Sanctum, `firebase.auth` for Firebase
- Count modifications use dedicated `count_actions` endpoint with `increment`/`decrement` actions
- All responses follow consistent JSON structure defined in OpenAPI spec

### Firebase Integration
- **Service**: `FirebaseAuthService` - Token verification and user management
- **Middleware**: `FirebaseAuthMiddleware` - Protects API routes
- **Controller**: `FirebaseAuthController` - Handles login, FCM tokens, logout
- **Routes**: `/api/v1/auth/firebase/*` endpoints
- **Documentation**:
  - `/docs/FIREBASE_AUTH_IMPLEMENTATION.md` - Complete technical design
  - `/docs/FIREBASE_SETUP.md` - Firebase Console setup guide
  - `/docs/FLUTTER_INTEGRATION.md` - Flutter integration guide

## Key API Endpoints

> **📖 For complete API endpoint list, see [README.md](README.md#key-api-endpoints)**

## Definition of Done Requirements

> **📖 For complete Definition of Done criteria, see [README.md](README.md#definition-of-done-requirements)**

## Development Guidelines

> **📖 For detailed development guidelines, see [README.md](README.md#development-guidelines)**

Key principles:
- Mobile-First Design with responsive layouts
- Transaction Safety for count modifications
- Performance optimization using dedicated count tables
- Standardized error handling and authentication

### Feature Development Workflow

**Spec-Driven Development Process:**
1. Review existing spec files in `/spec/features/`
2. Update feature status in `.feature` files when starting/completing work
3. Run `php artisan spec:status` to update progress tracking
4. Follow Definition of Done criteria before marking features complete

**Status Tracking Commands:**
```bash
# Update features implementation status
php artisan spec:status

# View current progress without updating files
php artisan spec:status --dry-run --format=table

# Check spec-test consistency
php artisan spec:check

# Sync spec files with test files
php artisan spec:sync
```

**Claude Code Protocol:**
When completing any feature development, Claude Code will automatically:
1. Update the `.feature` file status: `# 1. Status: DONE`
2. Execute `php artisan spec:status` to refresh progress tracking  
3. Include status changes in commit messages
4. Ensure all Definition of Done criteria are met

## File Organization

- Core Laravel structure with additional spec-driven documentation
- Documentation in `/docs/` including:
  - System flow diagrams
  - Firebase integration guides (setup, implementation, Flutter)
- Complete behavioral specifications in `/spec/` directory
- Mobile-responsive Livewire components for web interface
- Firebase integration files:
  - `app/Services/FirebaseAuthService.php` - Firebase authentication service
  - `app/Http/Middleware/FirebaseAuthMiddleware.php` - Route protection
  - `app/Http/Controllers/Api/FirebaseAuthController.php` - API endpoints
  - `config/firebase.php` - Firebase configuration
  - `database/migrations/*_add_firebase_fields_to_users_table.php` - User schema