# Change Manifest

- **Change ID:** FIX-ACADEMIC-DASHBOARD-DUE-DENOMINATOR-2026-09-11
- **Task ID:** FIX-ACADEMIC-DASHBOARD-DUE-DENOMINATOR-2026-09-11
- **Title:** Selaraskan denominator indikator live dengan sesi yang sudah selesai
- **Date:** 2026-09-11
- **Owner module/workstream:** Academic Dashboard
- **Change class:** MODULE_INTERNAL
- **Business outcome:** Persentase kelengkapan dan tren tidak turun hanya karena sesi masih berlangsung atau belum dimulai.
- **Git branch / commit:** Tidak tersedia pada workspace lokal; Local UAT only

## Impact

- **Affected modules/workstreams:** Academic dashboard live attendance metrics and trend.
- **Source-of-truth entities/services affected:** Live ClassSession and StudentAttendance read metrics; no business data changed.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** None; role scope retained.
- **Database migration:** NONE
- **Backward-compatibility impact:** Historical periods are unchanged because their sessions have ended; future/current periods now use a denominator consistent with due-session status.
- **Environment/config variables changed:** NONE

## Files

- **Files modified:** `application/web/app/Domains/Academic/Services/AttendanceSemanticMetricsService.php`; `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`; `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`; `codex/WORK_LOG.md`.
- **Files added:** This manifest.
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification

- **Automated tests run:** `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/AttendanceSemanticMetricsServiceTest.php` — 24 tests / 81 assertions.
- **Smoke test:** `php artisan view:cache` passed.
- **Result:** PASS
- **Staging result:** NOT_APPLICABLE

## Deployment and closeout

- **Deploy readiness:** NOT_READY — local UAT only.
- **Rollback:** Revert the service/test/log changes; no database rollback required.
