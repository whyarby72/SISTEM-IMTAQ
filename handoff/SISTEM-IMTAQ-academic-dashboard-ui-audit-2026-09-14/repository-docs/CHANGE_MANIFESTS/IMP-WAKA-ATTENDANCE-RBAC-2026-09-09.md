# Change Manifest

- **Change ID:** `IMP-WAKA-ATTENDANCE-RBAC-2026-09-09`
- **Task ID:** `IMP-WAKA-ATTENDANCE-RBAC-2026-09-09`
- **Title:** Waka Akademik dapat mengisi kehadiran semua kelas
- **Date:** 2026-09-09
- **Owner module/workstream:** Academic / Attendance Authorization
- **Change class:** `SECURITY_GLOBAL`
- **Business outcome:** Tombol “Isi Kehadiran” tidak lagi menghasilkan 403 bagi Waka Akademik; Waka dapat mengisi dan mengesahkan sesi kelas mana pun.
- **Git branch:** Tidak tersedia pada checkout lokal ini.
- **Git commit / release:** Belum ada.

## Impact

- **Affected modules/workstreams:** Attendance entry, attendance finalization, exception monitor.
- **Source-of-truth entities/services affected:** Resolver dan service attendance; data bisnis tidak diubah otomatis.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** Waka global; Wali Kelas tetap terbatas pada assignment kelas efektif.
- **Database migration:** NONE
- **Backward-compatibility impact:** Pemanggil service existing tetap valid karena parameter baru opsional.
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files

- **Files added:** Change impact dan manifest ini.
- **Files modified:** Resolver, controller, dua service attendance, test UI, `codex/WORK_LOG.md`.
- **Files deleted:** None.
- **Protected zones touched:** Authorization boundary — approved by explicit owner request.

## Verification

- **Automated tests run:** `StudentAttendanceUiTest`, `StudentAttendanceDraftServiceTest`, `WaliKelasContextResolverTest`, `AttendanceExceptionUiTest`.
- **Regression scope:** Waka open/save/finalize, Wali class-scope, exception link.
- **Result:** 11 test lulus dengan 63 assertion; regresi UI/exception 9 test lulus dengan 57 assertion; Blade cache lulus.
- **Staging result:** NOT_APPLICABLE
- **Smoke test:** Pending browser verification using an authenticated Waka account.

## Deployment

- **Deploy readiness:** STAGING_READY
- **Deployment notes:** Local UAT completed; staging authorization regression required.
- **Rollback / disable procedure:** Revert global Waka authorization branch in resolver and attendance services.
- **Database recovery dependency:** None.

## Closeout

- **Known limitations/issues:** Waka must still have a linked Staff identity; this preserves audit attribution.
- **Documentation updated:** Work log.
- **Change Impact Register updated if required:** N/A
- **Status:** DONE
