# FOUNDATION-DB-R2 — PostgreSQL Ephemeral CI Implementation

**Mode:** EXISTING_REPO_ADOPTION  
**Task type:** CONTROLLED_DATABASE_GLOBAL_TEST_INFRA_IMPLEMENTATION  
**Owner:** CODEX  
**State:** READY_FOR_EXECUTION  
**Branch:** chore/foundation-db-r2-postgres-ephemeral-ci  
**State-basis:** 9332bd21cb1cc28942ae8c10c28cd2b80600fd30

## Authority

FOUNDATION-DB-D1:
`CLOSED / ACCEPTED / OPTION_A_POSTGRES_EPHEMERAL_RECOMMENDED`

FOUNDATION-DB-R1:
`CLOSED / ACCEPTED / DESIGN_ONLY / PASS`

Authoritative plan:
`codex/PLANS/FOUNDATION-DB-R1-POSTGRES-EPHEMERAL-CI-DESIGN-2026-09-28.md`

This task authorizes implementation of the R1 design only.

## Goal

Replace SQLite as the authoritative full foundation/integration test database with an isolated disposable PostgreSQL 18.6 service in CI, enforce a fail-closed test-database identity guard, replay migrations from zero on PostgreSQL, run the full verification suite, and verify the exact implementation commit in GitHub Actions.

The implementation must never connect to or mutate pilot, staging, or production databases.

## Design clarification before implementation

R1 contains a wording collision in its guard pseudocode: it allows test database names beginning with `imtaq_` while also saying to reject names "containing" `imtaq`.

R2 must implement the following precise rule instead:

- CI allowed database name: exactly `imtaq_ci_test`
- local allowed database name: regex `^imtaq_test_[a-z0-9][a-z0-9_-]*$` after normalization to lowercase
- explicitly reject the exact protected base identity `imtaq`
- explicitly reject any allowed-looking name containing protected environment markers `pilot`, `staging`, `production`, `prod`, or `live`
- reject every database name outside the two allow patterns above

This clarification preserves the R1 security intent and prevents the guard from rejecting its own approved test identities.

## Approved write scope

Implementation files:

1. `.github/workflows/application-foundation.yml`
2. `application/web/phpunit.xml`
3. `application/web/tests/TestCase.php`
4. `application/web/tests/Support/TestDatabaseIdentityGuard.php`
5. One narrowly scoped guard regression test, preferred:
   `application/web/tests/Feature/Foundation/TestDatabaseIdentityGuardTest.php`
   - equivalent path is allowed only if required by repository conventions
6. Optional narrow local-test operations documentation only if needed for reproducibility
7. Closeout/evidence:
   - `codex/CHANGE_MANIFESTS/FOUNDATION-DB-R2-2026-09-28.md`
   - `PROJECT_STATE.json`
   - `TEST_MATRIX.csv`
   - `EVIDENCE_INDEX.json`
   - `codex/CURRENT_TASK_CONTEXT.md`
   - `NEXT_ACTION.md`

`application/web/config/database.php` is NOT in default write scope.

If implementation proves it is strictly required, STOP and report:
`R2_HOLD_DATABASE_CONFIG_SCOPE_EXPANSION_REQUIRED`
before editing it.

## Required implementation

### A. GitHub Actions PostgreSQL service

Use explicit:

`postgres:18.6`

Do not use `latest`.

If the exact image cannot be pulled or does not run PostgreSQL 18.6:
STOP with `R2_HOLD_POSTGRES_18_6_IMAGE_UNAVAILABLE`.
Do not silently switch versions.

Provision a disposable job-local database:

- DB: `imtaq_ci_test`
- user: `imtaq_ci`
- password: CI-only disposable credential
- host from runner: `127.0.0.1`
- port: `5432`

Credential rules:
- never reuse pilot/staging/production credentials;
- never read production secret stores;
- do not print password or DSN;
- a clearly non-sensitive disposable CI-only password may be used if that avoids an external secret dependency, provided it is scoped only to the ephemeral service and is explicitly documented as non-production/non-secret.

Add bounded health/readiness checking.

PHP setup must include:
- `mbstring`
- `pdo_pgsql`

The full foundation lane must not depend on `pdo_sqlite`.

### B. CI environment

Set explicitly for the job:

- `APP_ENV=testing`
- `DB_CONNECTION=pgsql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=5432`
- `DB_DATABASE=imtaq_ci_test`
- `DB_USERNAME=imtaq_ci`
- `DB_PASSWORD=<CI-only disposable credential>`
- `DB_URL=`

Preserve existing PHP 8.4.x runtime guard.

### C. Fail-closed identity guard

Implement `Tests\Support\TestDatabaseIdentityGuard` or the exact equivalent named above.

Before any migration/test write it must verify:

1. APP_ENV resolves to `testing`.
2. Laravel default DB connection resolves to `pgsql`.
3. `DB_URL` / resolved pgsql URL is empty/null.
4. Configured host is an allowed local test host:
   - `127.0.0.1`
   - `localhost`
   - service alias `postgres` only if actually used.
5. Configured database name satisfies the exact CI/local allow rule.
6. Protected environment tokens are rejected.
7. Laravel-resolved connection driver is `pgsql`.
8. Query actual server identity using safe metadata only:
   - `current_database()`
   - `current_user`
   - server version / `server_version_num`
9. Actual database identity matches configured approved disposable identity.
10. In CI, server version must be PostgreSQL 18.6. Prefer a stable numeric check such as `server_version_num = 180006` if supported by the running image.
11. Diagnostics may print only safe driver/database/host/version metadata. Never print password, DB_URL, DSN, secret headers, or full connection string.

