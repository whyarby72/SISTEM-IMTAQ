# FOUNDATION-DB-R4 — PostgreSQL Regression Remediation

- Task: `FOUNDATION-DB-R4-POSTGRES-REGRESSION-REMEDIATION`
- Branch: `chore/foundation-db-r4-postgres-regression-remediation`
- Baseline: `132f4dbe3f17c5890d149be8ce08711c4118ca3d`
- Owner-approved contract: Asia/Jakarta business semantics, UTC technical PostgreSQL session, aware boundaries.

## Scope implemented

- Added the supported Laravel PostgreSQL `timezone` connection key with a UTC default.
- Added a CI assertion that the PostgreSQL technical session timezone is UTC.
- Removed the literal disposable password from the job-level environment and inject it after an early Actions mask.
- Corrected the 11 diagnosed fixture/assertion regressions without changing migrations, constraints, or business services.
- Converted the five TZ-C fixture/clock paths to explicit Asia/Jakarta-aware values.

Protected migrations, schema constraints, Academic business services, dependencies, provider state, persistent data, and deployment were not changed.

## Validation

- PHP lint: PASS for all changed PHP/config files.
- `python3 scripts/check_project_structure.py`: PASS.
- `git diff --check`: PASS.
- Local PHPUnit: environment-blocked by the fail-closed guard because no disposable PostgreSQL service is available and `.env` resolves to protected pilot `imtaq`; no pilot connection/write was attempted.
- Pint: changed dashboard test formatted; unrelated existing findings remain in `tests/Support/TestDatabaseIdentityGuard.php`.
- Exact disposable PostgreSQL CI: pending on the pushed implementation commit.

## Safety

- Pilot/staging/production database write: NONE.
- Migration/schema change: NONE.
- Provider/OpenAI mutation: NONE.
- Historical timestamp rewrite: NONE.
