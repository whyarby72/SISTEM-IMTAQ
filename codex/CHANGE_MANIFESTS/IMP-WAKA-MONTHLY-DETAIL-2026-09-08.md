# Change Manifest

- **Change ID:** IMP-WAKA-MONTHLY-DETAIL
- **Task ID:** IMP-WAKA-MONTHLY-DETAIL
- **Title:** Ekspor rincian laporan bulanan per santri
- **Date:** 2026-09-08
- **Owner module/workstream:** Academic monthly attendance reporting
- **Business outcome:** Pengguna berwenang dapat mengunduh rincian snapshot historis Juli 2026 per kelas dalam CSV atau PDF.
- **Git branch / commit:** Local workspace; commit not available

## Impact

- **Source-of-truth:** Existing monthly student snapshot; no recalculation from daily transactions.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** NO new privilege; existing Wali/Waka/Super Admin viewer checks are reused.
- **Database migration:** NONE
- **Dependency change:** Added `mpdf/mpdf` for UTF-8 Arabic font embedding and shaping.
- **Backward-compatibility impact:** Existing summary exports and detail page remain available.

## Files

- **Files added:** This impact record, this manifest, `application/web/tests/Feature/Admin/MonthlyStudentAttendanceExportTest.php`, and `application/web/public/images/logo-imtaq.png`.
- **Files modified:** `application/web/routes/web.php`, `application/web/app/Http/Controllers/Admin/MonthlyAttendanceReportController.php`, `application/web/app/Shared/Platform/Reports/MonthlyAttendanceReportExportService.php`, `application/web/resources/views/admin/academic/monthly-reports/detail.blade.php`, `application/web/composer.json`, `application/web/composer.lock`, `codex/WORK_LOG.md`.
- **Files deleted:** NONE

## Verification

- **Automated tests:** PASS — 8 tests, 48 assertions.
- **Additional check:** `php artisan view:cache` PASS.
- **Browser smoke:** July Kelas 3A detail displayed 15 santri and both `Unduh CSV` and `Unduh PDF` links with the expected class-specific URLs.

## Closeout

- **Known limitations/issues:** None known for the requested logo placement; the supplied official logo is embedded in the PDF masthead.
- **Status:** DONE — safe checkpoint