Any mismatch = hard failure before migration/test writes.

### D. CI pre-migration guard

The same guard semantics must run before `migrate:fresh`.

Use the minimum repository-compatible approach:
- bootstrap Laravel from a narrow PHP preflight using Composer's dev autoload and the guard class; or
- another narrow mechanism that uses the same guard logic.

Do not add production application commands merely for CI unless absolutely required.

### E. phpunit.xml

Remove the full-suite forced SQLite authority:

- remove forced `DB_CONNECTION=sqlite`
- remove forced `DB_DATABASE=:memory:`

Preserve:
- `APP_ENV=testing`
- cache/session array behavior
- queue sync
- mail/broadcast test safety
- observability disables.

Do not commit any credential.

Ensure `DB_URL` cannot redirect the test connection. Keeping it explicitly empty is acceptable if it does not override the CI PostgreSQL environment incorrectly; verify actual PHPUnit environment precedence.

### F. tests/TestCase.php

Remove:
- forced default connection `sqlite`
- forced SQLite `:memory:`
- SQLite URL override

Preserve:
- testing app environment
- array session/cache/limiter behavior
- sync queue
- RateLimiter reset
- AppServiceProvider boot behavior

Invoke the fail-closed test DB identity guard before a test can write.

### G. Migration-from-zero

After identity guard passes:

`php artisan migrate:fresh --database=pgsql --force`

Then re-run identity guard / safe schema assertions.

Do not edit any applied migration.

Assess `RefreshDatabase` interaction. If the preflight plus framework test lifecycle creates unsafe/redundant destructive replay, make the smallest test-infrastructure adjustment within authorized scope and record it. Do not change business semantics.

### H. PostgreSQL extensions/schema assertions

Verify after migration:
- `btree_gist` exists where migration history requires it;
- assess whether `pgcrypto` is actually required by current migrations; verify it only when applicable;
- PostgreSQL-specific schema path completes successfully.

Do not weaken/skips constraints to make tests pass.

### I. Regression

Minimum closeout validation:

1. repository structure/routing check
2. PHP 8.4.x guard
3. `pdo_pgsql` loaded
4. PostgreSQL 18.6 health/readiness
5. guard negative cases:
   - APP_ENV != testing rejected
   - SQLite driver rejected
   - exact `imtaq` rejected
   - staging/production/pilot/prod/live names rejected
   - non-empty redirecting DB_URL rejected
6. guard positive cases for approved CI/local identities
7. migration-from-zero PASS
8. extension/schema assertions PASS
9. Shared Core regression
10. Academic regression
11. existing AI database-target guard regression where applicable
12. full current PHPUnit suite, record exact counts
13. `./scripts/verify-foundation.sh`
14. exact GitHub Actions run on exact implementation commit

If another independent failure appears after PostgreSQL migration compatibility is resolved:
record `PASS_WITH_NEW_BLOCKER`; do not hide or relabel it.

## Blocker resolution rule

Only mark `FOUNDATION_SQLITE_MIGRATION_COMPATIBILITY = RESOLVED` when exact-current CI proves the full suite is no longer failing due to SQLite migration incompatibility.

If CI is fully green:
- mark exact-current CI PASS;
- route next product track to `ACADEMIC_WEB_COMPLETION_REVIEW`;
- preserve canonical queue gate `SOC-MD-06`.

## Forbidden

Do not:

- edit any file under `application/web/database/migrations/`;
- create a new migration;
- touch pilot/staging/production databases;
- use pilot/staging/production credentials;
- modify Academic/Shared Core business source;
- modify `application/web/config/database.php` without HOLD/re-authorization;
- change PHP runtime contract;
- change Composer manifest/lock/dependencies;
- change provider/OpenAI state;
- deploy;
- change hosting;
- merge to main;
- weaken DB constraints;
- fall back to SQLite for the full foundation/integration suite;
- switch PostgreSQL versions silently.

## Acceptance criteria

- FDB-R2-AC-01 exact R1 baseline/branch state confirmed.
- FDB-R2-AC-02 PostgreSQL 18.6 ephemeral service implemented and healthy.
- FDB-R2-AC-03 `pdo_pgsql` authoritative for foundation lane.
- FDB-R2-AC-04 phpunit/TestCase SQLite forcing removed.
- FDB-R2-AC-05 fail-closed identity guard implemented with corrected name semantics.
- FDB-R2-AC-06 migration-from-zero succeeds without migration edits.
- FDB-R2-AC-07 required extension/schema assertions pass.
- FDB-R2-AC-08 targeted negative/positive guard tests pass.
- FDB-R2-AC-09 required Shared Core/Academic/AI/full regressions recorded.
- FDB-R2-AC-10 exact implementation commit GitHub Actions result recorded.
- FDB-R2-AC-11 no protected persistent DB/business/provider/deployment mutation.
- FDB-R2-AC-12 change manifest/state/evidence reconciled.
- FDB-R2-AC-13 remote parity PASS and worktree clean.
- FDB-R2-AC-14 if CI green, next product track = `ACADEMIC_WEB_COMPLETION_REVIEW`.

## Closeout states

PASS:
`FOUNDATION-DB-R2 = COMPLETED / PASS`

PASS_WITH_NEW_BLOCKER:
PostgreSQL/SQLite authority issue is resolved, but another independent CI failure is evidenced.

HOLD:
unsafe database identity, image/version mismatch, config scope expansion, migration edit requirement, or protected-environment access risk.

Commit/push implementation and evidence, then STOP for ChatGPT audit.
