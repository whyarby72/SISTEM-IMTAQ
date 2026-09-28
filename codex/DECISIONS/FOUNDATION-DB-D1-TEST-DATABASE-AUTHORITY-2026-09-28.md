# FOUNDATION-DB-D1 — Test Database Authority Decision

**Status:** `COMPLETED / DESIGN_ONLY / OPTION_A_POSTGRES_EPHEMERAL_RECOMMENDED`  
**Date:** `2026-09-28`  
**Repository:** `whyarby72/SISTEM-IMTAQ`  
**Branch:** `chore/foundation-db-d1-test-database-authority`  
**Baseline:** `2e9f26094a88efaed6c8ab3a1f934c47bb647d74`  
**Exact upstream baseline:** `2e9f26094a88efaed6c8ab3a1f934c47bb647d74`

## Decision

`OPTION_A_POSTGRES_EPHEMERAL_RECOMMENDED`

The full foundation/integration suite should use an isolated, disposable
PostgreSQL database. PostgreSQL is the repository's declared technical
database baseline and the existing migration history contains PostgreSQL
DDL that SQLite cannot execute without a compatibility layer. This is a
test-authority decision only. It does not authorize implementation.

`IMPLEMENTATION AUTHORIZATION = NOT_AUTHORIZED`

The next atomic task is:

`FOUNDATION-DB-R1-POSTGRES-EPHEMERAL-CI-DESIGN`

No migration, test harness, workflow, application source, dependency, or
database was changed by D1.

## Root cause and exact evidence

GitHub Actions run `36355381623`, on repair commit
`659f6b3947e85c2f50cbc6dcfdc506aaf4b8705b`, passed PHP setup, the PHP
runtime guard, Composer validation/install, and repository routing. It
failed when PHPUnit started:

- `489 failed, 3 passed, 17 warnings, 53 assertions`;
- SQLite rejected `ALTER TABLE students ALTER COLUMN student_code DROP NOT NULL`;
- failing migration:
  `application/web/database/migrations/2026_09_05_000003_make_student_code_optional_and_add_identifier_columns.php:16`.

The full test harness has two independent SQLite forcing points:

1. `application/web/phpunit.xml` forces `DB_CONNECTION=sqlite` and
   `DB_DATABASE=:memory:`.
2. `application/web/tests/TestCase.php` sets the default connection to
   SQLite and the SQLite database to `:memory:` during application creation.

This is a test-environment mismatch, not evidence that the production
database should be changed to SQLite.

## Authoritative sources

| Source | Authority established |
| --- | --- |
| `docs/02_architecture/POSTGRESQL_SCHEMA.md` | PostgreSQL is the technical schema baseline; PostgreSQL extensions, UUIDs, constraints, timestamps, and database-enforced integrity are part of the architecture. |
| Applied migration history | The application schema includes PostgreSQL-specific DDL and must be replayable from zero on the authoritative engine. |
| `application/web/config/database.php` | A first-class `pgsql` connection already exists; SQLite is only one available driver, not an architecture decision. |
| `application/web/phpunit.xml` and `application/web/tests/TestCase.php` | Current full-suite test authority is explicitly forced to SQLite. This is the source of the observed incompatibility. |
| `.github/workflows/application-foundation.yml` | Current CI installs only `pdo_sqlite` and does not provision PostgreSQL. |
| `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md` | Applied migrations are immutable; changes require explicit scope, validation, recovery, and staging discipline. |
| `modules/CHANGE_IMPACT_RULES.md` | This is at least a `DATABASE_GLOBAL` change and requires migration/rollback/restore and impacted-regression consideration. |

## Change classification

Minimum classification:

- `DATABASE_GLOBAL` — test database engine authority, migration replay, and
  database backup/restore safety are affected.
- `CI_INFRASTRUCTURE` — the workflow must provision and health-check an
  isolated PostgreSQL service.
- `ENVIRONMENT_CONFIGURATION` — test-only connection settings and identity
  guards must be explicit.
- `MIGRATION_COMPATIBILITY_CONTRACT` — the full migration history must be
  executed on the engine for which it was designed.

`SHARED_CORE` is affected by regression coverage because many Shared Core
tests use `RefreshDatabase`, but no Shared Core business source is proposed
for change. No Academic business semantics are changed.

## Option A — ephemeral PostgreSQL (recommended)

### Fit with current architecture

This option runs the full foundation/integration suite against a PostgreSQL
service created for the CI job. The database must be disposable per run and
must be created from the current migration history, not restored from pilot
or production data.

### Required design controls

- CI service container or equivalent isolated PostgreSQL service;
- CI-only database name, user, and password, with no pilot/staging/
  production host or credential fallback;
- a fresh database per job/run, with an identity guard checking both the
  resolved driver and database identity before any migration/write;
- migration-from-zero before the foundation suite;
- PostgreSQL extension availability required by the migrations, including
  `btree_gist` where the migration history requires it;
- no real academic data, secrets, or persistent business database access;
- failure-safe cleanup of the disposable service/database;
- local documentation for a separately named disposable developer database,
  never the `imtaq` pilot database;
- a CI assertion that the resolved connection is PostgreSQL and the database
  identity is the expected test identity.

### Expected implementation scope (future task, not D1)

The minimum proposed write scope is:

1. `.github/workflows/application-foundation.yml` — provision PostgreSQL,
   expose only CI test connection variables, wait for readiness, run
   migration-from-zero, and invoke the foundation suite.
2. `application/web/phpunit.xml` — remove the unconditional SQLite full-suite
   authority and retain only safe test defaults that allow CI/local disposable
   PostgreSQL values to be supplied.
3. `application/web/tests/TestCase.php` — stop overriding the connection to
   SQLite; retain testing-only app/session/cache/queue hardening and add the
   approved test-database identity guard if needed.
