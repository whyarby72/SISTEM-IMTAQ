# Change Manifest

- **Change ID:** FIX-ACADEMIC-DASHBOARD-TEACHER-RESPONSIVE-2026-09-11
- **Task ID:** FIX-ACADEMIC-DASHBOARD-TEACHER-RESPONSIVE-2026-09-11
- **Title:** Responsifkan indikator kehadiran guru
- **Date:** 2026-09-11
- **Owner module/workstream:** Academic Dashboard
- **Change class:** MODULE_INTERNAL
- **Business outcome:** Indikator guru tetap terbaca dan tidak meluber pada tablet maupun ponsel.
- **Git branch / commit:** Tidak tersedia pada workspace lokal; Local UAT only

## Impact

- **Affected modules/workstreams:** Academic dashboard responsive presentation.
- **Source-of-truth entities/services affected:** None.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** None.
- **Database migration:** NONE
- **Backward-compatibility impact:** Desktop layout retained; mobile rules only improve wrapping.
- **Environment/config variables changed:** NONE

## Files

- **Files modified:** `application/web/resources/views/academic/dashboard.blade.php`; `codex/WORK_LOG.md`.
- **Files added:** This manifest.
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification

- **Automated tests run:** `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/TeacherAttendanceServiceTest.php` — 29 tests / 89 assertions.
- **Smoke test:** `php artisan view:cache` passed.
- **Result:** PASS
- **Staging result:** NOT_APPLICABLE

## Deployment and closeout

- **Deploy readiness:** NOT_READY — local UAT only.
- **Rollback:** Revert the view/log changes; no database rollback required.
