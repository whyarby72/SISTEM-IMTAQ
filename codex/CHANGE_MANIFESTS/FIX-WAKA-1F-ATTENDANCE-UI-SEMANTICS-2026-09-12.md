# Change Manifest — WAKA-1F Attendance Dashboard UI Semantics

Date: 2026-09-12  
Task: WAKA-1F  
Status: COMPLETED

## Files modified

- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- `codex/CHANGE_IMPACTS/FIX-WAKA-1F-ATTENDANCE-UI-SEMANTICS-2026-09-12.md`
- `codex/CHANGE_MANIFESTS/FIX-WAKA-1F-ATTENDANCE-UI-SEMANTICS-2026-09-12.md`
- `codex/WORK_LOG.md`

## Presentation contract

- Physical presence: `(PRESENT + LATE) / RESOLVED`, displayed with resolved denominator context.
- Completeness: `RESOLVED / ELIGIBLE`, with resolved/eligible/missing breakdown.
- Missing attendance is never presented as absent or physical `0%`.
- `eligible = 0` has a distinct “no mandatory data” message.
- `eligible > 0` and `resolved = 0` displays “Belum ada data kehadiran tervalidasi”; completeness remains `0%`.
- Class card uses “data kehadiran tervalidasi”, not “sesi selesai”.
- Trend displays physical presence and completeness together with resolved/eligible context.

## Verification

- Targeted dashboard/UI tests: 36 tests / 137 assertions / 0 failures.
- Academic regression: 228 tests / 877 assertions / 0 failures.
- View cache: PASS.
- Pint changed test file: PASS.
- PHP syntax lint: PASS.

## Non-scope

- `AttendanceSemanticMetricsService`: unchanged.
- `AcademicRoleDashboardService`: unchanged.
- `AcademicDashboardExportService`: unchanged.
- Controller/routes, migrations, database, seeders, RBAC, schedule, grade/report logic: unchanged.

## Safety

Database write: NONE.  
Real data: UNCHANGED.

## Next

Stop after WAKA-1F and wait for review before WAKA-1G.
