# Change Manifest

- **Change ID:** FIX-ACADEMIC-DASHBOARD-TEACHER-ATTENDANCE-2026-09-11
- **Task ID:** FIX-ACADEMIC-DASHBOARD-TEACHER-ATTENDANCE-2026-09-11
- **Title:** Tampilkan indikator kehadiran guru live
- **Date:** 2026-09-11
- **Owner module/workstream:** Academic Dashboard / Teacher Attendance
- **Change class:** MODULE_INTERNAL
- **Business outcome:** Dashboard Waka menampilkan status kehadiran guru berdasarkan transaksi yang tersedia.
- **Git branch / commit:** Tidak tersedia pada workspace lokal; Local UAT only

## Impact

- **Affected modules/workstreams:** Academic dashboard teacher attendance status.
- **Source-of-truth entities/services affected:** SessionTeacherParticipation read only; no attendance records changed.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** Existing dashboard scope retained.
- **Database migration:** NONE
- **Backward-compatibility impact:** Sessions without teacher participation remain explicitly unavailable rather than fabricated.
- **Environment/config variables changed:** NONE

## Files

- **Files modified:** `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`; `application/web/resources/views/academic/dashboard.blade.php`; `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`; `codex/WORK_LOG.md`.
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
- **Rollback:** Revert the service/view/test/log changes; no database rollback required.
