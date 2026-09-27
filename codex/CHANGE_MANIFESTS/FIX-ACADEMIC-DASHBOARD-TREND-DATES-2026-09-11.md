# Change Manifest

- **Change ID:** FIX-ACADEMIC-DASHBOARD-TREND-DATES-2026-09-11
- **Task ID:** FIX-ACADEMIC-DASHBOARD-TREND-DATES-2026-09-11
- **Title:** Tampilkan seluruh slot tanggal pada tren kehadiran
- **Date:** 2026-09-11
- **Owner module/workstream:** Academic Dashboard
- **Change class:** MODULE_INTERNAL
- **Business outcome:** Rentang tren 7/14/30 hari terbaca konsisten, termasuk hari tanpa sesi.
- **Git branch / commit:** Tidak tersedia pada workspace lokal; Local UAT only

## Impact

- **Affected modules/workstreams:** Academic dashboard attendance trend.
- **Source-of-truth entities/services affected:** None; read-only trend shaping.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** None.
- **Database migration:** NONE
- **Backward-compatibility impact:** Existing session-day values remain unchanged; missing days now return null metrics for an explicit unavailable state.
- **Environment/config variables changed:** NONE

## Files

- **Files modified:** `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`; `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`; `codex/WORK_LOG.md`.
- **Files added:** This manifest.
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification

- **Automated tests run:** `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/JointClassMetricsTest.php` — 25 tests / 78 assertions.
- **Smoke test:** `php artisan view:cache` passed.
- **Result:** PASS
- **Staging result:** NOT_APPLICABLE

## Deployment and closeout

- **Deploy readiness:** NOT_READY — local UAT only.
- **Rollback:** Revert the service/test/log changes; no database rollback required.
