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
