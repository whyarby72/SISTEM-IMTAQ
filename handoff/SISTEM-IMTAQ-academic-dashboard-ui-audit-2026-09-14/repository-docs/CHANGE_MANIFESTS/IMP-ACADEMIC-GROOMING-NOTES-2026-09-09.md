# Change Manifest

- **Change ID:** `IMP-ACADEMIC-GROOMING-NOTES-2026-09-09`
- **Task ID:** `IMP-ACADEMIC-GROOMING-NOTES-2026-09-09`
- **Title:** Catatan kerapian santri per sesi
- **Date:** 2026-09-09
- **Owner module/workstream:** Academic / Attendance Operations
- **Change class:** `DATABASE_GLOBAL`
- **Business outcome:** Petugas dapat mencatat “Tidak berseragam” per santri dan per sesi tanpa memengaruhi persentase kehadiran.
- **Git branch:** Tidak tersedia pada checkout lokal ini.
- **Git commit / release:** Belum ada.

## Impact

- **Affected modules/workstreams:** Pengisian, pemeriksaan, dan audit sesi attendance.
- **Source-of-truth entities/services affected:** `StudentSessionGroomingNote`; `student_attendance` tidak diubah.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** Mengikuti role Wali Kelas/Waka dan mencatat actor user.
- **Database migration:** YES — `2026_09_09_000001_create_student_session_grooming_notes_table.php`
- **Backward-compatibility impact:** Kolom/tabel baru bersifat additive; attendance lama tetap kompatibel.
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files

- **Files added:** Migration, model, service, impact, manifest.
- **Files modified:** Participant model, attendance controller/view, attendance UI test, `codex/WORK_LOG.md`.
- **Files deleted:** None.
- **Protected zones touched:** NONE

## Verification

- **Automated tests run:** `StudentAttendanceUiTest`, `StudentAttendanceDraftServiceTest`, `WaliKelasContextResolverTest`.
- **Regression scope:** Penyimpanan catatan kerapian, hak Waka lintas kelas, hak Wali Kelas, dan workflow attendance.
- **Result:** 11 test lulus dengan 64 assertion; Blade cache lulus; migration syntax check lulus.
- **Staging result:** NOT_APPLICABLE
- **Smoke test:** Pending browser entry test setelah migration dijalankan pada environment target.

## Deployment

- **Deploy readiness:** STAGING_READY
- **Deployment notes:** Jalankan migration additive di staging dan verifikasi catatan tidak muncul pada KPI attendance.
- **Rollback / disable procedure:** Jangan rollback setelah data grooming tersimpan; gunakan forward-fix jika sudah deployed.
- **Database recovery dependency:** Backup database wajib mengikuti prosedur staging/production.

## Closeout

- **Known limitations/issues:** Catatan kerapian belum dirangkum pada dashboard atau PDF laporan bulanan.
- **Documentation updated:** Work log.
- **Change Impact Register updated if required:** N/A
- **Status:** DONE
