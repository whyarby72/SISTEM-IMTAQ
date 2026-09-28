# FOUNDATION-DB-R4 — PostgreSQL Regression Remediation

**Mode:** EXISTING_REPO_ADOPTION  
**Task type:** CONTROLLED_TEST_CONFIG_SECURITY_REMEDIATION  
**Owner:** CODEX  
**State:** READY_FOR_EXECUTION  
**Branch:** chore/foundation-db-r4-postgres-regression-remediation  
**State-basis:** 4d54283f3c8aec7cb04f203837fed746c2b71104

## Authority

FOUNDATION-TZ-D1 is CLOSED / ACCEPTED.

Project Owner explicitly approved:

`OPTION_A — ASIA_JAKARTA_BUSINESS / UTC_TECHNICAL_SESSION / TIMEZONE_AWARE_BOUNDARIES`

Canonical decision artifact:

`codex/DECISIONS/FOUNDATION-TZ-D1-TIMEZONE-STORAGE-AUTHORITY-2026-09-28.md`

Owner authority:
- business/institutional timezone = `Asia/Jakarta`;
- human local academic times are interpreted explicitly as Asia/Jakarta at the application/domain boundary;
- PostgreSQL `timestamptz` represents an absolute instant;
- technical/PostgreSQL session baseline = `UTC`;
- Today/local-day/schedule/report/display semantics = `Asia/Jakarta`;
- offset-less timestamps may not rely on DB/session defaults;
- tests use timezone-aware values or explicit offsets;
- no applied migration edit;
- no historical timestamp mass rewrite.

## Trigger

Exact-current PostgreSQL CI evidence from run `36402569265`:

- PostgreSQL 18.6 readiness: PASS
- identity guard: PASS
- migration-from-zero: PASS
- schema/extension assertions: PASS
- full foundation PHPUnit: FAIL
- result: `16 failed, 15 passed, 490 warnings, 2171 assertions`

R3 classification:
- 7 `TEST_FIXTURE`
- 4 `TEST_ASSERTION`
- 5 `CONTRACT_GAP / TZ-C`

R4 remediates the 11 test-only failures, enforces the ratified technical timezone baseline narrowly, converts the five TZ-C fixtures to timezone-aware representations, and replays them.

R4 MUST NOT modify Academic business services before replay proves a residual source defect.

## Change classification

- `DATABASE_GLOBAL` — PostgreSQL session-timezone configuration
- `SECURITY_GLOBAL` — CI disposable credential log visibility
- `CROSS_DOMAIN` — shared timestamp contract and Shared Core audit tests
- `ACADEMIC_DOMAIN` — regression consumers/tests only in this task

Application business source remains protected unless a separate post-R4 task is authorized.

## Approved write scope

### Runtime/config enforcement
1. `application/web/config/database.php`
   - only to enforce explicit PostgreSQL session timezone `UTC`;
   - preferred form: framework-supported `timezone` connection key using
     `env('DB_TIMEZONE', 'UTC')`;
   - first verify the installed Laravel/Postgres connector actually supports the
     selected configuration key.
   - if framework support is absent and a custom connector/provider/source
     change would be required:
     STOP = `R4_HOLD_DB_TIMEZONE_ENFORCEMENT_SCOPE_EXPANSION`.

2. `.github/workflows/application-foundation.yml`
   - enforce CI PostgreSQL/session timezone contract;
   - remediate CI disposable credential log visibility;
   - preserve PostgreSQL 18.6, identity guard, migration-from-zero, PHP 8.4,
     Composer, and foundation verification.

