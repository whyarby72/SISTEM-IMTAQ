# FOUNDATION-DB-R1 — PostgreSQL Ephemeral CI Design

**Status:** `COMPLETED / DESIGN_ONLY / PASS`  
**Date:** `2026-09-28`  
**Repository:** `whyarby72/SISTEM-IMTAQ`  
**Branch:** `chore/foundation-db-r1-postgres-ephemeral-ci-design`  
**Design baseline:** `bcdd1280d9976020250659baa9a064e278f2e70b`  
**Decision authority:** `OPTION_A_POSTGRES_EPHEMERAL_RECOMMENDED`

`IMPLEMENTATION AUTHORIZATION = NOT_AUTHORIZED`

## 1. Selected PostgreSQL version

The CI service is explicitly pinned to PostgreSQL `18.6`:

```yaml
image: postgres:18.6
```

This is not a `latest` tag. It matches the repository's verified pilot
evidence (`PostgreSQL 18.6`) and therefore minimizes schema/extension drift
while the application migrations are replayed in CI. The repository has no
authoritative staging or production PostgreSQL version decision; production
parity remains `PENDING_AUTHORITY` and must not be inferred from the pilot.

The R2 implementation must fail before migrations if the server major/patch
does not match the selected CI contract. A later version change requires a
new decision or an explicit implementation review; it must not be silently
introduced by a floating image tag.

## 2. CI service and health check design

The foundation workflow will run on `ubuntu-latest` and use a job service
container. The following is the exact shape, expressed as design
pseudocode; it is not applied by R1:

```yaml
jobs:
  foundation:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:18.6
        env:
          POSTGRES_DB: imtaq_ci_test
          POSTGRES_USER: imtaq_ci
          POSTGRES_PASSWORD: ${{ secrets.IMTAQ_CI_POSTGRES_PASSWORD }}
        ports:
          - 5432:5432
        options: >-
          --health-cmd "pg_isready -U imtaq_ci -d imtaq_ci_test"
          --health-interval 5s
          --health-timeout 5s
          --health-retries 24
    env:
      APP_ENV: testing
      DB_CONNECTION: pgsql
      DB_HOST: 127.0.0.1
      DB_PORT: 5432
      DB_DATABASE: imtaq_ci_test
      DB_USERNAME: imtaq_ci
      DB_PASSWORD: ${{ secrets.IMTAQ_CI_POSTGRES_PASSWORD }}
      DB_URL: ''
```

R2 must also perform an explicit readiness loop after the service health
check, with a bounded timeout, before the Laravel-resolved identity guard.
The workflow must fail closed if readiness is not reached. The service is
created and destroyed with the job; it is not a shared database server.

The CI credential is a dedicated CI-only credential. It must never equal or
be loaded from the pilot, staging, production, provider, or application
secret stores. It may be a repository/environment secret named only for this
CI service, or a non-sensitive job-local value generated for the disposable
service; the implementation must not print it.

## 3. PHP extension contract

The PHP setup step must install:

```yaml
extensions: mbstring, pdo_pgsql
```

`pdo_pgsql` is mandatory for the authoritative full suite. `pdo_sqlite` may
remain available only for an explicitly separated unit-test lane; it must
not be the full foundation/integration database authority.

The R2 preflight must assert that `pdo_pgsql` is loaded before connecting.

## 4. PostgreSQL extensions and schema requirements

Targeted applied-migration evidence requires:

- `btree_gist`, because multiple migrations execute
  `CREATE EXTENSION IF NOT EXISTS btree_gist` and create `EXCLUDE USING gist`
  constraints for temporal overlap protection;
- `pgcrypto`, because the canonical PostgreSQL schema contract identifies it
  as the UUID-generation extension. R2 must verify whether the current
  migration path creates or depends on it and provision/verify it in the
  disposable service before the suite if required by the actual schema;
- PostgreSQL `CHECK` constraints, raw `ALTER TABLE ... ADD/DROP CONSTRAINT`,
  `jsonb`, UUID columns, and PostgreSQL temporal/constraint behavior must be
  exercised by the migration-from-zero path.

The default PostgreSQL service role is allowed to create these extensions
inside its disposable database. No extension installation or privilege
change may be attempted on pilot/staging/production. If the exact migration
path needs an extension not available in the pinned image, R2 must stop and
record the blocker instead of weakening or skipping the constraint.

## 5. Fail-closed database identity guard

The guard must run before any migration, `RefreshDatabase` activity, or test
write. R2 should implement a reusable guard in
`application/web/tests/Support/` and invoke it from the test bootstrap and
the CI preflight. It may be split into a narrowly scoped bootstrap/command
only if that is required to execute before Artisan migration.

