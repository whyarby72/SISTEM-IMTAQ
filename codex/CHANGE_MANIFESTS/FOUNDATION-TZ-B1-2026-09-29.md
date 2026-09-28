# FOUNDATION-TZ-B1 — Today Query Boundary Remediation

- Task: `FOUNDATION-TZ-B1-TODAY-QUERY-BOUNDARY-REMEDIATION`
- Branch: `fix/foundation-tz-b1-today-query-boundary`
- Baseline: `bb3e923e9cde3228e3cc7e5065c048a2c58a47d0`
- Authority: `OPTION_A — ASIA_JAKARTA_BUSINESS / UTC_TECHNICAL_SESSION / TIMEZONE_AWARE_BOUNDARIES`

## Scope

- Kept business-day boundaries in `Asia/Jakarta`.
- Added copied UTC instants for `planned_start_at` SQL bindings, preserving the
  start-inclusive and next-local-midnight-exclusive window.
- Removed the disposable PostgreSQL service password and masking step.
- Configured only the GitHub-hosted disposable CI service with
  `POSTGRES_HOST_AUTH_METHOD=trust`; no `DB_PASSWORD` is configured.
- Preserved identity guard, PostgreSQL 18.6, UTC session assertion, migrations,
  schema constraints, operational-state logic, authorization, and dashboard code.

## Validation before exact CI

- PHP lint: PASS for changed PHP files.
- Pint: PASS for changed PHP files.
- `git diff --check`: PASS.
- `python3 scripts/check_project_structure.py`: PASS (`current task=SOC-MD-06`).
- Local PHPUnit: safely blocked because no disposable PostgreSQL is available;
  the fail-closed guard prevented use of protected pilot `imtaq`.

## Exact CI evidence

- Pending on the pushed implementation commit.
- Required evidence: focused Today tests, UTC session, identity guard,
  migrate-from-zero, schema/extension checks, Academic/full foundation suite,
  and absence of `POSTGRES_PASSWORD`, disposable password literals, and
  `DB_PASSWORD` values in Actions logs.

## Safety

- Application business services other than the authorized Today query boundary:
  unchanged.
- Dashboard service: unchanged.
- Migration/schema/dependency/database/provider/OpenAI mutation: NONE.
- Pilot/staging/production database or credentials: untouched.
