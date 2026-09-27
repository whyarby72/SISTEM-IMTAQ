# Change Manifest

- **Change ID:** FIX-ACADEMIC-DASHBOARD-EXPORT-PERIOD-2026-09-11
- **Task ID:** FIX-ACADEMIC-DASHBOARD-EXPORT-PERIOD-2026-09-11
- **Title:** Selaraskan periode Dashboard Akademik dan ekspor CSV
- **Date:** 2026-09-11
- **Owner module/workstream:** Academic Dashboard
- **Change class:** MODULE_INTERNAL
- **Business outcome:** Waka Akademik menerima ekspor dengan periode yang sama seperti yang dipilih pada Dashboard, termasuk saat URL membawa parameter tanggal lama.
- **Git branch / commit:** Tidak tersedia pada workspace lokal; Local UAT only

## Impact

- **Affected modules/workstreams:** Academic dashboard and dashboard export.
- **Source-of-truth entities/services affected:** None; only controller period resolution.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** Existing auth and export permission checks retained.
- **Database migration:** NONE
- **Backward-compatibility impact:** Existing explicit date-range exports remain supported; month selection now has the same precedence as the dashboard.
- **Environment/config variables changed:** NONE

## Files

- **Files modified:** `application/web/app/Http/Controllers/Academic/AcademicDashboardController.php`; `application/web/resources/views/academic/dashboard.blade.php`; `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`; `codex/WORK_LOG.md`.
- **Files added:** This manifest.
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification

- **Automated tests run:** `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php` — 20 tests / 67 assertions.
- **Smoke test:** `php artisan view:cache` passed.
- **Result:** PASS
- **Staging result:** NOT_APPLICABLE

## Deployment and closeout

- **Deploy readiness:** NOT_READY — local UAT only.
- **Rollback:** Revert the controller/test/log changes; no database rollback required.