Required logic:

```text
require APP_ENV == "testing"
require DB_CONNECTION == "pgsql"
require DB_URL is empty/null
require configured host in {127.0.0.1, localhost, postgres}
require configured database matches:
  CI:    imtaq_ci_test
  local: imtaq_test_<explicit developer suffix>
reject database names equal to or containing canonical protected identities:
  imtaq, pilot, staging, production, prod, live
resolve Laravel's pgsql connection
require resolved driver == "pgsql"
query current_database(), current_user, inet_server_addr(), server_version()
require current_database matches the same disposable naming contract
require resolved host/address is local CI or explicitly allowed local test host
require server major/patch == 18.6 for the CI contract
never print DB_PASSWORD, DB_URL, a connection string, or secret headers
otherwise abort before migration/test write
```

The guard must not trust only `phpunit.xml`, only environment variables, or
only a direct PDO connection. It must verify the Laravel-resolved identity
and the server's actual identity. Cached configuration and a non-empty
`DB_URL` must not silently redirect the test suite. A mismatch is a hard
failure, not a warning or fallback to SQLite.

## 6. `phpunit.xml` contract

R2 must remove the unconditional full-suite forcing of:

```xml
<env name="DB_CONNECTION" value="sqlite" force="true"/>
<env name="DB_DATABASE" value=":memory:" force="true"/>
```

It must also stop forcing a non-empty database URL. The contract should
retain:

- `APP_ENV=testing`;
- array cache/session settings;
- synchronous queue;
- test-safe broadcast/mail/maintenance settings;
- observability feature disables already present.

CI supplies the PostgreSQL values through the job environment. Local
developers supply explicitly named disposable PostgreSQL values through a
non-committed testing environment file or shell environment. No credential
or database URL is committed in `phpunit.xml`.

## 7. `TestCase.php` contract

R2 must remove the unconditional calls that set the default connection to
SQLite and set the SQLite database to `:memory:`. It must preserve the
existing testing setup for `app.env`, session, cache, queue, rate limiter,
and application provider boot behavior.

Before returning the application, the test bootstrap must invoke the
fail-closed guard described above. The guard must be stronger than the
current comment: it must reject the pilot even when cached configuration or
an accidental `.env` value would otherwise point there. No silent driver
fallback is permitted.

## 8. Migration-from-zero strategy

The disposable PostgreSQL database is empty at job start. R2 must make one
explicit migration-from-zero preflight authoritative:

```text
assert_test_database_identity
php artisan migrate:fresh --database=pgsql --force
assert_test_database_identity_again
assert required extensions and schema constraints
run the foundation/integration PHPUnit suite
```

The command is safe only because the identity guard has already proven the
database is the job-local disposable database. It must never run against a
persistent environment.

`RefreshDatabase` remains the per-test isolation mechanism. R2 must verify
Laravel's migration-state behavior so that the preflight and
`RefreshDatabase` do not create an uncontrolled redundant destructive
cycle. If the framework requires a single owner for migration replay, the
implementation must make that ownership explicit and document it; it must
not rely on an undocumented order.

## 9. Safe local developer workflow

The supported local full-suite path is an explicit disposable PostgreSQL
container or a separately named local database:

```text
start PostgreSQL 18.6 locally with a disposable volume
create database imtaq_test_<developer-suffix>
create a test-only role/credential for that database
export APP_ENV=testing
export DB_CONNECTION=pgsql
export DB_HOST=127.0.0.1
export DB_PORT=<isolated-port>
export DB_DATABASE=imtaq_test_<developer-suffix>
export DB_USERNAME=<test-only-role>
export DB_PASSWORD=<test-only-password>
export DB_URL=
run the Laravel-resolved identity guard
run migrate:fresh only after the guard passes
run the targeted or full PHPUnit suite
destroy the container/database when finished
```

The pilot database name `imtaq` and any staging/production name are
explicitly rejected. A missing or ambiguous local target fails closed; it
does not fall back to SQLite for the full suite. SQLite can be retained only
for a separately named unit-only lane that never claims foundation/schema
coverage.

## 10. Exact R2 write scope

R2 may write only the following, subject to a separate implementation
authorization:

1. `.github/workflows/application-foundation.yml` — PostgreSQL 18.6 service,
   health/readiness, CI environment, `pdo_pgsql`, identity preflight,
   migration-from-zero, extension/schema checks, and test invocation.
