# FOUNDATION-DB-D1 — Test Database Authority

**Mode:** EXISTING_REPO_ADOPTION  
**Task type:** DATABASE_GLOBAL_DIAGNOSIS_AND_DESIGN  
**Owner:** CODEX  
**State:** READY_FOR_EXECUTION  
**Branch:** chore/foundation-db-d1-test-database-authority  
**State-basis before D1:** feb9e1be7e8dc0240752f55e613af484233e8d25

## Trigger

Exact GitHub Actions run `36355381623` on repair commit
`659f6b3947e85c2f50cbc6dcfdc506aaf4b8705b` verified:

- PHP 8.4 setup: PASS
- approved runtime guard: PASS
- Composer validate/install: PASS
- repository routing check: PASS
- PHPUnit/foundation: FAIL

Observed failure:
`SQLSTATE[HY000]: General error: 1 near "ALTER": syntax error`

Failing migration statement:
`ALTER TABLE students ALTER COLUMN student_code DROP NOT NULL`

File:
`application/web/database/migrations/2026_09_05_000003_make_student_code_optional_and_add_identifier_columns.php:16`

Current full test harness explicitly forces:
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=:memory:`
- `tests/TestCase.php` also sets the default database to SQLite in-memory.

Repository architecture separately defines PostgreSQL as the technical baseline.

Applied migrations are immutable under the safe-maintenance contract.

## Goal

Determine the authoritative database engine for full foundation/integration tests and define the minimum safe remediation design.

D1 is diagnosis/design only. It must not implement any database/test-harness migration.

## Change classification

At minimum:
- `DATABASE_GLOBAL`

Also identify whether the proposed solution touches:
- `SHARED_CORE`
- CI infrastructure
- environment configuration
- migration compatibility contracts.

Do not silently broaden scope.

## Required evidence

Inspect targeted sources only:

1. exact R2R Actions evidence/run `36355381623`;
2. `application/web/phpunit.xml`;
3. `application/web/tests/TestCase.php`;
4. failing migration;
5. database configuration;
6. `docs/02_architecture/POSTGRESQL_SCHEMA.md`;
7. `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md`;
8. `modules/CHANGE_IMPACT_RULES.md`;
9. CI workflow;
10. any existing test strategy or CI database guidance located by targeted search.

## Mandatory analysis

### A. PostgreSQL ephemeral CI

Assess a design where full foundation/integration CI uses an isolated ephemeral PostgreSQL service.

Must address:
- no connection to pilot/staging/production DB;
- CI-only credentials;
- per-run disposable database;
- migration-from-zero behavior;
- compatibility with current PostgreSQL-specific migrations;
- required workflow/env/test harness changes;
- local-development test strategy;
- regression and security implications;
- rollback.

### B. SQLite portability layer

Assess keeping SQLite as an explicitly supported full-suite test engine.

Must identify:
- all known PostgreSQL-specific migration/schema constructs that would need compatibility handling;
- whether old applied migrations could remain immutable;
- whether compatibility can be achieved without falsifying production behavior;
- ongoing maintenance cost and divergence risk;
- regression scope.

Do not edit old migrations as part of this task.

### C. HOLD

Use HOLD if repository authority is insufficient to choose a test-database contract safely.

## Decision rules

Return one of:

- `OPTION_A_POSTGRES_EPHEMERAL_RECOMMENDED`
- `OPTION_B_SQLITE_PORTABILITY_RECOMMENDED`
- `HOLD_AUTHORITY_OR_SCOPE_REQUIRED`

The recommendation must be grounded in repository architecture and maintenance rules, not convenience.

Do not infer that the user already approved implementation.

## Required artifact

Create:

`codex/DECISIONS/FOUNDATION-DB-D1-TEST-DATABASE-AUTHORITY-2026-09-28.md`

It must contain:

- root cause;
- authoritative sources;
- change class;
- A/B/C comparison;
- recommended option;
- exact proposed implementation file scope;
- protected files/zones;
- migration immutability conclusion;
- test isolation/security requirements;
- local developer workflow implication;
- CI workflow implication;
- regression plan;
- rollback plan;
- implementation task ID if recommendation is actionable;
- explicit implementation authorization state = NOT_AUTHORIZED.

## Forbidden

Do not:

- edit any migration;
- create a new migration;
- change `phpunit.xml`;
- change `tests/TestCase.php`;
- change `config/database.php`;
- change GitHub Actions workflow;
- create/alter any real database;
- run migrations against pilot/staging/production;
- change application/business source;
- change Composer/dependencies/PHP;
- change provider/OpenAI;
- deploy;
- merge to main.

Read-only local experiments are allowed only if they cannot touch a non-test database and do not mutate repository state.

## Acceptance criteria

- FDB-D1-AC-01 exact baseline and failing Actions evidence recorded.
- FDB-D1-AC-02 SQLite forcing points identified.
- FDB-D1-AC-03 PostgreSQL architecture authority identified.
- FDB-D1-AC-04 applied migration immutability enforced.
- FDB-D1-AC-05 A/B/C comparison complete.
- FDB-D1-AC-06 database/test isolation requirements explicit.
- FDB-D1-AC-07 exact proposed implementation write scope explicit.
- FDB-D1-AC-08 no implementation or DB mutation performed.
- FDB-D1-AC-09 one next implementation/design task identified or HOLD.
- FDB-D1-AC-10 commit/push clean and STOP.

## Expected next task if Option A is selected after audit

`FOUNDATION-DB-R1-POSTGRES-EPHEMERAL-CI-DESIGN`

or, if D1 itself provides sufficient exact design detail and ChatGPT/owner later authorizes implementation:

`FOUNDATION-DB-R2-POSTGRES-EPHEMERAL-CI-IMPLEMENTATION`

Do not execute either inside D1.

## Closeout

Return:

`FOUNDATION-DB-D1 = COMPLETED / DESIGN_ONLY / <DECISION>`

Then STOP for ChatGPT audit.
