# GEMINI.md

## Project Overview

This project is a web application called "HoldYourBeer" for tracking beer consumption. Users can record the brand and name of beers they've tried, and the application automatically counts the number of times each beer has been tasted.

The project is built on the Laravel framework (version 12) for the backend and uses a Livewire frontend. It utilizes **dual database environment**:
- **Development**: PostgreSQL (version 17) for persistent data storage
- **Testing**: SQLite memory database for fast, isolated tests

The application requires PHP 8.3, uses PHPUnit for testing with PCOV for code coverage, and the development environment is managed using Laradock.

The application follows a spec-driven development approach, with API specifications in OpenAPI format and feature descriptions in Gherkin.

## Building and Running

This project uses Laradock. For all local development settings, placeholder values, and the exact `docker-compose` commands for running tests, migrations, etc., please refer to the single source of truth:

**➡️ `../laradock_setting.md`**

This file contains the command templates and all necessary variables.

> **📝 Note for AI Assistants**: The `laradock_setting.md` file is available locally but excluded from git repository. Claude Code and Gemini CLI can access this file to get the actual path variables and command templates needed for development tasks.

## Development Conventions

*   **Language Preference:** Whenever possible, respond in Traditional Chinese.
*   **Spec-Driven Development:** All features are defined in the `/spec` directory before implementation.
*   **Commit-First Checks:** Before committing any code, all tests must be run and pass.
*   **Feature Status Updates:** The status of features should be updated in the corresponding `.feature` files.
*   **Mobile-First Responsive Design:** The UI is built with a mobile-first approach using Tailwind CSS.


# GEMINI.md

## Project Overview

[Brief description of the project]

## 🧠 Knowledge Management (AI Instructions)

This project uses a **Session-centric** documentation system. As an AI, you MUST follow these rules:

### 1. Before Starting a Task
- **Check Context**: Read `docs/INDEX-product.md` and `docs/INDEX-architecture.md` to understand the current state.
- **Check History**: If modifying an existing feature, find the related session in `docs/sessions/` to understand previous decisions.

### 2. During Development
- **Create Session**: If this is a new feature or significant change, ask the user to run `/session-create` (or create it yourself if permitted).
- **Document Decisions**: Record all technical decisions (Option A vs B) in the session file.
- **Update Checklist**: Maintain the implementation checklist in the session file.

### 3. After Completion
- **Archive**: Remind the user to run `/session-archive`.
- **Update Indexes**: Help the user generate summaries for `docs/INDEX-*.md`.

### 4. Slash Commands (Workflows)
You can use the following slash commands to quickly execute session scripts:
- `/session-create`: Runs `../.agent/scripts/create-session.sh` to start a new session.
- `/session-archive`: Runs `../.agent/scripts/archive-session.sh` to archive the current session.
- `/changelog-update`: Runs `../.agent/scripts/update-changelog.sh` to update the changelog.

### 5. Directory Structure
- `docs/sessions/`: The source of truth for development history.
- `docs/INDEX-*.md`: Quick lookup for decisions, architecture, API, and features.
- `docs/CHANGELOG.md`: High-level version history.

## Development Conventions

*   **Language Preference:** Whenever possible, respond in Traditional Chinese.
*   **Spec-Driven Development:** All features are defined in the `/spec` directory before implementation.
*   **Commit-First Checks:** Before committing any code, all tests must be run and pass.


# GEMINI.md

## Project Overview

[Brief description of the project]

## 🧠 Knowledge Management (AI Instructions)

This project uses a **Session-centric** documentation system. As an AI, you MUST follow these rules:

### 1. Before Starting a Task
- **Check Context**: Read `docs/INDEX-product.md` and `docs/INDEX-architecture.md` to understand the current state.
- **Check History**: If modifying an existing feature, find the related session in `docs/sessions/` to understand previous decisions.

### 2. During Development
- **Create Session**: If this is a new feature or significant change, ask the user to run `/session-create` (or create it yourself if permitted).
- **Document Decisions**: Record all technical decisions (Option A vs B) in the session file.
- **Update Checklist**: Maintain the implementation checklist in the session file.

### 3. After Completion
- **Archive**: Remind the user to run `/session-archive`.
- **Update Indexes**: Help the user generate summaries for `docs/INDEX-*.md`.

### 4. Slash Commands (Workflows)
You can use the following slash commands to quickly execute session scripts:
- `/session-create`: Runs `../.agent/scripts/create-session.sh` to start a new session.
- `/session-archive`: Runs `../.agent/scripts/archive-session.sh` to archive the current session.
- `/changelog-update`: Runs `../.agent/scripts/update-changelog.sh` to update the changelog.

### 5. Directory Structure
- `docs/sessions/`: The source of truth for development history.
- `docs/INDEX-*.md`: Quick lookup for decisions, architecture, API, and features.
- `docs/CHANGELOG.md`: High-level version history.

## Development Conventions

*   **Language Preference:** Whenever possible, respond in Traditional Chinese.
*   **Spec-Driven Development:** All features are defined in the `/spec` directory before implementation.
*   **Commit-First Checks:** Before committing any code, all tests must be run and pass.
