# Change Manifest

- **Change ID:** IMP-WALI-UI
- **Task ID:** IMP-WALI-UI
- **Title:** Menambahkan akses pengisian kehadiran dari dashboard Wali
- **Date:** 2026-09-08
- **Owner module/workstream:** Academic dashboard + attendance UI
- **Change class:** `MODULE_CONTRACT`
- **Business outcome:** Wali dapat menemukan sesi dan membuka formulir pengisian langsung dari dashboard.
- **Git branch:** Local workspace
- **Git commit / release:** Not available in current workspace

## Impact
- **Affected modules/workstreams:** Academic dashboard and attendance navigation
- **Source-of-truth entities/services affected:** `ClassSession` read query
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** Existing Wali session scope remains authoritative; no new permission
- **Database migration:** NONE
- **Backward-compatibility impact:** Existing dashboards and attendance forms preserved
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files
- **Files added:** This impact/manifest record
- **Files modified:** `app/Domains/Academic/Services/AcademicRoleDashboardService.php`, `resources/views/academic/dashboard.blade.php`, `tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`, `codex/WORK_LOG.md`
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification
- **Automated tests run:** `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/StudentAttendanceUiTest.php`
- **Regression scope:** Wali dashboard session listing and existing attendance UI
- **Result:** PASS — 20 tests, 65 assertions
- **Staging result:** NOT_APPLICABLE — local UAT only
- **Smoke test:** Wali dashboard period July displays 12 sessions with `Isi kehadiran` links

## Deployment
- **Deploy readiness:** STAGING_READY after staging target is available
- **Deployment notes:** No database command required
- **Rollback / disable procedure:** Revert service/view/test changes
- **Database recovery dependency:** NONE

## Closeout
- **Known limitations/issues:** Dashboard lists a maximum of 12 sessions; older sessions remain accessible through the existing route when linked directly.
- **Documentation updated:** Work log and change records
- **Change Impact Register updated if required:** YES
- **Status:** DONE