### Test remediation
3. `application/web/tests/Feature/Academic/AI/AcademicAiRuntimeTest.php`
4. `application/web/tests/Feature/Academic/AI/AiProviderConfigurationTest.php`
5. `application/web/tests/Feature/Shared/CoreContractsTest.php`
6. `application/web/tests/Feature/Academic/AcademicTodaySessionServiceTest.php`
7. `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
8. `application/web/tests/Feature/Academic/SubstitutionServiceTest.php`
9. `application/web/tests/Feature/Academic/SwapServiceTest.php`
10. `application/web/tests/Feature/Academic/TeacherAttendanceServiceTest.php`

Optional:
11. one narrowly scoped PostgreSQL timezone contract regression test under
    `application/web/tests/Feature/Foundation/` if existing tests cannot
    prove the connection/session timezone and aware-boundary behavior cleanly.

### Evidence/state
12. `codex/CHANGE_MANIFESTS/FOUNDATION-DB-R4-2026-09-28.md`
13. `PROJECT_STATE.json`
14. `TEST_MATRIX.csv`
15. `EVIDENCE_INDEX.json`
16. `codex/CURRENT_TASK_CONTEXT.md`
17. `NEXT_ACTION.md`

No other files are authorized.

## Explicitly forbidden

Do not modify:
- any file under `application/web/database/migrations/`;
- PostgreSQL constraints or schema;
- `AcademicTodaySessionService.php`;
- `AcademicRoleDashboardService.php`;
- other application/business source;
- provider/OpenAI runtime/configuration;
- PHP/Composer/dependencies;
- pilot/staging/production database or credentials;
- historical timestamp data;
- deployment/hosting;
- main branch.

If a residual timezone failure proves business-source behavior is wrong:
STOP and report
`R4_TZ_B_APPLICATION_DEFECT_CANDIDATE`.
Do not patch the service in R4.

## Phase 0 — exact baseline

Before editing:

1. verify branch, HEAD, upstream, clean worktree;
2. verify state-basis `4d54283f3c8aec7cb04f203837fed746c2b71104`;
3. verify the approved Option A decision artifact;
4. verify applied migration files are unchanged;
5. inventory the 16 R3 failures and map them to the R4 remediation items below.

## Phase 1 — 11 independently classified test-only failures

Apply Minimum Necessary Change only.

### A. secret_last4 fixture

In `AcademicAiRuntimeTest.php`:
- replace the five-character synthetic suffix with exactly four characters;
- do not change the canonical `varchar(4)` schema or model contract.

### B. audit chronology assertions

In `AiProviderConfigurationTest.php`:
- replace non-canonical `created_at` ordering/lookups with canonical
  `occurred_at`;
- retain all secret-safety and failure-category assertions.

Do not add Laravel timestamps to `audit_logs`.

### C. JSONB assertion

In `CoreContractsTest.php`:
- replace wildcard scalar comparison against JSONB with a PostgreSQL-valid
  JSON-aware assertion;
- preserve the original redaction intent;
- do not cast/flatten JSONB in production merely to satisfy the test.

### D. exclusion-constraint fixtures

In Today/Substitution/Swap/TeacherAttendance tests:
- construct fixture state that is legal under
  `class_sessions_active_no_overlap`;
- preserve the business precondition actually being tested;
- do not disable/defer/drop the exclusion constraint.

For tests whose previous setup inserted an invalid overlapping active session
before reaching the service under test, redesign the fixture so the intended
teacher/participation/status conflict is exercised without violating the class
session exclusion first.

### E. closed status vocabulary fixture

The test that persists `session_status=UNKNOWN` must no longer violate the
canonical CHECK constraint.

Preferred remediation:
- test the canonical persisted contract by asserting unknown persisted status is
  rejected, OR
- use a non-persisted seam only if the service fallback behavior itself remains
  an explicit documented requirement.

Do not broaden the CHECK constraint.

## Phase 2 — ratified timezone contract enforcement

### A. PostgreSQL session baseline

Verify the installed framework's PostgreSQL connector supports the proposed
connection `timezone` configuration.

If supported:
- set the pgsql connection timezone explicitly to
  `env('DB_TIMEZONE', 'UTC')`;
- CI must set/verify UTC explicitly;
- add a focused assertion such as `SHOW TIME ZONE` resolving to UTC.

If not supported without custom source:
STOP with
`R4_HOLD_DB_TIMEZONE_ENFORCEMENT_SCOPE_EXPANSION`.

### B. Timezone-aware fixtures

For the five prior TZ-C failures only:

- replace bare business timestamp strings with timezone-aware
  `Asia/Jakarta` Carbon values or explicit `+07:00` timestamps;
- test clocks must carry the same explicit timezone/instant semantics;
- local-day boundaries remain start-inclusive / next-day-exclusive;
- do not change expected business outcomes merely to fit PostgreSQL;
- do not modify the service source during this phase.

### C. Mandatory focused replay gate

After only config/test changes, run the five former TZ-C tests.

If all five pass:
- classify the historical CI failures as
  `TZ-A / FIXTURE_OR_ENVIRONMENT_CONTRACT_MISMATCH`;
- keep Academic business source unchanged.

If any still fail:
- capture exact failing method, expected/actual values, resolved timestamps,
  PostgreSQL session timezone, app timezone, and relevant SQL/query boundary;
- classify as `TZ-B_CANDIDATE` or residual `TZ-C` with evidence;
- STOP:
  `R4_TZ_B_APPLICATION_DEFECT_CANDIDATE`
  or
  `R4_HOLD_TIMEZONE_CONTRACT_STILL_AMBIGUOUS`;
- do not modify Academic business source in this task.

## Phase 3 — CI ephemeral credential log visibility

Remediate:
`CI_EPHEMERAL_CREDENTIAL_LOG_VISIBILITY`.

Requirements:
- keep the PostgreSQL service disposable and CI-only;
- do not use pilot/staging/production credentials;
- do not introduce a production secret dependency;
- the literal credential value must not appear in normal Actions logs;
- preserve database identity guard behavior.

The exact implementation may use an Actions masking/runtime mechanism or an
equivalent no-persistent-secret approach.

If the only way found requires a persistent production/environment secret:
STOP and redesign; do not use such a secret.

## Phase 4 — regression

Run at minimum:

1. repository structure check;
2. database identity guard tests;
3. PostgreSQL session timezone assertion;
4. the 11 corrected non-timezone tests;
5. the five timezone replay tests;
6. focused Shared Core tests;
7. focused Academic tests;
8. focused AI/provider tests relevant to changed tests;
9. migration-from-zero on disposable PostgreSQL;
10. schema/extension assertions;
11. full current PHPUnit suite;
12. `./scripts/verify-foundation.sh`;
13. lint/Pint/view cache checks required by repository protocol;
14. exact GitHub Actions run on the implementation commit.

Record actual test/warning/assertion counts; do not hard-code an obsolete suite
count as the pass condition.

490-warning cleanup is not part of R4 unless a warning is proven causal.

## Exact-current CI audit rule

Preferred closeout requires exact GitHub Actions evidence.

If local `gh` authentication is unavailable but:
- implementation is pushed,
- local safe verification is complete,
- workflow trigger is present,

then close as:
`COMPLETED / PASS_PENDING_EXTERNAL_CI_AUDIT`

and route to ChatGPT for exact Actions inspection.

Do NOT classify invalid local `gh` authentication as an application blocker.

If exact Actions is directly available to Codex, record it normally.

## Blocker resolution

If full exact-current CI becomes green:

- `FOUNDATION_POSTGRESQL_FULL_SUITE_REGRESSION = RESOLVED`
- `CI_EPHEMERAL_CREDENTIAL_LOG_VISIBILITY = RESOLVED`
- five prior timezone failures = `TZ-A` if they passed after aware
  fixture/config replay without business source change
- next product track =
  `ACADEMIC_WEB_COMPLETION_REVIEW`

If an independent new failure appears:
`PASS_WITH_NEW_BLOCKER` with exact evidence.

If any five timezone failures remain:
STOP under the timezone residual rules above; no business source patch.

## Historical timestamp boundary

R4 does not inspect or rewrite persistent pilot timestamp data.

If implementation evidence creates a concrete concern that existing pilot rows
may contain ambiguous instants, record a future:
`FOUNDATION-TZ-H1-HISTORICAL-TIMESTAMP-RECONCILIATION`
as read-only follow-up.

Do not make it a prerequisite for CI unless exact evidence requires it.

## Acceptance criteria

- FDB-R4-AC-01 approved Option A authority recorded and unchanged.
- FDB-R4-AC-02 exact R3 failure inventory reconciled.
- FDB-R4-AC-03 11 test-only failures remediated without schema weakening.
- FDB-R4-AC-04 PostgreSQL technical session timezone explicitly UTC.
- FDB-R4-AC-05 five former TZ-C fixtures are timezone-aware.
- FDB-R4-AC-06 focused timezone replay determines TZ-A or STOP before source edit.
- FDB-R4-AC-07 CI disposable credential no longer exposed in normal logs.
- FDB-R4-AC-08 no applied migration/business-source/persistent-data mutation.
- FDB-R4-AC-09 required focused/full regressions recorded.
- FDB-R4-AC-10 exact-current Actions evidence recorded or explicitly pending external ChatGPT audit.
- FDB-R4-AC-11 state/evidence/change manifest reconciled.
- FDB-R4-AC-12 remote parity PASS and worktree clean.

## Closeout states

PASS:
`FOUNDATION-DB-R4 = COMPLETED / PASS`

PASS pending only external Actions inspection:
`FOUNDATION-DB-R4 = COMPLETED / PASS_PENDING_EXTERNAL_CI_AUDIT`

PASS with unrelated new blocker:
`FOUNDATION-DB-R4 = COMPLETED / PASS_WITH_NEW_BLOCKER`

HOLD:
- DB timezone enforcement needs broader source scope;
- timezone replay still proves/indicates an application defect;
- protected database/credential boundary would be crossed.

STOP after R4 closeout for ChatGPT audit.
