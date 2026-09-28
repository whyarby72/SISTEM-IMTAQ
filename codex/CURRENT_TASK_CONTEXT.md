# CURRENT TASK CONTEXT

**Task:** `FOUNDATION-DB-R4-POSTGRES-REGRESSION-REMEDIATION`  
**State:** `READY_FOR_EXECUTION`  
**Current phase:** `CI / POSTGRESQL REGRESSION REMEDIATION`  
**Branch:** `chore/foundation-db-r4-postgres-regression-remediation`  
**State-basis:** `4d54283f3c8aec7cb04f203837fed746c2b71104`

## Owner authority

Project Owner approved:

`OPTION_A — ASIA_JAKARTA_BUSINESS / UTC_TECHNICAL_SESSION / TIMEZONE_AWARE_BOUNDARIES`

Decision artifact:
`codex/DECISIONS/FOUNDATION-TZ-D1-TIMEZONE-STORAGE-AUTHORITY-2026-09-28.md`

## Active blocker

`FOUNDATION_POSTGRESQL_FULL_SUITE_REGRESSION`

Exact-current evidence:
- run `36402569265`
- 16 failed / 15 passed / 490 warnings / 2171 assertions
- 7 TEST_FIXTURE
- 4 TEST_ASSERTION
- 5 prior TZ-C

## Authorized R4 work

- fix the 11 test-only PostgreSQL regressions;
- enforce PostgreSQL technical session timezone UTC via supported DB config;
- convert the five prior TZ-C fixtures/clocks to explicit Asia/Jakarta-aware values;
- replay those five BEFORE any business-source change;
- remediate CI disposable credential log visibility;
- run focused/full PostgreSQL regression;
- record exact Actions evidence or external-audit-pending status.

## Critical stop rule

Do not modify:
- AcademicTodaySessionService.php
- AcademicRoleDashboardService.php
- any other business source
- migrations/schema/constraints
- persistent pilot data

If any of the five timezone tests still fail after aware fixtures + UTC session enforcement:
STOP as `R4_TZ_B_APPLICATION_DEFECT_CANDIDATE` or residual TZ-C.
Do not patch services in R4.

## Required contract

`codex/TASK_CONTEXTS/FOUNDATION-DB-R4-POSTGRES-REGRESSION-REMEDIATION.md`

## Exit

If exact CI green:
- resolve PostgreSQL full-suite blocker;
- resolve credential-log visibility;
- classify prior TZ-C as TZ-A if replay passed without business-source change;
- next product track = `ACADEMIC_WEB_COMPLETION_REVIEW`.

STOP for ChatGPT audit.