2. `application/web/phpunit.xml` — remove SQLite full-suite forcing and keep
   only safe framework-test defaults.
3. `application/web/tests/TestCase.php` — remove the SQLite override and
   call the fail-closed identity guard while preserving test service setup.
4. `application/web/tests/Support/TestDatabaseIdentityGuard.php` or one
   narrowly equivalent guard/bootstrap file — shared guard logic and safe
   diagnostics.
5. Optional narrowly scoped test/operations documentation describing local
   disposable PostgreSQL setup.
6. `codex/CHANGE_MANIFESTS/FOUNDATION-DB-R2-2026-09-28.md` plus state/evidence
   artifacts required by the repository contract.

`application/web/config/database.php` is intentionally excluded from the
minimum scope because it already contains a usable `pgsql` connection. It
may be touched only if R2 proves a dedicated test connection is necessary,
with an explicit impact update before the edit.

Never write:

- any applied migration or new business migration;
- pilot/staging/production database or data;
- Academic/Shared Core business source;
- Composer/PHP/dependency contracts;
- AI/provider/OpenAI source or state;
- production secrets or deployment configuration.

## 11. Regression and verification plan

R2 must run, in order:

1. structure/routing verification;
2. PHP extension and PostgreSQL service health checks;
3. direct and Laravel-resolved disposable identity guard tests;
4. migration-from-zero;
5. extension and constraint/schema checks, including `btree_gist`,
   PostgreSQL exclusion constraints, JSONB, and raw checks where applicable;
6. targeted database guard tests;
7. Shared Core regression;
8. Academic regression;
9. existing AI database-target guard tests;
10. the full current PHPUnit suite, reporting exact current counts;
11. PHP lint, Pint/view cache only if required by the workflow contract;
12. exact GitHub Actions run on the implementation commit.

The baseline failure must be resolved without changing business semantics.
Any new failure after the SQLite blocker is resolved must be recorded as a
new blocker; it must not be reported as CI PASS.

## 12. Rollback and recovery

Rollback is limited to reverting the R2 workflow/test-harness/guard commit
to the last known-good commit. Stop using the changed CI job and recreate the
disposable service from the previous definition.

No pilot, staging, or production database rollback is part of R2 because no
such database may be touched. A disposable CI database is destroyed with
the job. There is no restore-over-pilot path and no migration-history edit
path.

## 13. Protected zones and immutability

- Applied migrations remain immutable.
- Production/staging/pilot connection identities and credentials remain
  outside the test contract.
- Shared Core identity, RBAC, Academic attendance, session-occurrence, and
  reporting semantics remain unchanged.
- Composer lock/package versions and the approved PHP 8.4.x contract remain
  unchanged.
- Provider/OpenAI state and secrets remain untouched.

## Acceptance mapping

| ID | R1 result |
| --- | --- |
| FDB-R1-AC-01 | PASS — D1 authority and state basis `bcdd1280d9976020250659baa9a064e278f2e70b` confirmed. |
| FDB-R1-AC-02 | PASS — PostgreSQL 18.6 explicitly selected; production parity remains pending authority. |
| FDB-R1-AC-03 | PASS — service, health check, readiness, credentials, and `pdo_pgsql` specified. |
| FDB-R1-AC-04 | PASS — fail-closed direct and Laravel-resolved identity guard specified. |
| FDB-R1-AC-05 | PASS — exact `phpunit.xml` and `TestCase.php` contracts specified. |
| FDB-R1-AC-06 | PASS — no applied migration edit proposed. |
| FDB-R1-AC-07 | PASS — local disposable PostgreSQL workflow specified. |
| FDB-R1-AC-08 | PASS — exact R2 scope and regression plan specified. |
| FDB-R1-AC-09 | PASS — routing reconciled to `FOUNDATION-DB-R2-POSTGRES-EPHEMERAL-CI-IMPLEMENTATION`. |
| FDB-R1-AC-10 | PASS — commit `16e6193` is pushed and local/upstream parity is verified at closeout. |

## Closeout

`FOUNDATION-DB-R1 = COMPLETED / DESIGN_ONLY / PASS`

`DATABASE WRITE = NONE`  
`MIGRATION CHANGE = NONE`  
`APPLICATION SOURCE CHANGE = NONE`  
`WORKFLOW IMPLEMENTATION = NONE`  
`IMPLEMENTATION AUTHORIZATION = NOT_AUTHORIZED`  
`NEXT_ATOMIC_TASK = FOUNDATION-DB-R2-POSTGRES-EPHEMERAL-CI-IMPLEMENTATION`
