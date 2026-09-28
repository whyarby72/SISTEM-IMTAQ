# CURRENT TASK CONTEXT

**Task:** `FOUNDATION-DB-D1-TEST-DATABASE-AUTHORITY`  
**State:** `READY_FOR_EXECUTION`  
**Current phase:** `CI / TEST DATABASE AUTHORITY`  
**Branch:** `chore/foundation-db-d1-test-database-authority`  
**State-basis before D1:** `feb9e1be7e8dc0240752f55e613af484233e8d25`

## Verified trigger

Exact GitHub Actions run:
`36355381623`

Repair commit:
`659f6b3947e85c2f50cbc6dcfdc506aaf4b8705b`

Verified sequence:
- PHP setup: PASS
- PHP 8.4.x guard: PASS
- Composer validate/install: PASS
- repository routing: PASS
- PHPUnit/foundation: FAIL

Failure root:
SQLite rejects PostgreSQL-style
`ALTER TABLE students ALTER COLUMN student_code DROP NOT NULL`.

## Architectural tension

- repository architecture baseline: PostgreSQL;
- full test harness currently forces SQLite `:memory:`;
- failing migration contains PostgreSQL-specific SQL;
- applied migration history is immutable.

## Required now

1. `codex/TASK_CONTEXTS/FOUNDATION-DB-D1-TEST-DATABASE-AUTHORITY.md`
2. `application/web/phpunit.xml`
3. `application/web/tests/TestCase.php`
4. `application/web/config/database.php`
5. `application/web/database/migrations/2026_09_05_000003_make_student_code_optional_and_add_identifier_columns.php`
6. `docs/02_architecture/POSTGRESQL_SCHEMA.md`
7. `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md`
8. `modules/CHANGE_IMPACT_RULES.md`
9. `.github/workflows/application-foundation.yml`
10. `PROJECT_STATE.json`
11. `NEXT_ACTION.md`

## Output

Create:
`codex/DECISIONS/FOUNDATION-DB-D1-TEST-DATABASE-AUTHORITY-2026-09-28.md`

Decision must be one of:
- `OPTION_A_POSTGRES_EPHEMERAL_RECOMMENDED`
- `OPTION_B_SQLITE_PORTABILITY_RECOMMENDED`
- `HOLD_AUTHORITY_OR_SCOPE_REQUIRED`

## Boundary

Diagnosis/design only.

Do not edit migrations, test harness, workflow, DB config, business source, PHP/Composer, dependencies, provider state, or any database.

## Exit

Commit/push decision artifact and state/evidence only.
STOP for ChatGPT audit.
