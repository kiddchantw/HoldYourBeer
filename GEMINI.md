# GEMINI.md

## Project Overview

This project is a web application called "HoldYourBeer" for tracking beer consumption. Users can record the brand and name of beers they've tried, and the application automatically counts the number of times each beer has been tasted.

The project is built on the Laravel framework (version 12) for the backend and uses a Livewire frontend. It utilizes PostgreSQL (version 17) as its database and requires PHP 8.3. The development environment is managed using Laradock.

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

**4. Run Tests:**

All `php artisan` or test commands **must** be run inside the Laradock `workspace` container.

Use the following command template from the `HoldYourBeer` project root:

```bash
# Command Template
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace <YOUR_COMMAND_HERE>
```

### Examples:

```bash
# Run all automated tests
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace php artisan test

# Run tests in a specific file
docker-compose -f ../../laradock/docker-compose.yml exec -w /var/www/side/HoldYourBeer workspace php artisan test --filter=BeerCreationTest
```

## Development Conventions

*   **Spec-Driven Development:** All features are defined in the `/spec` directory before implementation.
*   **Commit-First Checks:** Before committing any code, all tests must be run and pass.
*   **Feature Status Updates:** The status of features should be updated in the corresponding `.feature` files.
*   **Mobile-First Responsive Design:** The UI is built with a mobile-first approach using Tailwind CSS.
