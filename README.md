# HoldYouBeer

A simple application to track every beer you drink. Record the brand, the specific series/name, and automatically count how many times you've tasted each one.

This project is developed using a Spec-driven development approach.

---

## Technology Stack

- **Backend Framework**: Laravel 12
- **Web Frontend**: Livewire
- **Database**: PostgreSQL 17
- **PHP Version**: 8.3
- **Development Environment**: Laradock

---

## Design Principles

- **Mobile-First Responsive Design**: The web interface is built with a mobile-first approach. All features must be fully functional and aesthetically pleasing on mobile, tablet, and desktop screen sizes. This is achieved using the Tailwind CSS framework.

---

## Local Development Setup

This project uses Laradock for a consistent development environment. Follow these steps to get started.

### 1. Clone the Repository

```bash
git clone <your-repository-url>
cd HoldYourBeer
```

### 2. Setup Laradock

We will add Laradock as a git submodule.

```bash
git submodule add https://github.com/Laradock/laradock.git
```

### 3. Configure Laradock Environment

Navigate into the `laradock` directory and create your environment file.

```bash
cd laradock
cp env-example .env
```

Now, **edit the `.env` file** with your preferred editor and set the following versions:

```env
# Set the PHP version
PHP_VERSION=8.3

# Set the database to PostgreSQL
DB_CONNECTION=pgsql

# Set the PostgreSQL version
POSTGRES_VERSION=17
```

### 4. Start Docker Containers

From within the `laradock` directory, run the following command to build and start the necessary containers.

```bash
docker-compose up -d nginx postgres
```

### 5. Setup Laravel Application

Enter the `workspace` container to run Laravel commands.

```bash
docker-compose exec workspace bash
```

Once inside the container, run these commands from the root directory (`/var/www`):

```bash
# Install PHP dependencies
composer install

# Create Laravel environment file
cp .env.example .env

# Generate an application key
php artisan key:generate

# IMPORTANT: Configure your .env for the database
# Open a new terminal and run `docker-compose exec workspace nano .env` or edit the file directly.
# Make sure the DB_HOST points to the name of your database container.
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=default
DB_USERNAME=default
DB_PASSWORD=default

# Run database migrations
php artisan migrate
```

### 6. Access The Application

You should now be able to access the application in your browser at [http://localhost](http://localhost).

---

## Application Specification

This project follows a Spec-driven development methodology. All specifications for behavior and APIs are located in the `/spec` directory.

- **API Contract**: The OpenAPI specification can be found at `spec/api/api.yaml`.
- **Feature Descriptions**: Human-readable feature descriptions (Gherkin) are located in `spec/features/`.
