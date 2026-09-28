# FOUNDATION-TZ-B1 — Academic Today Query-Boundary Remediation

**Mode:** EXISTING_REPO_ADOPTION  
**Task type:** NARROW_ACADEMIC_APPLICATION_DEFECT_REMEDIATION  
**Owner:** CODEX  
**State:** READY_FOR_EXECUTION  
**Branch:** fix/foundation-tz-b1-today-query-boundary  
**State-basis:** 62406f14c2fc2b201de8a13cfad107e1d3506114

## Authority

The Project Owner approved:

`OPTION_A — ASIA_JAKARTA_BUSINESS / UTC_TECHNICAL_SESSION / TIMEZONE_AWARE_BOUNDARIES`

Canonical decision:
`codex/DECISIONS/FOUNDATION-TZ-D1-TIMEZONE-STORAGE-AUTHORITY-2026-09-28.md`

R4 stopped correctly at:
`R4_TZ_B_APPLICATION_DEFECT_CANDIDATE`

Exact-current final-head evidence:

- GitHub Actions run: `36413692676`
- head: `62406f14c2fc2b201de8a13cfad107e1d3506114`
- PostgreSQL 18.6 readiness: PASS
- PostgreSQL technical session timezone UTC: PASS
- test DB identity guard: PASS
- migration-from-zero: PASS
- schema/extension checks: PASS
- foundation suite: `1 failed, 15 passed, 505 warnings, 2202 assertions`
- only failure:
  `AcademicTodaySessionServiceTest::test_today_boundaries_are_start_inclusive_and_next_day_exclusive`

Observed mismatch:

- expected local business instant after presentation:
  `2026-09-12 00:00:00 Asia/Jakarta`
- actual returned:
  `2026-09-13 00:00:00 Asia/Jakarta`

## Root-cause authority

The ratified Option A contract requires:

`local Asia/Jakarta calendar boundary → absolute instant → timestamptz query`

The current service correctly constructs local Jakarta day boundaries but sends
those timezone-aware Carbon values directly to the SQL comparison:

`planned_start_at >= startOfToday`
`planned_start_at < startOfNextDay`

With the PostgreSQL session explicitly UTC, the query binding path serializes
the timestamp value without preserving the original offset. Therefore a local
Jakarta midnight must be normalized to its UTC instant before SQL binding.

For the failing example:

- `2026-09-12 00:00:00+07:00`
  = `2026-09-11 17:00:00Z`
- `2026-09-13 00:00:00+07:00`
  = `2026-09-12 17:00:00Z`

The intended SQL window is therefore:

`[2026-09-11 17:00:00Z, 2026-09-12 17:00:00Z)`

B1 is authorized to correct that query-boundary binding only.

## Goal

1. Correct Academic Today query boundaries so local-day selection obeys the
   approved Asia/Jakarta business-time contract while PostgreSQL operates in UTC.
2. Preserve start-inclusive / next-day-exclusive semantics.
3. Preserve all existing operational-state semantics.
4. Eliminate the CI ephemeral credential-log debt by removing the credential
   entirely from the disposable PostgreSQL service rather than masking it after
   service creation.
5. Run exact-current PostgreSQL CI and close the foundation blocker only if the
   final branch HEAD is green.

## Approved write scope

### Business source — narrowly authorized
1. `application/web/app/Domains/Academic/Services/AcademicTodaySessionService.php`

Only the local-day query boundary normalization is authorized.

Expected minimal implementation shape:

- keep `$startOfToday` and `$startOfNextDay` as Asia/Jakarta business
  boundaries for local semantics/response metadata;
- create copied UTC instants for persistence/query binding, e.g.
  `$queryStart = $startOfToday->copy()->utc()`;
  `$queryEnd = $startOfNextDay->copy()->utc()`;
- bind `$queryStart` and `$queryEnd` in the `planned_start_at` predicates.

Do not change:
- authorization;
- raw session-status precedence;
- operational state classification;
- subject/class shaping;
- active/cancelled/rescheduled semantics.

### Test
2. `application/web/tests/Feature/Academic/AcademicTodaySessionServiceTest.php`

Required regression coverage:
- local midnight start is included;
- next local midnight is excluded;
- returned selected session presents as the expected Asia/Jakarta local time;
- query behavior remains correct with PostgreSQL session UTC;
- preserve existing Today tests.

A new test file is NOT preferred if the canonical test already expresses the
boundary contract.

### CI security debt
3. `.github/workflows/application-foundation.yml`

Resolve:
`CI_EPHEMERAL_CREDENTIAL_LOG_VISIBILITY`

The current masking step is insufficient because GitHub creates service
containers before job steps, so the literal service password appears in the
`docker create` command.

Authorized remediation:

- remove `POSTGRES_PASSWORD` from the disposable service;
- remove the later masking/password-injection step;
- use `POSTGRES_HOST_AUTH_METHOD=trust` for this GitHub-hosted, disposable,
  isolated CI database only;
- leave application `DB_PASSWORD` empty/unset for the CI lane;
- retain exact database/user/host identity guard;
- retain `runs-on: ubuntu-latest`;
- explicitly document that this trust-auth configuration is CI-only and must
  not be copied to self-hosted, pilot, staging, or production environments.

Do not introduce a repository/environment production secret merely to satisfy
this disposable CI service.

