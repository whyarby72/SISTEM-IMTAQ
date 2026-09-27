# Change Manifest

- **Change ID:** IMP-WALI-UI-REPEAT
- **Task ID:** IMP-WALI-UI-REPEAT
- **Title:** Memperjelas daftar sesi pengisian kehadiran Wali Kelas
- **Date:** 2026-09-08
- **Owner module/workstream:** Academic dashboard
- **Change class:** `MODULE_INTERNAL`
- **Business outcome:** Sesi yang memang berbeda dapat dibedakan dengan cepat oleh Wali Kelas.
- **Git branch:** Local workspace
- **Git commit / release:** Not available in current workspace

## Impact
- **Affected modules/workstreams:** Wali Kelas dashboard presentation
- **Source-of-truth entities/services affected:** Existing `ClassSession` and attendance relations, read-only
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** None
- **Database migration:** NONE
- **Backward-compatibility impact:** None
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files
- **Files added:** This impact record and manifest
- **Files modified:** `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`, `application/web/resources/views/academic/dashboard.blade.php`, `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`, `codex/WORK_LOG.md`
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification
- **Automated tests run:** `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/StudentAttendanceUiTest.php`
- **Regression scope:** Dashboard session list, attendance UI rendering
- **Result:** PASS — 20 tests, 71 assertions
- **Additional check:** `php artisan view:cache` PASS; browser Wali dashboard smoke PASS, including date group headings, status filter, and per-status counts

## Closeout
- **Known limitations/issues:** Only the first 12 sessions in the selected period are shown, as before.
- **Documentation updated:** Work log and change records
- **Status:** DONE
