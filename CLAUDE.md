# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

HoldYourBeer is a Laravel-based beer tracking application that follows a **Spec-driven development** approach. Users can register, track beers they've consumed, increment/decrement consumption counts, and manage tasting notes.

## Technology Stack

> **📖 For detailed setup instructions, see [README.md](README.md#technology-stack)**

- **Backend**: Laravel 12 with PHP 8.3
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

## File Organization

- Core Laravel structure with additional spec-driven documentation
- Documentation in `/docs/` including system flow diagrams
- Complete behavioral specifications in `/spec/` directory
- Mobile-responsive Livewire components for web interface