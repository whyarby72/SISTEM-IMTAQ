# Change Manifest

- **Change ID:** `IMP-ACADEMIC-DISCIPLINE-CODES-2026-09-09`
- **Task ID:** `IMP-ACADEMIC-DISCIPLINE-CODES-2026-09-09`
- **Title:** Catatan Ketertiban dengan pilihan standar
- **Date:** 2026-09-09
- **Owner module/workstream:** Academic / Attendance Operations
- **Change class:** `DATABASE_GLOBAL`
- **Business outcome:** Petugas dapat menandai semua santri rapi lalu hanya mengubah santri yang memiliki pelanggaran ketertiban.
- **Git branch:** Tidak tersedia pada checkout lokal ini.
- **Git commit / release:** Belum ada.

## Impact

- **Affected modules/workstreams:** Pengisian dan pemeriksaan sesi attendance.
- **Source-of-truth entities/services affected:** `StudentSessionGroomingNote.discipline_code`.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** Tidak berubah.
- **Database migration:** YES — `2026_09_09_000002_add_discipline_code_to_student_session_grooming_notes.php`
- **Backward-compatibility impact:** Additive; `note_text` tetap digunakan untuk catatan tambahan.
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files

- **Files added:** Migration, impact, manifest.
- **Files modified:** Model, service, controller, attendance session view, UI test, `codex/WORK_LOG.md`.
- **Files deleted:** None.
- **Protected zones touched:** NONE

## Verification

- **Automated tests run:** `StudentAttendanceUiTest`, `StudentAttendanceDraftServiceTest`, `WaliKelasContextResolverTest`.
- **Regression scope:** Pilihan ketertiban, catatan tambahan, RBAC Waka/Wali, dan workflow attendance.
- **Result:** 11 test lulus dengan 65 assertion; Blade cache lulus; migration syntax check lulus.
- **Staging result:** NOT_APPLICABLE
- **Smoke test:** Migration berhasil dijalankan pada database lokal.

## Deployment

- **Deploy readiness:** STAGING_READY
- **Deployment notes:** Jalankan migration additive dan verifikasi opsi di browser.
- **Rollback / disable procedure:** Forward-fix setelah data opsi baru tersimpan.
- **Database recovery dependency:** Backup database mengikuti prosedur staging/production.

## Closeout

- **Known limitations/issues:** Belum dirangkum pada dashboard atau PDF laporan bulanan.
- **Documentation updated:** Work log.
- **Change Impact Register updated if required:** N/A
- **Status:** DONE
