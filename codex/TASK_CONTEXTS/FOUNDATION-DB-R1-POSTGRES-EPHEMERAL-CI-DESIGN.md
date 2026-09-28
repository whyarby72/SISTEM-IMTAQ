# FOUNDATION-DB-R1 — PostgreSQL Ephemeral CI Design

**Mode:** EXISTING_REPO_ADOPTION  
**Task type:** DATABASE_GLOBAL_IMPLEMENTATION_DESIGN  
**Owner:** CODEX  
**State:** READY_FOR_EXECUTION  
**Branch:** chore/foundation-db-r1-postgres-ephemeral-ci-design  
**State-basis:** bcdd1280d9976020250659baa9a064e278f2e70b

## Authority

FOUNDATION-DB-D1 is CLOSED / ACCEPTED.

Decision artifact:
`codex/DECISIONS/FOUNDATION-DB-D1-TEST-DATABASE-AUTHORITY-2026-09-28.md`

Approved direction for design:
`OPTION_A_POSTGRES_EPHEMERAL_RECOMMENDED`

This authorizes DESIGN ONLY. It does not authorize implementation.

## Problem

The full foundation/integration suite currently forces SQLite, while the repository's technical database baseline is PostgreSQL and the applied migration history contains PostgreSQL-specific DDL.

Exact verified failure:
- GitHub Actions run `36355381623`
- repair commit `659f6b3947e85c2f50cbc6dcfdc506aaf4b8705b`
- PHP/Composer/routing: PASS
- PHPUnit/foundation: FAIL
- SQLite rejects PostgreSQL DDL in applied migration history.

Applied migrations are immutable.

## Goal

Produce an exact, implementation-ready design for moving the full foundation/integration CI suite to an isolated disposable PostgreSQL database without touching pilot/staging/production databases or changing business semantics.

## Change classification

Required:
- DATABASE_GLOBAL
- CI_INFRASTRUCTURE
- ENVIRONMENT_CONFIGURATION
- MIGRATION_COMPATIBILITY_CONTRACT

Shared Core and Academic are regression consumers only unless the design discovers a real contract change.

## Required design decisions

### 1. PostgreSQL version authority
Determine and justify the exact PostgreSQL major version for CI.

Requirements:
- choose a supported explicit major version, not `latest`;
- align with repository/application constraints and expected production target where evidence exists;
- if production version authority is absent, select a conservative CI major and label production parity as pending rather than inventing it.

### 2. CI service definition
Specify exact GitHub Actions service/container design, including:
- service image;
- CI-only database name;
- CI-only username/password strategy;
- port mapping if needed;
- health check;
- readiness timeout/retry;
- `pdo_pgsql` PHP extension;
- required PostgreSQL extensions such as `btree_gist`.

No production/pilot/staging credential may be reused.

### 3. Test database identity guard
Design a fail-closed guard before any migration/test write.

It must verify at minimum:
- `APP_ENV=testing`;
- resolved DB driver is `pgsql`;
- database name matches an explicitly disposable test naming contract;
- host is an allowed CI/local-test host;
- database name is not a protected identity such as pilot/staging/production canonical names;
- no fallback to `DB_URL` or cached config can silently redirect the suite.

Decide whether the guard belongs in:
- `tests/TestCase.php`;
- a reusable `tests/Support/` class;
- an Artisan/test bootstrap command;
- or a narrowly scoped combination.

### 4. phpunit.xml contract
Define exactly which SQLite-forcing variables must be removed or changed.

The design must preserve:
- APP_ENV=testing;
- array/sync test-safe cache/session/queue defaults;
- no secret values committed.

Define how CI injects PostgreSQL settings and how local developers provide a disposable local test target.

### 5. TestCase.php contract
Define exactly how to remove the current hard override to SQLite without weakening the existing guarantee that tests never use the pilot database.

The new contract must be stronger than the current comment:
- fail closed on unsafe DB identity;
- do not silently infer a database;
- preserve cache/rate-limiter/application testing setup.

### 6. Migration-from-zero strategy
Define whether:
- PHPUnit/RefreshDatabase owns migration replay;
- CI performs a separate preflight `migrate:fresh` or equivalent;
- or both are needed.

Avoid redundant destructive commands. No command may target a persistent environment.

### 7. PostgreSQL extension strategy
Inspect applied migrations and identify required extensions and permissions.

