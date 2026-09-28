# CURRENT TASK CONTEXT

**Task:** `FOUNDATION-DB-R1-POSTGRES-EPHEMERAL-CI-DESIGN`  
**State:** `READY_FOR_EXECUTION`  
**Current phase:** `CI / POSTGRESQL EPHEMERAL TEST DESIGN`  
**Branch:** `chore/foundation-db-r1-postgres-ephemeral-ci-design`  
**State-basis:** `bcdd1280d9976020250659baa9a064e278f2e70b`

## Authority

FOUNDATION-DB-D1:
`CLOSED / ACCEPTED / OPTION_A_POSTGRES_EPHEMERAL_RECOMMENDED`

Decision artifact:
`codex/DECISIONS/FOUNDATION-DB-D1-TEST-DATABASE-AUTHORITY-2026-09-28.md`

## Problem

The full suite currently forces SQLite although PostgreSQL is the technical baseline and applied migrations contain PostgreSQL-specific DDL.

Exact failing CI evidence:
- run `36355381623`
- PHP/Composer/routing PASS
- PHPUnit/foundation FAIL on SQLite migration compatibility

## Required now

1. `codex/TASK_CONTEXTS/FOUNDATION-DB-R1-POSTGRES-EPHEMERAL-CI-DESIGN.md`
2. `codex/DECISIONS/FOUNDATION-DB-D1-TEST-DATABASE-AUTHORITY-2026-09-28.md`
3. `.github/workflows/application-foundation.yml`
4. `application/web/phpunit.xml`
5. `application/web/tests/TestCase.php`
6. `application/web/config/database.php`
7. targeted migration/extension evidence only
8. `docs/02_architecture/POSTGRESQL_SCHEMA.md`
9. `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md`
10. `modules/CHANGE_IMPACT_RULES.md`
11. `PROJECT_STATE.json`
12. `NEXT_ACTION.md`

## Output

Create:
`codex/PLANS/FOUNDATION-DB-R1-POSTGRES-EPHEMERAL-CI-DESIGN-2026-09-28.md`

Design must lock:
- PostgreSQL major version;
- CI service and health check;
- disposable DB identity/credentials;
- fail-closed database guard;
- phpunit.xml contract;
- TestCase.php contract;
- extension requirements;
- migration-from-zero strategy;
- local developer workflow;
- exact R2 file scope;
- regression and rollback.

## Boundary

DESIGN ONLY.

Do not edit migrations, workflow implementation, test harness implementation, DB config, application source, PHP/Composer/dependencies, provider state, deployment, or any database.

## Exit

After PASS:
- next implementation task = `FOUNDATION-DB-R2-POSTGRES-EPHEMERAL-CI-IMPLEMENTATION`
- implementation remains NOT_AUTHORIZED
- commit/push design + state/evidence
- STOP for ChatGPT audit.
