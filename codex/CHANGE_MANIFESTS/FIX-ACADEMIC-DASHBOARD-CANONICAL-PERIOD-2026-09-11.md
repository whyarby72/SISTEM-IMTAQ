# Change Manifest — FIX-ACADEMIC-DASHBOARD-CANONICAL-PERIOD

- Date: 2026-09-11
- Scope: URL dan filter periode Dashboard Waka Akademik
- Owner module: Academic UI/controller
- Change class: `MODULE_INTERNAL`
- Expected write scope: dashboard controller, dashboard feature test, work log, change manifest.

## Impact

- Request dengan `month` dan `from/to` lama diarahkan ke URL canonical berbasis bulan.
- Sumber transaksi kehadiran, permission, database, migration, dan export contract tidak berubah.

## Files

- Modified: `application/web/app/Http/Controllers/Academic/AcademicDashboardController.php`
- Modified: `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- Modified: `codex/WORK_LOG.md`

## Verification

- `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/TeacherAttendanceServiceTest.php`
  - Passed: 29 tests, 98 assertions.
- `php artisan view:cache`
  - Passed.
- Browser UAT
  - Passed: URL `month=2026-07&from=2026-09-01&to=2026-09-30` menjadi `month=2026-07`; form menampilkan 01–31 Juli 2026.

## Status

- Status: DONE
- Staging: NOT_APPLICABLE; local UAT only.
