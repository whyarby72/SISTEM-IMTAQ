# Change Manifest

- **Change ID:** FIX-ACADEMIC-TIMEZONE-2026-09-11
- **Task ID:** FIX-ACADEMIC-TIMEZONE-2026-09-11
- **Title:** Tetapkan timezone operasional Academic ke Asia/Jakarta
- **Date:** 2026-09-11
- **Owner module/workstream:** Academic scheduling and attendance dashboard
- **Change class:** MODULE_INTERNAL
- **Business outcome:** Tanggal/hari sesi dan klasifikasi live mengikuti waktu operasional Indonesia.
- **Git branch / commit:** Tidak tersedia pada workspace lokal; Local UAT only

## Impact

- **Affected modules/workstreams:** Application date handling used by Academic workflows.
- **Source-of-truth entities/services affected:** None; no data is rewritten.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** None.
- **Database migration:** NONE
- **Backward-compatibility impact:** `APP_TIMEZONE` may override the default; deployments should set their intended timezone explicitly.
- **Environment/config variables changed:** `.env.example` documents `APP_TIMEZONE=Asia/Jakarta`; local `.env` was not modified.

## Files

- **Files modified:** `application/web/config/app.php`; `application/web/.env.example`; `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`; `codex/WORK_LOG.md`.
- **Files added:** This manifest.
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification

- **Automated tests run:** `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php` — 22 tests / 71 assertions.
- **Smoke test:** `php artisan view:cache` passed.
- **Result:** PASS
- **Staging result:** NOT_APPLICABLE

## Deployment and closeout

- **Deploy readiness:** NOT_READY — local UAT only.
- **Rollback:** Restore the previous timezone config; no database rollback required.
