# Change Manifest

- **Change ID:** IMP-WALI-UI-TERMS
- **Task ID:** IMP-WALI-UI-TERMS
- **Title:** Konsistensi istilah Santri pada formulir kehadiran
- **Date:** 2026-09-08
- **Owner module/workstream:** Academic attendance UI
- **Change class:** `MODULE_INTERNAL`
- **Business outcome:** Istilah formulir sesuai bahasa yang dipahami kalangan pesantren.
- **Git branch:** Local workspace
- **Git commit / release:** Not available in current workspace

## Impact
- **Affected modules/workstreams:** Academic attendance presentation
- **Source-of-truth entities/services affected:** None
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** None
- **Database migration:** NONE
- **Backward-compatibility impact:** None
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files
- **Files added:** This manifest
- **Files modified:** `resources/views/academic/attendance/show.blade.php`, `tests/Feature/Academic/StudentAttendanceUiTest.php`, `codex/WORK_LOG.md`
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification
- **Automated tests run:** `php artisan test tests/Feature/Academic/StudentAttendanceUiTest.php`
- **Regression scope:** Attendance form rendering
- **Result:** PASS — 2 tests, 9 assertions
- **Staging result:** NOT_APPLICABLE — local UAT only
- **Smoke test:** Browser shows `Kehadiran Santri`, `Total santri`, and `Santri`

## Closeout
- **Known limitations/issues:** None for this copy change
- **Documentation updated:** Work log and manifest
- **Status:** DONE
