# Change Manifest — FIX-ACADEMIC-DASHBOARD-DATE-LABELS

- Date: 2026-09-11
- Scope: Dashboard Waka Akademik — konsistensi label hari dan tanggal
- Expected write scope: dashboard Blade view, dashboard feature test, work log, change manifest.

## Changes

- `application/web/resources/views/academic/dashboard.blade.php`
  - Menambahkan peta nama hari dan bulan Indonesia yang eksplisit.
  - Menampilkan hari pada pemisah daftar sesi.
  - Menyeragamkan waktu sesi dan tanggal tren agar tidak mengikuti locale server.
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
  - Menambahkan assertion format hari/tanggal pada daftar sesi dashboard.
- `codex/WORK_LOG.md`
  - Mencatat hasil atomic step.

## Verification

- `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/TeacherAttendanceServiceTest.php`
  - Passed: 29 tests, 90 assertions.
- `php artisan view:cache`
  - Passed.

## Safety

- Tidak ada perubahan database, migration, permission, atau data bisnis.
- Tidak ada destructive command.
