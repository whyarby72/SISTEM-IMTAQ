# SISTEM IMTAQ application foundation

This is the Laravel foundation for the SISTEM IMTAQ modular monolith.

## Local setup

Requirements:

- PHP 8.3 or newer
- Composer 2.x
- PostgreSQL (connection values are placeholders in `.env.example`)

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan test
```

The local `.env` is intentionally ignored. Replace its PostgreSQL placeholder values locally; never commit credentials or other secrets.

## Boundary

This foundation is one Laravel project with one PostgreSQL target. It contains no business-domain tables or provider integrations. Future Shared Core and domain code must preserve explicit ownership boundaries.

## Dependency baseline

Exact resolved versions are recorded in `composer.lock`. The scaffold was generated from Laravel `v13.10.1` and resolved Laravel Framework `v13.30.1`.