4. `application/web/tests/Support/` — only if a reusable database identity
   guard or PostgreSQL test bootstrap is needed; it must reject pilot,
   staging, and production identities.
5. `application/web/config/database.php` — change only if a dedicated,
   explicit test connection is required. The existing `pgsql` connection
   should be reused where possible; no production connection semantics
   should be weakened.
6. `.github/workflows` or a narrowly scoped test helper — only for the
   migration-from-zero/health-check orchestration if it cannot remain in the
   existing workflow.
7. A new Change Manifest and targeted test/operations documentation.

Not in scope: migration edits, new business migrations, Composer/PHP
changes, production configuration, pilot data, provider/OpenAI code, or
Academic business logic.

### Regression and security implications

The implementation gate must cover migration-from-zero, schema/constraint
inspection, Shared Core and Academic `RefreshDatabase` tests, the existing
AI database-target guard tests, and the full required CI suite. The run must
prove that no pilot/staging/production connection is reachable and must not
print database passwords or connection URLs containing secrets.

### Rollback

Rollback is a code/workflow revert to the prior test contract plus disposal
of the ephemeral PostgreSQL service. No production or pilot rollback is
needed because no persistent non-test database is touched. Any disposable
test database is destroyed rather than restored over another environment.

## Option B — SQLite portability layer

`OPTION_B_SQLITE_PORTABILITY_RECOMMENDED` is rejected for the full suite.

The targeted migration scan found these PostgreSQL-specific or
PostgreSQL-sensitive constructs in the applied migration history:

- `ALTER TABLE ... ALTER COLUMN ... DROP/SET NOT NULL`;
- `CREATE EXTENSION IF NOT EXISTS btree_gist`;
- `EXCLUDE USING gist` constraints for temporal/non-overlap integrity;
- raw `ALTER TABLE ... ADD/DROP CONSTRAINT` statements;
- PostgreSQL `jsonb` columns;
- database-enforced `CHECK` constraints expressed through raw SQL;
- multiple temporal/constraint definitions whose intended enforcement is
  documented as PostgreSQL schema behavior.

The failing `student_code` migration is already applied in the accepted
history and is immutable. It cannot be edited to add a SQLite branch as
part of this task. A portability layer would therefore require a separate,
carefully designed compatibility strategy for the historical schema, future
migrations, schema assertions, and constraint behavior. Skipping or
weakening PostgreSQL-only constraints in SQLite would make the test suite
accept behavior that production does not provide. Reimplementing equivalent
behavior in application code would also increase divergence and would not
prove database enforcement.

SQLite could remain useful for explicitly isolated unit tests that do not
exercise the full migration/schema contract, but that is not equivalent to
the current full foundation/integration suite and must be named and gated as
such. It is not a safe replacement for the authoritative full suite.

## Option C — HOLD

`HOLD_AUTHORITY_OR_SCOPE_REQUIRED` is not selected. Authority is sufficient:
the schema contract names PostgreSQL, the existing connection configuration
supports PostgreSQL, and the exact CI failure demonstrates the incompatibility
with the current SQLite forcing. A later implementation still requires a
separate owner authorization and the exact R1 design review before writes.

## Protected files and zones

Protected during D1 and the future implementation unless separately
authorized:

- all applied files under `application/web/database/migrations/`;
- pilot/staging/production database connections and persistent business data;
- Academic attendance/session semantics and Shared Core identity semantics;
- Composer lock/package versions and PHP runtime contract;
- provider/OpenAI runtime and credentials;
- deployment and production secret configuration.

The D1 artifact itself is the only repository file created by this task.

## Local and CI workflow implications

Local full-suite execution should require an explicitly named disposable
PostgreSQL database, for example a developer-owned `imtaq_test_<suffix>`
database or an isolated container. It must never infer or reuse `imtaq`.

CI should use a job-local PostgreSQL service and an ephemeral database
identity, run migrations from zero, execute the suite, publish only safe
failure metadata, and tear the service down at job completion. The workflow
must fail closed if the driver/database identity is not the expected
disposable PostgreSQL target.

## Acceptance mapping

| ID | D1 result |
| --- | --- |
| FDB-D1-AC-01 | PASS — exact baseline, run, commit, and failure recorded. |
| FDB-D1-AC-02 | PASS — both SQLite forcing points recorded. |
| FDB-D1-AC-03 | PASS — PostgreSQL architecture authority recorded. |
| FDB-D1-AC-04 | PASS — applied migration immutability preserved. |
| FDB-D1-AC-05 | PASS — Options A/B/C compared. |
| FDB-D1-AC-06 | PASS — isolation, identity, credential, and secret controls explicit. |
| FDB-D1-AC-07 | PASS — future implementation file scope explicit. |
| FDB-D1-AC-08 | PASS — no implementation or database mutation performed. |
| FDB-D1-AC-09 | PASS — next task is `FOUNDATION-DB-R1-POSTGRES-EPHEMERAL-CI-DESIGN`. |
| FDB-D1-AC-10 | PASS — commit `415d929a98eb837689934ab884fe4ba68232e366` is pushed and local/upstream SHAs match. |

## Closeout

`FOUNDATION-DB-D1 = COMPLETED / DESIGN_ONLY / OPTION_A_POSTGRES_EPHEMERAL_RECOMMENDED`

`DATABASE WRITE = NONE`  
`MIGRATION CHANGE = NONE`  
`APPLICATION SOURCE CHANGE = NONE`  
`IMPLEMENTATION AUTHORIZATION = NOT_AUTHORIZED`  
`NEXT_ATOMIC_TASK = FOUNDATION-DB-R1-POSTGRES-EPHEMERAL-CI-DESIGN`
