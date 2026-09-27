# Change Manifest

- **Change ID:** `IMP-WAKA-ATTENDANCE-ACTION-2026-09-09`
- **Task ID:** `IMP-WAKA-ATTENDANCE-ACTION-2026-09-09`
- **Title:** Tautan Isi Kehadiran pada temuan data
- **Date:** 2026-09-09
- **Owner module/workstream:** Academic / Waka Akademik UI
- **Change class:** `MODULE_INTERNAL`
- **Business outcome:** Setiap temuan memiliki akses langsung ke halaman pengisian sesi terkait.
- **Git branch:** Tidak tersedia pada checkout lokal ini.
- **Git commit / release:** Belum ada.

## Impact

- **Affected modules/workstreams:** Monitor pengecualian kehadiran.
- **Source-of-truth entities/services affected:** Tidak ada.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** Tidak berubah; route tujuan tetap menerapkan otorisasi yang ada.
- **Database migration:** NONE
- **Backward-compatibility impact:** Tidak ada.
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files

- **Files added:** Change impact dan manifest ini.
- **Files modified:** `application/web/resources/views/academic/attendance/exceptions.blade.php`, `application/web/tests/Feature/Academic/AttendanceExceptionUiTest.php`, `codex/WORK_LOG.md`.
- **Files deleted:** None.
- **Protected zones touched:** NONE

## Verification

- **Automated tests run:** `php artisan test tests/Feature/Academic/AttendanceExceptionUiTest.php --compact`
- **Regression scope:** UI monitor pengecualian dan route tautan sesi.
- **Result:** 3 tests passed, 13 assertions; Blade cache passed.
- **Staging result:** NOT_APPLICABLE
- **Smoke test:** Route named `academic.attendance.show` rendered in each exception row.

## Deployment

- **Deploy readiness:** NOT_READY
- **Deployment notes:** Local UAT only.
- **Rollback / disable procedure:** Revert kolom aksi dan tautan dari view.
- **Database recovery dependency:** None.

## Closeout

- **Known limitations/issues:** Waka yang tidak memiliki konteks Wali Kelas dapat diarahkan ke halaman yang tetap menolak penyimpanan sesuai RBAC.
- **Documentation updated:** Work log.
- **Change Impact Register updated if required:** N/A
- **Status:** DONE
