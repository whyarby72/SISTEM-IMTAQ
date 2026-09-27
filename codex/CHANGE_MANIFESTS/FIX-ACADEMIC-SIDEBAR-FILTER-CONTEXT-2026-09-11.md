# Change Manifest — FIX-ACADEMIC-SIDEBAR-FILTER-CONTEXT

- Date: 2026-09-11
- Scope: Konteks filter pada navigasi sidebar Academic/Waka
- Owner module: Academic UI
- Change class: `MODULE_INTERNAL`
- Expected write scope: sidebar Blade partial, dashboard feature test, work log, change manifest.

## Impact

- Navigasi kembali ke dashboard mempertahankan periode yang sedang dipantau.
- Tidak mengubah rute, hak akses, sumber transaksi, database, migration, atau konfigurasi.

## Files

- Modified: `application/web/resources/views/academic/partials/sidebar.blade.php`
- Modified: `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- Modified: `codex/WORK_LOG.md`

## Verification

- `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/TeacherAttendanceServiceTest.php`
  - Passed: 29 tests, 96 assertions.
- `php artisan view:cache`
  - Passed.
- Route output
  - Passed: link dashboard membawa `from`/`to` aktif dalam query string.

## Status

- Status: DONE
- Staging: NOT_APPLICABLE; local UAT only.
