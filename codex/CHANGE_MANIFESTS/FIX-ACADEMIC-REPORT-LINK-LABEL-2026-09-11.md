# Change Manifest — FIX-ACADEMIC-REPORT-LINK-LABEL

- Date: 2026-09-11
- Scope: Label menu laporan pada sidebar Academic/Waka
- Owner module: Academic UI
- Change class: `MODULE_INTERNAL`
- Expected write scope: sidebar Blade partial, dashboard feature test, work log, change manifest.

## Impact

- Label menu diperjelas menjadi `Laporan Juli 2026` karena rute dan data laporan yang tersedia masih khusus Juli.
- Tidak mengubah rute, sumber laporan, hak akses, database, migration, atau konfigurasi.

## Files

- Modified: `application/web/resources/views/academic/partials/sidebar.blade.php`
- Modified: `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- Modified: `codex/WORK_LOG.md`

## Verification

- `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/TeacherAttendanceServiceTest.php`
  - Passed: 29 tests, 99 assertions.
- `php artisan view:cache`
  - Passed.
- Browser accessibility UAT
  - Passed: sidebar menampilkan label `Laporan Juli 2026`.

## Status

- Status: DONE
- Staging: NOT_APPLICABLE; local UAT only.
