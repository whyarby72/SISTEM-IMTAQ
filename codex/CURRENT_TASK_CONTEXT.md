# CURRENT TASK CONTEXT

**Task:** `FOUNDATION-DB-R2-POSTGRES-EPHEMERAL-CI-IMPLEMENTATION`  
**State:** `COMPLETED / PASS_WITH_NEW_BLOCKER`
**Current phase:** `CI / POSTGRESQL EPHEMERAL TEST IMPLEMENTATION`  
**Branch:** `chore/foundation-db-r2-postgres-ephemeral-ci`  
**State-basis:** `9332bd21cb1cc28942ae8c10c28cd2b80600fd30`

## Authority

FOUNDATION-DB-D1:
`CLOSED / ACCEPTED / OPTION_A_POSTGRES_EPHEMERAL_RECOMMENDED`

FOUNDATION-DB-R1:
`CLOSED / ACCEPTED / DESIGN_ONLY / PASS`

Plan:
`codex/PLANS/FOUNDATION-DB-R1-POSTGRES-EPHEMERAL-CI-DESIGN-2026-09-28.md`

## Required now

1. `codex/TASK_CONTEXTS/FOUNDATION-DB-R2-POSTGRES-EPHEMERAL-CI-IMPLEMENTATION.md`
2. `codex/PLANS/FOUNDATION-DB-R1-POSTGRES-EPHEMERAL-CI-DESIGN-2026-09-28.md`
3. `.github/workflows/application-foundation.yml`
4. `application/web/phpunit.xml`
5. `application/web/tests/TestCase.php`
6. `application/web/composer.json`
7. `application/web/scripts/verify-foundation.sh`
8. targeted applied migration/extension evidence only
9. `PROJECT_STATE.json`
10. `NEXT_ACTION.md`

## Authorized implementation

- PostgreSQL 18.6 disposable GitHub Actions service
- `pdo_pgsql`
- PostgreSQL test environment wiring
- fail-closed test database identity guard
- remove SQLite full-suite forcing
- migration-from-zero on disposable PostgreSQL only
- targeted guard regression
- full required regression and exact GitHub Actions verification
- closeout evidence/state

## Critical safety

Applied migrations are immutable.

Never touch pilot/staging/production database or credentials.

Guard naming semantics:
- CI: exactly `imtaq_ci_test`
- local: `^imtaq_test_[a-z0-9][a-z0-9_-]*$`
- exact `imtaq` and names containing pilot/staging/production/prod/live are rejected.

`application/web/config/database.php` is not authorized. If it becomes required:
STOP = `R2_HOLD_DATABASE_CONFIG_SCOPE_EXPANSION_REQUIRED`.

## Exit

Push implementation/evidence and verify exact-current GitHub Actions.

If CI green:
- close `FOUNDATION_SQLITE_MIGRATION_COMPATIBILITY`
- next product track = `ACADEMIC_WEB_COMPLETION_REVIEW`

STOP for ChatGPT audit.
