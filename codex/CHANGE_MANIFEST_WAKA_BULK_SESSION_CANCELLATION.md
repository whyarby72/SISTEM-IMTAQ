# Change Manifest — Waka Bulk Session Cancellation

- **Change ID:** IMP-ACADEMIC-WAKA-BULK-CANCEL
- **Date:** 2026-09-10
- **Scope:** Academic attendance exception control
- **Outcome:** Waka Akademik dapat mempratinjau dan membatalkan sesi PLANNED secara massal berdasarkan kelas dan rentang tanggal.

## Safety

- **Database migration/seed/import:** NONE
- **Authorization:** Waka Akademik dan Super Admin; endpoint tetap dilindungi backend.
- **Historical facts:** Sesi tidak dihapus; setiap pembatalan dicatat sebagai `ScheduleChange` berstatus `APPLIED`.
- **Attendance integrity:** Sesi dengan data kehadiran, status selain `PLANNED`, atau sesi yang berubah saat proses tetap dilewati.
- **Pilot scope:** Kandidat hanya berasal dari kelas/tahun ajaran non-pilot.

## Files

- **Modified:** `application/web/app/Domains/Academic/Services/CancellationService.php`; `application/web/app/Domains/Academic/Services/AttendanceExceptionMonitor.php`; `application/web/app/Http/Controllers/Academic/AttendanceExceptionController.php`; `application/web/resources/views/academic/attendance/exceptions.blade.php`; `application/web/routes/web.php`; `application/web/tests/Feature/Academic/AttendanceExceptionUiTest.php`
- **Added:** This manifest
- **Deleted:** None

## Verification

- `php artisan test tests/Feature/Academic/AttendanceExceptionUiTest.php tests/Feature/Academic/CancellationServiceTest.php --compact`: PASS, 8 tests / 26 assertions
- `php artisan view:cache`: PASS
- PHP lint for affected controller/services: PASS
- Route check confirms GET preview and POST bulk-cancel routes.

**Status:** DONE
