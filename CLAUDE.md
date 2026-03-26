# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PCPB Portal — institutional portal for the Civil Police of Paraíba State (Brazil). Monolithic Laravel 12 app with Vue 3 + Inertia.js frontend, serving public pages, authenticated officer operations, and an admin panel.

**Stack:** PHP 8.4, Laravel 12, Vue 3, Inertia.js 2.0, Tailwind CSS, PostgreSQL 15, MinIO (S3), Apache 2.4, Node.js 22, Vite.

## Common Commands

```bash
# Docker (primary development environment)
docker compose build
docker compose up -d
docker exec pcpb php artisan tinker

# PHP
composer install
vendor/bin/pint                          # Lint/format PHP
php vendor/bin/phpunit                   # Run tests

# Frontend
npm install
npm run dev                              # Vite dev server (port 5173)
npm run build                            # Production build
npm run format                           # Prettier formatting

# Artisan
php artisan migrate
php artisan db:seed --class=AdminCoreSeeder --force

# Custom commands
php artisan noticias:importar-plone --url=... --login=... --password=...
php artisan noticias:exportar-csv
php artisan noticias:diagnosticar-plone --url=... --login=... --password=...
```

## Architecture

### Authentication (Custom Dual-Strategy)
1. **Primary**: External API via `API_LOGIN_URL` with `API_TOKEN` header (matrícula/senha)
2. **Fallback**: Local database if external API fails
3. New users auto-assigned role `servidor`

### Route Organization
- `routes/web.php` — Public + authenticated officer routes
- `routes/admin.php` — Admin panel (`/painel/*`, 120+ routes)
- `routes/auth.php` — Login/register
- `routes/breadcrumbs.php` — Breadcrumb definitions

### Admin Panel
Built on `balajidharma/laravel-admin-core`. All admin routes live under `/painel/` and are protected by admin middleware. Covers: users, roles/permissions, news, videos, banners, seized vehicles, courses, enrollments, accommodation, contact messages, audit logs, and certificate generation.

### File Storage (MinIO)
Two S3 buckets configured via `AWS_*` env vars:
- `AWS_BUCKET_FOTOS` (`funcionais`) — officer photos
- `AWS_BUCKET` (`veiculos`) — seized vehicles

Helpers: `app/Helpers/StorageHelper.php`, `app/Helpers/UploadHelper.php`

### Key Packages
- `spatie/laravel-permission` — roles & permissions + audit trail
- `inertiajs/inertia-laravel` — SPA-style routing without API
- `barryvdh/laravel-dompdf` — PDF generation for operations & certificates
- `intervention/image` — image processing on upload
- `phpoffice/phpspreadsheet` — Excel exports

### Frontend Structure
- `resources/js/Pages/` — Inertia page components (Vue 3)
- `resources/js/Components/` — Shared components
- `resources/js/Layouts/` — App layouts
- State management via Pinia; CKEditor 5 for rich text; Chart.js for dashboards

### App Layers
```
app/Models/          → 20+ Eloquent models
app/Http/Controllers/       → Public controllers
app/Http/Controllers/Admin/ → Admin panel controllers
app/Services/        → AuditLoggerService, DataSanitizerService
app/Helpers/         → StorageHelper, UploadHelper, CertificadoHelper
app/Traits/          → Reusable model/controller behavior
app/Observers/       → Model event listeners
app/Policies/        → Spatie authorization policies
```

## Environment Setup

Copy `.env.example` and configure:
- `APP_KEY`, `APP_URL`, `APP_LOCALE=pt_BR`
- `DB_*` — PostgreSQL credentials
- `AWS_*` — MinIO endpoint + bucket names
- `API_TOKEN`, `API_LOGIN_URL` — external auth API
- `MAIL_*` — SMTP (Mailpit on port 1025 for dev)
- `FORCE_HTTPS`, `SESSION_SECURE_COOKIE`

## Docker Entrypoint Behavior

`docker-entrypoint.sh` auto-runs on container start:
1. Installs missing vendor/node_modules
2. Waits for PostgreSQL readiness
3. Runs migrations (`--force`)
4. Seeds DB only if empty (AdminCoreSeeder + DormitorioSeeder)
5. Clears all caches
6. Sets OPcache (disabled in local, enabled in production)
7. Starts Vite dev server (local) or runs `npm run build` (production)
8. Starts Apache

**Docker services:** `app` (ports 8015, 5173), `db` (5432), `minio` (9000, 9001), `mailpit` (1025, 8025)
