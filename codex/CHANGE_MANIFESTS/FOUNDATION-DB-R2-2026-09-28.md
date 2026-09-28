# FOUNDATION-DB-R2 — PostgreSQL Ephemeral CI Implementation

**Status:** `IMPLEMENTED / PENDING_EXACT_CI_VERIFICATION`  
**Date:** `2026-09-28`  
**Branch:** `chore/foundation-db-r2-postgres-ephemeral-ci`  
**Starting HEAD:** `3e0fdaabedc21f6c7a1fd11ac5932cf676f93c32`

## Scope

Implement the approved PostgreSQL 18.6 disposable foundation CI lane,
remove SQLite as the full-suite authority, add a fail-closed test database
identity guard, replay migrations from zero only on the disposable service,
and add targeted guard coverage.

Change classes:

- `DATABASE_GLOBAL`
- `CI_INFRASTRUCTURE`
- `ENVIRONMENT_CONFIGURATION`
- `MIGRATION_COMPATIBILITY_CONTRACT`

## Files changed

- `.github/workflows/application-foundation.yml`
- `application/web/phpunit.xml`
- `application/web/tests/TestCase.php`
- `application/web/tests/Support/TestDatabaseIdentityGuard.php`
- `application/web/tests/Feature/Foundation/TestDatabaseIdentityGuardTest.php`
- this manifest and evidence/state files as closeout progresses.

`application/web/config/database.php` was not changed.

## Safety controls

- PostgreSQL image is pinned to `postgres:18.6`.
- CI service database is exactly `imtaq_ci_test` with user `imtaq_ci`.
- CI-only disposable password is scoped to the job service and is not a
  production/pilot/staging credential.
- `pdo_pgsql` is the foundation-lane PHP driver.
- The guard rejects non-testing environments, non-PostgreSQL drivers,
  redirecting `DB_URL`, remote hosts, pilot/staging/production/prod/live
  database markers, and unknown database names.
- The guard verifies Laravel-resolved driver/database identity and CI
  PostgreSQL `server_version_num=180006` before migration/test writes.
- `migrate:fresh` is run only after the guard and only against the job-local
  service database.
- Applied migrations were not edited and no migration was created.
- No pilot/staging/production database or credential was accessed.

## Verification performed locally

- `php -l` guard: PASS
- `php -l` guard tests: PASS
- `phpunit.xml` XML parse: PASS
- `git diff --check`: PASS
- targeted guard suite: PASS — 12 tests, 12 assertions
- local PostgreSQL migration/full-suite run: NOT AVAILABLE; Docker daemon was
  unavailable and no local PostgreSQL service was listening on `127.0.0.1:5432`.

The exact GitHub Actions result on the implementation commit is required
before this manifest can be marked `COMPLETED / PASS`.

## Protected zones unchanged

- `application/web/database/migrations/**`
- `application/web/config/database.php`
- pilot/staging/production database and credentials
- Academic, Shared Core, provider/OpenAI, Composer, PHP, and deployment
  contracts

## Rollback

Revert the implementation commit and allow the ephemeral CI service to be
destroyed with its job. No persistent database rollback is required or
authorized.

## Authorization

`R2_IMPLEMENTATION_AUTHORIZED = YES`  
`DATABASE_WRITE_OUTSIDE_EPHEMERAL_CI = NOT_AUTHORIZED`  
`MIGRATION_EDIT = NOT_AUTHORIZED`
