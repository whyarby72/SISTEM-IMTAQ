# Change Manifest — FIX-ACADEMIC-SIDEBAR-ACCESSIBILITY

- Date: 2026-09-11
- Scope: Sidebar navigasi Academic/Waka
- Owner module: Academic UI
- Change class: `MODULE_INTERNAL`
- Expected write scope: sidebar Blade partial, dashboard feature test, work log, change manifest.

## Impact

- Rute dan otorisasi tidak berubah.
- Tidak ada perubahan database, migration, konfigurasi, atau data bisnis.
- Ikon sidebar tetap visual; nama menu tersedia untuk aksesibilitas dan tooltip.

## Files

- Modified: `application/web/resources/views/academic/partials/sidebar.blade.php`
- Modified: `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- Modified: `codex/WORK_LOG.md`

## Verification

- `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/TeacherAttendanceServiceTest.php`
  - Passed: 29 tests, 92 assertions.
- `php artisan view:cache`
  - Passed.
- Browser accessibility tree
  - Passed: menu names are exposed instead of icon glyphs.

## Status

- Status: DONE
- Staging: NOT_APPLICABLE; local UAT only.
