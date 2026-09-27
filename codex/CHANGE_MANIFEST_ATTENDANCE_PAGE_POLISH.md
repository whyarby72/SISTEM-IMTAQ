# Change Manifest — Attendance Page Polish

- **Change ID:** IMP-ACADEMIC-ATTENDANCE-PAGE-POLISH
- **Date:** 2026-09-09
- **Scope:** Academic student attendance page presentation
- **Outcome:** Halaman Kehadiran Santri memiliki satu header yang jelas, kartu ringkasan yang konsisten, form tindakan yang lebih rapi, tabel yang lebih terbaca pada desktop/mobile, dan dropdown guru pengganti tanpa data pilot.

## Safety

- **Database migration/seed/import:** NONE
- **Business logic:** NONE; existing attendance, substitution, and cancellation routes remain unchanged.
- **Authorization:** NONE; existing authorization remains authoritative.
- **Historical facts:** NONE changed.

## Files

- **Modified:** `application/web/app/Http/Controllers/Academic/StudentAttendanceController.php`; `application/web/resources/views/academic/attendance/show.blade.php`; `application/web/tests/Feature/Academic/StudentAttendanceUiTest.php`; `codex/WORK_LOG.md`
- **Added:** This manifest
- **Deleted:** None

## Verification

- `php artisan view:cache`: PASS
- `php artisan test tests/Feature/Academic/StudentAttendanceUiTest.php --compact`: PASS, 7 tests / 53 assertions

**Status:** DONE
