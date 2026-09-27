# Change Manifest

- **Change ID:** FIX-ACADEMIC-DASHBOARD-LIVE-ATTENDANCE-2026-09-11
- **Task ID:** FIX-ACADEMIC-DASHBOARD-LIVE-ATTENDANCE-2026-09-11
- **Title:** Gunakan transaksi kehadiran live pada Dashboard Waka
- **Date:** 2026-09-11
- **Owner module/workstream:** Academic Dashboard
- **Change class:** MODULE_INTERNAL
- **Business outcome:** Angka Dashboard Waka mencerminkan data kehadiran sesi yang benar-benar tersimpan dan dapat diedit, termasuk untuk periode Juli.
- **Git branch / commit:** Tidak tersedia pada workspace lokal; Local UAT only

## Impact

- **Affected modules/workstreams:** Academic dashboard attendance metrics, period status, and trend.
- **Source-of-truth entities/services affected:** Live ClassSession and StudentAttendance metrics; monthly snapshot records are not modified.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** None; existing role scope and permission checks retained.
- **Database migration:** NONE
- **Backward-compatibility impact:** Historical monthly reports remain snapshot-backed; only the operational dashboard source changes.
- **Environment/config variables changed:** NONE

## Files

- **Files modified:** `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`; `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`; `codex/WORK_LOG.md`.
- **Files added:** This manifest.
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification

- **Automated tests run:** `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php` — 21 tests / 70 assertions.
- **Smoke test:** `php artisan view:cache` passed.
- **Result:** PASS
- **Staging result:** NOT_APPLICABLE

## Deployment and closeout

- **Deploy readiness:** NOT_READY — local UAT only.
- **Rollback:** Revert the service/test/log changes; no database rollback required.
