---
description: Repository Information Overview
alwaysApply: true
---

# Laravel Vue Admin Panel Information

## Summary
A comprehensive admin panel boilerplate built with **Laravel 12**, **Vue 3**, and **Inertia.js**. It features a single-page application (SPA) architecture, utilizing **Tailwind CSS** for styling and **Pinia** for state management. The project includes built-in support for permissions, menus, and a modern dashboard interface.

## Structure
- **app/**: Core PHP application logic including Models, Controllers, and Services.
- **bootstrap/**: Framework bootstrapping and cache configuration.
- **config/**: Application configuration files (auth, database, services, etc.).
- **database/**: Database migrations, seeders, and factories.
- **public/**: Web server entry point (`index.php`) and compiled assets.
- **resources/**: Frontend source code (Vue components, CSS, and Blade templates).
- **routes/**: Route definitions for web, API, and admin sections.
- **storage/**: Compiled templates, sessions, cache, and application logs.
- **tests/**: Automated tests (Feature and Unit).

## Language & Runtime
**Language**: PHP, JavaScript  
**Version**: PHP ^8.4, Node.js v22  
**Build System**: Vite  
**Package Manager**: Composer, npm

## Dependencies
**Main Dependencies**:
- `laravel/framework`: ^12.0
- `inertiajs/inertia-laravel`: ^2.0
- `@inertiajs/vue3`: ^2.2.11
- `vue`: ^3.5.22
- `tailwindcss`: ^3.2.1
- `pinia`: ^2.0.17
- `balajidharma/laravel-admin-core`: ^3.0
- `tightenco/ziggy`: ^2.4

**Development Dependencies**:
- `phpunit/phpunit`: ^11.5.3
- `laravel/breeze`: ^2.0
- `laravel/sail`: ^1.41
- `fakerphp/faker`: ^1.23
- `prettier`: ^3.6.2

## Build & Installation
```bash
# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Build assets for production
npm run build

# Generate app key
php artisan key:generate

# Run migrations and seeders
php artisan migrate --seed --seeder=AdminCoreSeeder

# Create storage link
php artisan storage:link
```

## Docker
**Dockerfile**: `Dockerfile`  
**Image**: `php:8.4-apache` base image  
**Configuration**: 
- Multi-stage setup installing PHP extensions (GD, PDO, Zip, Opcache) and Node.js 22.
- **Docker Compose** (`docker-compose.yml`) defines:
  - `app`: Laravel application on port 8015.
  - `db`: PostgreSQL 15 database.
  - `minio`: S3-compatible object storage.
  - `mailpit`: Email testing interface.

## Testing
**Framework**: PHPUnit  
**Test Location**: `tests/`  
**Naming Convention**: `*Test.php`  
**Configuration**: `phpunit.xml`  

**Run Command**:
```bash
php artisan test
```