### Evidence/state
4. `codex/CHANGE_MANIFESTS/FOUNDATION-TZ-B1-2026-09-29.md`
5. `PROJECT_STATE.json`
6. `TEST_MATRIX.csv`
7. `EVIDENCE_INDEX.json`
8. `codex/CURRENT_TASK_CONTEXT.md`
9. `NEXT_ACTION.md`

No other file is authorized.

## Protected zones

Do not modify:

- `AcademicRoleDashboardService.php`;
- any other Academic business service;
- database migrations;
- database schema/constraints;
- `config/database.php` (R4 UTC session setting remains authoritative);
- `phpunit.xml`;
- `tests/TestCase.php`;
- test DB identity guard;
- Composer/PHP/dependencies;
- provider/OpenAI state;
- pilot/staging/production database or credentials;
- historical timestamp data;
- deployment/hosting;
- main branch.

## Phase 0 — exact baseline

Before editing:

- verify branch and clean worktree;
- verify HEAD/base lineage from
  `62406f14c2fc2b201de8a13cfad107e1d3506114`;
- verify exact-current run `36413692676`;
- verify the failing line/mismatch remains the sole failure;
- verify R4 did not modify AcademicTodaySessionService.

## Phase 1 — focused source remediation

Implement Minimum Necessary Change.

Required semantic invariant:

`Business day = Asia/Jakarta`

`Persistence/query instant = UTC-normalized absolute instant`

Do not change expected business outcomes.

## Phase 2 — focused regression

Run the Today service test suite against safe disposable PostgreSQL.

At minimum prove:

- boundary test PASS;
- existing Today operational-state tests PASS;
- app timezone remains Asia/Jakarta;
- DB session timezone remains UTC.

If a different Today failure appears because the minimal boundary fix exposes
another source defect:

STOP = `B1_PASS_WITH_NEW_TODAY_BLOCKER`

Do not broaden source changes without evidence.

## Phase 3 — CI credential elimination

Change the service to CI-only trust authentication.

Verify normal GitHub Actions logs contain:

- no `POSTGRES_PASSWORD=<literal>`;
- no disposable password literal;
- no `DB_PASSWORD` credential value.

The absence of a credential is the resolution; do not rely on masking after
service initialization.

Security boundary:

This is valid only for the GitHub-hosted disposable test service protected by:
- ephemeral hosted runner;
- exact test database identity guard;
- no pilot/staging/production host;
- no persistent business data.

## Phase 4 — full verification

Run at minimum:

- repository structure check;
- PHP lint for changed PHP;
- focused Today suite;
- test DB identity guard;
- PostgreSQL UTC session assertion;
- migration-from-zero;
- schema/extension assertions;
- focused Academic regression;
- full current PHPUnit/foundation suite;
- `./scripts/verify-foundation.sh`;
- Pint/diff checks required by repository protocol;
- exact GitHub Actions on implementation commit.

Warnings may remain if non-blocking/pre-existing; record actual count.

## Exact-current closeout rule

The final branch HEAD, not only the intermediate implementation commit, must
have exact Actions evidence.

If final HEAD is green:

- `FOUNDATION_POSTGRESQL_FULL_SUITE_REGRESSION = RESOLVED`
- `CI_EPHEMERAL_CREDENTIAL_LOG_VISIBILITY = RESOLVED`
- remaining prior TZ-C boundary failure = `TZ-B_RESOLVED`
- next product track = `ACADEMIC_WEB_COMPLETION_REVIEW`

If implementation commit is green but evidence-only final commit retriggers CI,
verify the final run before claiming exact-current green.

## State reconciliation

B1 closeout must update exact-current evidence from the stale R4 values.

Current known exact-current baseline before B1:
- run: `36413692676`
- head: `62406f14c2fc2b201de8a13cfad107e1d3506114`

Also remove the stale evidence debt claiming the disposable credential is still
visible only after exact logs prove the new no-credential service behavior.

Preserve:
- canonical queue gate `SOC-MD-06`;
- public Academic AI = OFF.

## Acceptance criteria

- FTZ-B1-AC-01 exact R4 final HEAD/run reconciled.
- FTZ-B1-AC-02 source change limited to Today query boundary normalization.
- FTZ-B1-AC-03 Jakarta local-day boundaries converted to UTC query instants.
- FTZ-B1-AC-04 start-inclusive/next-day-exclusive test passes.
- FTZ-B1-AC-05 existing Today behavior remains green.
- FTZ-B1-AC-06 CI service contains no PostgreSQL password credential.
- FTZ-B1-AC-07 identity guard/UTC session/migration/schema checks remain green.
- FTZ-B1-AC-08 no migration/schema/persistent-data/provider/deployment mutation.
- FTZ-B1-AC-09 full foundation suite exact-current result recorded.
- FTZ-B1-AC-10 final branch HEAD exact Actions result recorded.
- FTZ-B1-AC-11 state/evidence/manifest reconciled.
- FTZ-B1-AC-12 remote parity PASS and worktree clean.

## Closeout states

PASS:
`FOUNDATION-TZ-B1 = COMPLETED / PASS`

PASS_WITH_NEW_BLOCKER:
minimal boundary remediation succeeds but a different independent failure
remains.

HOLD:
the proposed UTC-boundary normalization does not match observed runtime
behavior, or safe CI credential elimination would require crossing a protected
environment boundary.

Commit/push implementation and evidence, then STOP for ChatGPT audit.
