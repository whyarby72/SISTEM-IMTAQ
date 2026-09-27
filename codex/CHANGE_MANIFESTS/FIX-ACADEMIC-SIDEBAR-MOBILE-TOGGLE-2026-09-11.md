# Change Manifest — FIX-ACADEMIC-SIDEBAR-MOBILE-TOGGLE

- Date: 2026-09-11
- Scope: Sidebar navigasi Academic/Waka pada mobile
- Owner module: Academic UI
- Change class: `MODULE_INTERNAL`
- Expected write scope: sidebar Blade partial, dashboard feature test, work log, change manifest.

## Impact

- Menambah kontrol UI lokal untuk membuka/menutup sidebar pada viewport ≤680px.
- Tidak mengubah rute, hak akses, data bisnis, database, migration, atau konfigurasi.

## Files

- Modified: `application/web/resources/views/academic/partials/sidebar.blade.php`
- Modified: `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- Modified: `codex/WORK_LOG.md`

## Verification

- `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/TeacherAttendanceServiceTest.php`
  - Passed: 29 tests, 93 assertions.
- `php artisan view:cache`
  - Passed.
- Browser mobile UAT
  - Passed: tombol awal `Buka navigasi`, berubah menjadi `Tutup navigasi`, `aria-expanded` menjadi true, dan footer tampil.

## Status

- Status: DONE
- Staging: NOT_APPLICABLE; local UAT only.