At minimum assess:
- `btree_gist`;
- any `pgcrypto`/UUID dependency if present;
- whether default GitHub Actions PostgreSQL image user can create needed extensions.

### 8. Local developer workflow
Design a safe local full-suite workflow:
- separately named disposable PostgreSQL DB or container;
- never the pilot DB;
- explicit opt-in;
- guard behavior;
- cleanup;
- exact command sequence.

SQLite may remain for explicitly isolated unit tests only if such a split is deliberate and documented; do not silently keep SQLite as full-suite authority.

### 9. Exact write scope for implementation
Propose the minimum exact file scope for R2.

Expected candidates:
- `.github/workflows/application-foundation.yml`
- `application/web/phpunit.xml`
- `application/web/tests/TestCase.php`
- optional `application/web/tests/Support/<guard>.php`
- optional narrow test/ops documentation
- evidence/state artifacts

`application/web/config/database.php` should remain unchanged unless D1/R1 proves a dedicated test connection is necessary.

No applied migration file may be changed.

### 10. Verification plan
R2 implementation verification must include:
- structure check;
- PostgreSQL health/readiness;
- explicit safe-DB identity assertion;
- migration-from-zero;
- schema/extension verification where needed;
- targeted database guard tests;
- Shared Core regression;
- Academic regression;
- existing AI database-target guard tests if applicable;
- full suite target: 509 tests / current equivalent;
- exact GitHub Actions run on implementation commit.

Do not hard-code historical test counts as a pass criterion if the suite legitimately changes; record actual exact-current counts.

### 11. Rollback
Define code/workflow rollback only.
No production DB rollback should be required because R2 must touch only disposable test infrastructure.

## Required artifact

Create:

`codex/PLANS/FOUNDATION-DB-R1-POSTGRES-EPHEMERAL-CI-DESIGN-2026-09-28.md`

The plan must contain:
- exact selected PostgreSQL major and rationale;
- exact CI service YAML shape/pseudocode;
- exact test env variables;
- exact identity-guard logic/pseudocode;
- exact file write scope;
- protected zones;
- migration immutability statement;
- extension requirements;
- local developer workflow;
- regression matrix;
- rollback;
- one implementation task ID;
- implementation authorization state = NOT_AUTHORIZED.

## Expected implementation task

`FOUNDATION-DB-R2-POSTGRES-EPHEMERAL-CI-IMPLEMENTATION`

Do not execute R2 inside R1.

## Routing debt reconciliation

During R1, reconcile durable routing so that:
- `codex/CURRENT_TASK_CONTEXT.md` points to R1 and no longer says D1 READY_FOR_EXECUTION;
- `PROJECT_STATE.json` acceptance IDs become R1-specific;
- `PROJECT_STATE.repo_commit` uses D1 final HEAD `bcdd1280d9976020250659baa9a064e278f2e70b` as state basis;
- `NEXT_ACTION.md` reflects R1 execution;
- canonical queue marker `**Next task ID:** `SOC-MD-06`` remains intact.

Do not rewrite unrelated AI/Academic history.

## Forbidden

Do not:
- edit any migration;
- create any migration;
- change workflow implementation;
- change phpunit.xml implementation;
- change TestCase.php implementation;
- change database.php;
- create/run a real database;
- run migrations against pilot/staging/production;
- alter application/business source;
- change Composer/dependencies/PHP;
- alter provider/OpenAI state;
- deploy;
- merge to main.

Read-only inspection is allowed.

## Acceptance criteria

- FDB-R1-AC-01 D1 authority and exact state basis correct.
- FDB-R1-AC-02 explicit PostgreSQL major selected or HOLD with authority gap.
- FDB-R1-AC-03 CI service/health/extension design exact.
- FDB-R1-AC-04 fail-closed test DB identity guard designed.
- FDB-R1-AC-05 phpunit.xml and TestCase.php changes specified exactly.
- FDB-R1-AC-06 no applied migration edit proposed.
- FDB-R1-AC-07 local developer safe workflow designed.
- FDB-R1-AC-08 exact R2 file scope and regression plan defined.
- FDB-R1-AC-09 routing debt reconciled.
- FDB-R1-AC-10 no implementation/DB mutation performed; commit/push clean.

## Closeout

Return:

`FOUNDATION-DB-R1 = COMPLETED / DESIGN_ONLY / PASS`

or HOLD if a material authority gap prevents an implementation-safe design.

Then STOP for ChatGPT audit.
