# Change Manifest

- **Change ID:** FIX-ACADEMIC-DASHBOARD-SNAPSHOT-BOUNDARY-2026-09-11
- **Task ID:** FIX-ACADEMIC-DASHBOARD-SNAPSHOT-BOUNDARY-2026-09-11
- **Title:** Batasi snapshot historis pada Juli penuh
- **Superseded by:** FIX-ACADEMIC-DASHBOARD-LIVE-ATTENDANCE-2026-09-11 (dashboard kini selalu live)
- **Date:** 2026-09-11
- **Owner module/workstream:** Academic Dashboard
- **Change class:** MODULE_INTERNAL
- **Business outcome:** Dashboard tidak mencampur snapshot rekap Juli dengan transaksi live ketika rentang tanggal melewati akhir Juli.
- **Git branch / commit:** Tidak tersedia pada workspace lokal; Local UAT only

## Impact

- **Affected modules/workstreams:** Academic dashboard attendance source selection.
- **Source-of-truth entities/services affected:** MonthlyStudentAttendanceSnapshot selection boundary only; no records changed.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** None; existing authorization retained.
- **Database migration:** NONE
- **Backward-compatibility impact:** This boundary behavior was superseded by the live-dashboard change; historical monthly reports remain snapshot-backed.
- **Environment/config variables changed:** NONE

## Files

- **Files modified:** `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`; `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`; `codex/WORK_LOG.md`.
- **Files added:** This manifest.
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification

- **Automated tests run:** `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php` — 21 tests / 68 assertions.
- **Smoke test:** `php artisan view:cache` passed.
- **Result:** PASS
- **Staging result:** NOT_APPLICABLE

## Deployment and closeout

- **Deploy readiness:** NOT_READY — local UAT only.
- **Rollback:** Revert the service/test/log changes; no database rollback required.
