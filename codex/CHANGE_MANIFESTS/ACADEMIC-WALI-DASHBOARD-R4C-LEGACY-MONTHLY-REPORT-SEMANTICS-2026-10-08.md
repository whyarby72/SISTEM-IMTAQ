# Change Manifest — Academic Wali Dashboard R4C

**Task:** `ACADEMIC-WALI-DASHBOARD-R4C-LEGACY-MONTHLY-REPORT-SEMANTICS`
**Date:** 2026-10-08
**Branch:** `feat/super-admin-user-access-preferences`
**Starting HEAD:** `7397b96c64c4992ba2f87096b0cb873654393d47`

## Outcome

`ACADEMIC_WALI_R4C_IMPLEMENTED_PASS` is the requested state, contingent on
the exact final disposable PostgreSQL CI evidence recorded at closeout.

The July 2026 report is explicitly identified as a
`LEGACY_MONTHLY_SNAPSHOT` from `IMTAQ_LEGACY`, period `2026-07`, at
`MONTHLY_AGGREGATE_SNAPSHOT` grain. The UI and CSV/PDF surfaces state that it
is historical, not live, and that live attendance changes do not automatically
change the archive. Archive publication wording is kept separate from live
source certification.

## Files changed

- `application/web/app/Shared/Platform/Reports/MonthlyAttendanceReportContext.php`
- `application/web/app/Http/Controllers/Admin/MonthlyAttendanceReportController.php`
- `application/web/app/Shared/Platform/Reports/MonthlyAttendanceReportExportService.php`
- `application/web/resources/views/academic/dashboard.blade.php`
- `application/web/resources/views/academic/partials/sidebar.blade.php`
- `application/web/resources/views/admin/academic/monthly-reports/index.blade.php`
- `application/web/resources/views/admin/academic/monthly-reports/detail.blade.php`
- `application/web/tests/Feature/Admin/MonthlyStudentAttendanceExportTest.php`
- `application/web/tests/Unit/Shared/Platform/Reports/MonthlyAttendanceReportContextTest.php`

## Safety boundary

No migration, schema, dependency, runtime configuration, route rename,
attendance business semantic, authorization, or certification authority
change. No PILOT/staging/production database access or write. Dashboard live
metrics remain on their existing live sources. Existing route names and
publication action are preserved.

## Verification

- PHP lint: pass for changed PHP files.
- `git diff --check`: pass.
- `php artisan view:cache`: pass.
- focused pure provenance tests: 2 tests, 12 assertions, pass.
- framework export test is guarded by the repository database identity guard
  locally; it must be run on disposable PostgreSQL CI.
- Exact final GitHub Actions evidence: to be recorded after push.

## Next action

Return to ChatGPT/project owner for R4C audit. Do not start R4D or any PILOT,
Human UAT, Grade G3, or AI task automatically.
