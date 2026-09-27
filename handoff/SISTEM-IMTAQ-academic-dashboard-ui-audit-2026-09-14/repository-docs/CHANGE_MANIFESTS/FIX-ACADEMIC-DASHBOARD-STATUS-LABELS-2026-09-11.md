# Change Manifest

- **Change ID:** FIX-ACADEMIC-DASHBOARD-STATUS-LABELS-2026-09-11
- **Task ID:** FIX-ACADEMIC-DASHBOARD-STATUS-LABELS-2026-09-11
- **Title:** Perjelas label indikator kehadiran live
- **Date:** 2026-09-11
- **Owner module/workstream:** Academic Dashboard
- **Change class:** MODULE_INTERNAL
- **Business outcome:** Waka dapat membedakan tidak adanya sesi, sesi belum jatuh tempo, dan data kehadiran yang belum disahkan.
- **Git branch / commit:** Tidak tersedia pada workspace lokal; Local UAT only

## Impact

- **Affected modules/workstreams:** Academic dashboard live attendance presentation.
- **Source-of-truth entities/services affected:** None; presentation labels only.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** None.
- **Database migration:** NONE
- **Backward-compatibility impact:** Existing session workflow labels remain unchanged.
- **Environment/config variables changed:** NONE

## Files

- **Files modified:** `application/web/resources/views/academic/dashboard.blade.php`; `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`; `codex/WORK_LOG.md`.
- **Files added:** This manifest.
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification

- **Automated tests run:** `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/JointClassMetricsTest.php` — 25 tests / 79 assertions.
- **Smoke test:** `php artisan view:cache` passed.
- **Result:** PASS
- **Staging result:** NOT_APPLICABLE

## Deployment and closeout

- **Deploy readiness:** NOT_READY — local UAT only.
- **Rollback:** Revert the view/test/log changes; no database rollback required.
