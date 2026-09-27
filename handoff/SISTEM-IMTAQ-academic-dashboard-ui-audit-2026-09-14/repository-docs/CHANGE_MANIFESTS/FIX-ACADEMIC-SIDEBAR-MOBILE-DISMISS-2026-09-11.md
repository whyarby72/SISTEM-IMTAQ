# Change Manifest — FIX-ACADEMIC-SIDEBAR-MOBILE-DISMISS

- Date: 2026-09-11
- Scope: Sidebar navigasi Academic/Waka pada mobile
- Owner module: Academic UI
- Change class: `MODULE_INTERNAL`
- Expected write scope: sidebar Blade partial, dashboard feature test, work log, change manifest.

## Impact

- Menambahkan pola penutupan sidebar mobile melalui `Escape` dan klik di luar.
- Tidak mengubah rute, hak akses, data bisnis, database, migration, atau konfigurasi.

## Files

- Modified: `application/web/resources/views/academic/partials/sidebar.blade.php`
- Modified: `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- Modified: `codex/WORK_LOG.md`

## Verification

- `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/TeacherAttendanceServiceTest.php`
  - Passed: 29 tests, 94 assertions.
- `php artisan view:cache`
  - Passed.
- Browser mobile UAT
  - Passed: sidebar terbuka, `Escape` menutupnya, fokus kembali ke tombol, dan `aria-expanded` menjadi false.

## Status

- Status: DONE
- Staging: NOT_APPLICABLE; local UAT only.
