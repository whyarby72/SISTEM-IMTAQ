# Change Manifest

- **Change ID:** FIX-ACADEMIC-DASHBOARD-JOINT-SESSIONS-2026-09-11
- **Task ID:** FIX-ACADEMIC-DASHBOARD-JOINT-SESSIONS-2026-09-11
- **Title:** Selaraskan indikator live untuk sesi kelas gabungan
- **Date:** 2026-09-11
- **Owner module/workstream:** Academic Dashboard / Attendance
- **Change class:** MODULE_INTERNAL
- **Business outcome:** Sesi yang ditujukan ke kelas melalui scope gabungan tercermin konsisten pada status periode, tren, dan daftar sesi.
- **Git branch / commit:** Tidak tersedia pada workspace lokal; Local UAT only

## Impact

- **Affected modules/workstreams:** Academic dashboard live session discovery.
- **Source-of-truth entities/services affected:** ClassSession and ClassSessionGroup reads only; no data changed.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** Existing role and class-scope checks retained.
- **Database migration:** NONE
- **Backward-compatibility impact:** Direct `class_id` sessions remain supported; group-scoped sessions are added to discovery.
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
