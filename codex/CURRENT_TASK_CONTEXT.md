# CURRENT TASK CONTEXT

**Task:** `FOUNDATION-TZ-B1-TODAY-QUERY-BOUNDARY-REMEDIATION`  
**State:** `COMPLETED / PASS`
**Current phase:** `ACADEMIC / TODAY TIME-BOUNDARY REMEDIATION`  
**Branch:** `fix/foundation-tz-b1-today-query-boundary`  
**State-basis:** `62406f14c2fc2b201de8a13cfad107e1d3506114`

## Authority

Project Owner approved:

`OPTION_A — ASIA_JAKARTA_BUSINESS / UTC_TECHNICAL_SESSION / TIMEZONE_AWARE_BOUNDARIES`

R4 stopped correctly at:

`R4_TZ_B_APPLICATION_DEFECT_CANDIDATE`

Exact-current final-head evidence before B1:
- run: `36413692676`
- head: `62406f14c2fc2b201de8a13cfad107e1d3506114`
- result: `1 failed, 15 passed, 505 warnings, 2202 assertions`
- remaining failure:
  `AcademicTodaySessionServiceTest::test_today_boundaries_are_start_inclusive_and_next_day_exclusive`

## Proven defect boundary

The local business day is Asia/Jakarta, but the SQL comparison runs under an
explicit UTC PostgreSQL session.

B1 is authorized to convert local Jakarta start/end-of-day boundaries to copied
UTC instants before binding them to the `timestamptz` query.

Business semantics stay Asia/Jakarta.

## Additional security debt

The R4 masking step does not cover service-container creation because GitHub
initializes services before normal job steps. The literal disposable password
still appears in the docker-create log.

B1 is authorized to remove that credential entirely and use
`POSTGRES_HOST_AUTH_METHOD=trust` only for the GitHub-hosted disposable CI
service.

## Required contract

`codex/TASK_CONTEXTS/FOUNDATION-TZ-B1-TODAY-QUERY-BOUNDARY-REMEDIATION.md`

## Critical boundaries

Authorized source:
- `AcademicTodaySessionService.php` query-boundary normalization only.

Authorized test:
- `AcademicTodaySessionServiceTest.php`.

Authorized workflow:
- disposable PostgreSQL credential elimination only.

Do not modify dashboard service, migrations, schema, DB config, persistent data,
provider/OpenAI, deployment, or unrelated source.

## Exit

If final branch HEAD exact GitHub Actions is green:
- resolve the PostgreSQL full-suite blocker;
- resolve credential-log visibility;
- mark the boundary defect resolved;
- route next product track to `ACADEMIC_WEB_COMPLETION_REVIEW`.

STOP for ChatGPT audit.
