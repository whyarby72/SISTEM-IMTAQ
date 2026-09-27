# Change Manifest — FIX-ACADEMIC-SIDEBAR-CURRENT-PAGE

- Date: 2026-09-11
- Scope: Sidebar navigasi Academic/Waka
- Owner module: Academic UI
- Change class: `MODULE_INTERNAL`
- Expected write scope: sidebar Blade partial, dashboard feature test, work log, change manifest.

## Impact

- Menambahkan penanda semantik halaman aktif pada seluruh menu sidebar secara kondisional.
- Tidak mengubah rute, hak akses, data bisnis, database, migration, atau konfigurasi.

## Files

- Modified: `application/web/resources/views/academic/partials/sidebar.blade.php`
- Modified: `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- Modified: `codex/WORK_LOG.md`

## Verification

- `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/TeacherAttendanceServiceTest.php`
  - Passed: 29 tests, 95 assertions.
- `php artisan view:cache`
  - Passed.
- Browser DOM UAT
  - Passed: menu Beranda memiliki `aria-current="page"` pada dashboard.

## Status

- Status: DONE
- Staging: NOT_APPLICABLE; local UAT only.
