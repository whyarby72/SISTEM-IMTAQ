# Change Manifest — WAKA-1D Dashboard Aggregation & Trend Semantics

Date: 2026-09-12  
Task: WAKA-1D  
Status: COMPLETED

## Files modified

- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `codex/CHANGE_IMPACTS/FIX-WAKA-1D-DASHBOARD-METRIC-SEMANTICS-2026-09-12.md`
- `codex/CHANGE_MANIFESTS/FIX-WAKA-1D-DASHBOARD-METRIC-SEMANTICS-2026-09-12.md`
- `codex/WORK_LOG.md`

## Formula contract

- Overview physical denominator: aggregate resolved opportunities.
- Overview completeness denominator: aggregate eligible opportunities.
- Grade physical denominator: aggregate resolved opportunities within grade.
- Grade completeness denominator: aggregate eligible opportunities within grade.
- Trend physical denominator: resolved opportunities per day.
- Trend completeness denominator: eligible opportunities per day.
- Unexcused absence semantics: no unexcused absence metric is exposed by this service; no formula added.
- Null/zero semantics: denominator zero yields `null`; eligible positive with resolved zero yields physical `null` and completeness `0`.
- Aggregation method: COUNT-WEIGHTED, never average of class percentages.

## Verification

- Targeted dashboard service tests: 30 tests / 119 assertions / 0 failures.
- Academic regression: 222 tests / 859 assertions / 0 failures.
- Targeted Pint: passed.
- PHP syntax lint: passed.
- Expected downstream failures: NONE.
- Unexpected failures: NONE.

## Safety

- Database write: NONE.
- Real data: UNCHANGED.
- Migration/schema, seed/import, RBAC, schedule, and UI/export: unchanged.

## Next

Stop after WAKA-1D and wait for review before WAKA-1E.
