# Change Manifest — WAKA-1C Canonical Attendance Semantic Metrics

Date: 2026-09-12  
Task: WAKA-1C  
Status: COMPLETED

## Purpose

Align class-period attendance semantic metrics with the approved canonical attendance opportunity: `participant_status = EXPECTED AND is_required = true`.

## Files modified

- `application/web/app/Domains/Academic/Services/AttendanceSemanticMetricsService.php`
- `application/web/tests/Feature/Academic/AttendanceSemanticMetricsServiceTest.php`
- `codex/CHANGE_IMPACTS/FIX-WAKA-1C-CANONICAL-ATTENDANCE-METRICS-2026-09-12.md`
- `codex/CHANGE_MANIFESTS/FIX-WAKA-1C-CANONICAL-ATTENDANCE-METRICS-2026-09-12.md`
- `codex/WORK_LOG.md`

## Contract

- Eligible: required `EXPECTED` opportunities under existing session/time and class-scope rules.
- Resolved: eligible opportunities with `VALIDATED` attendance and controlled attendance status.
- Missing: eligible minus resolved.
- Physical presence: `(PRESENT + LATE) / RESOLVED`, rounded to two decimals.
- Unexcused absence: `ABSENT / RESOLVED`, rounded to two decimals.
- Completeness: `RESOLVED / ELIGIBLE`, rounded to two decimals.
- `eligible = 0`: all rates `null`.
- `eligible > 0` and `resolved = 0`: physical/absence `null`, completeness `0`.
- Optional expected participants (`is_required = false`) are excluded from mandatory metrics and missing counts.

## Verification

- Targeted: 5 tests / 31 assertions / 0 failures.
- Academic regression: 218 tests / 843 assertions / 0 failures.
- Targeted Pint: passed.
- PHP syntax lint: passed.

## Non-scope and safety

- Downstream dashboard aggregation, trend, export, UI, routes, and controllers were not changed.
- Database write: NONE.
- Migration/schema change: NONE.
- Seed/import: NONE.
- RBAC/schedule change: NONE.
- Historical data rewrite: NONE.
- Real data unchanged.

## Next

Stop after WAKA-1C. Wait for review before WAKA-1D or downstream semantic alignment.
