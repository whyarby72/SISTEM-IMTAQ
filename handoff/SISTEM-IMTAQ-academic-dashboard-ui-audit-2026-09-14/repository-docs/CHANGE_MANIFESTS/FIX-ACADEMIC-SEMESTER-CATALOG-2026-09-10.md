# Change Manifest

- **Change ID:** FIX-ACADEMIC-SEMESTER-CATALOG-2026-09-10
- **Title:** Lengkapi katalog semester resmi 2026/2027
- **Owner module/workstream:** Academic Admin
- **Change class:** MODULE_INTERNAL
- **Business outcome:** Form persiapan jadwal menyediakan Semester 1 dan Semester 2 resmi, sementara semester pilot tetap tidak ditampilkan.
- **Files modified:** `application/web/database/seeders/OfficialAcademicStructure2026Seeder.php`; `codex/WORK_LOG.md`
- **Database change:** Seeder idempoten dijalankan pada database lokal; tidak ada migration dan tidak ada penghapusan data.
- **Verification:** Seeder berhasil; hasil katalog resmi: `S1-2026-2027` (2026-07-01 s.d. 2026-12-31) dan `S2-2026-2027` (2027-01-01 s.d. 2027-06-30).
- **Status:** DONE — Local UAT only
