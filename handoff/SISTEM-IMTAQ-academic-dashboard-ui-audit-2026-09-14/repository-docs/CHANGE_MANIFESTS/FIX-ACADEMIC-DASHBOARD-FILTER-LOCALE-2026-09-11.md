# Change Manifest — FIX-ACADEMIC-DASHBOARD-FILTER-LOCALE

- Date: 2026-09-11
- Scope: Dashboard Waka Akademik — filter dan ringkasan periode
- Expected write scope: dashboard Blade view, dashboard feature test, work log, change manifest.

## Changes

- `application/web/resources/views/academic/dashboard.blade.php`
  - Menambahkan pemetaan nama bulan Indonesia untuk label filter dan periode.
  - Menghilangkan ketergantungan `translatedFormat` pada locale server.
  - Membuat judul ringkasan snapshot mengikuti bulan periode aktif.
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
  - Memperbarui assertion periode bulan agar memverifikasi output Bahasa Indonesia.
- `codex/WORK_LOG.md`
  - Mencatat hasil atomic step.

## UAT audit

- Pemilihan bulan tetap mengalahkan `from`/`to` yang stale.
- Label periode dan opsi bulan tidak bergantung pada locale server.
- Tautan rentang tren tetap membawa konteks filter aktif.
- Ekspor tetap menggunakan periode bulan yang dipilih.
- UAT responsive pada default, tablet 1024×768, dan mobile 390×844 tidak menemukan horizontal overflow.

## Verification

- `php artisan test tests/Feature/Academic/AcademicRoleDashboardServiceTest.php tests/Feature/Academic/TeacherAttendanceServiceTest.php`
  - Passed: 29 tests, 91 assertions.
- `php artisan view:cache`
  - Passed.

## Safety

- Tidak ada perubahan database, migration, permission, atau data bisnis.
- Tidak ada destructive command.
