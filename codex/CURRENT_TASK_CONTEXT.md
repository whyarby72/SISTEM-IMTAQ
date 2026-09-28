# CURRENT TASK CONTEXT

**Task:** `FOUNDATION-DB-R3-POSTGRES-REGRESSION-DIAGNOSIS`  
**State:** `COMPLETED / DIAGNOSIS_ONLY / PASS`
**Current phase:** `CI / POSTGRESQL FULL-SUITE REGRESSION DIAGNOSIS`  
**Branch:** `chore/foundation-db-r3-postgres-regression-diagnosis`  
**State-basis:** `35bed6d7657b7ff21eaabb2e1e81feefc61158a9`

## Accepted R2 outcome

`FOUNDATION-DB-R2 = COMPLETED / PASS_WITH_NEW_BLOCKER`

Resolved:
- PostgreSQL 18.6 ephemeral CI authority
- pdo_pgsql foundation lane
- fail-closed disposable test DB identity guard
- migration-from-zero on PostgreSQL
- btree_gist/schema preflight
- FOUNDATION_SQLITE_MIGRATION_COMPATIBILITY

Exact-current CI:
- run: `36402569265`
- head: `349ef67ac6e9e6a2d02a25e137084b00fe820c21`
- result: FAIL at foundation PHPUnit
- summary: `16 failed, 15 passed, 490 warnings, 2171 assertions`

Active blocker:
`FOUNDATION_POSTGRESQL_FULL_SUITE_REGRESSION`

## Required now

1. `codex/TASK_CONTEXTS/FOUNDATION-DB-R3-POSTGRES-REGRESSION-DIAGNOSIS.md`
2. exact Actions run `36402569265`
3. all exact failing test files/lines from the run
4. corresponding Academic services/models
5. canonical audit schema/model
6. canonical AI provider credential schema/model
7. class-session schema/constraints
8. timezone/app configuration
9. `PROJECT_STATE.json`
10. `NEXT_ACTION.md`

## Output

Create:

`codex/DIAGNOSTICS/FOUNDATION-DB-R3-POSTGRES-REGRESSION-DIAGNOSIS-2026-09-28.md`

Every exact failure must be classified as one of:
- TEST_FIXTURE
- TEST_ASSERTION
- APPLICATION_DEFECT
- CONTRACT_GAP

Timezone/Today/Dashboard failures require explicit:
- TZ-A fixture/clock mismatch
- TZ-B application defect
- TZ-C contract gap

Also classify:
`CI_EPHEMERAL_CREDENTIAL_LOG_VISIBILITY`

## Boundary

DIAGNOSIS ONLY.

Do not modify tests, application source, workflow implementation, PHPUnit/TestCase,
database config, migrations, constraints, dependencies, provider state, or any
persistent database.

## Exit

Commit/push diagnosis + routing/evidence only.
Set one recommended next task or HOLD.
`IMPLEMENTATION_AUTHORIZATION = NOT_AUTHORIZED`.
STOP for ChatGPT audit.

## R3 closeout

The exact current run `36402569265` is reconciled in the diagnostic artifact.
All 16 failures have one primary classification. The five time-sensitive
failures are held at TZ-C pending an authoritative timezone/storage rule.
PostgreSQL constraints remain authoritative. No source, test, workflow,
migration, database, provider, or dependency mutation was performed.

Recommended next task after ChatGPT audit:
`FOUNDATION-DB-R4-POSTGRES-REGRESSION-REMEDIATION`.
