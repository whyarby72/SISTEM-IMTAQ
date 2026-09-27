# Change Manifest — Session Cancellation Expansion

- **Change ID:** IMP-ACADEMIC-SESSION-CANCELLATION
- **Date:** 2026-09-09
- **Scope:** Academic session operation and attendance UI
- **Outcome:** Wali Kelas/Waka dapat membatalkan sesi terjadwal, termasuk sesi yang waktunya sudah lewat, bila belum ada data kehadiran.

## Safety

- **Database migration/seed/import:** NONE
- **Authorization:** Existing Wali Kelas session resolver and Waka/Super Admin all-class scope reused.
- **Historical facts:** Session row is preserved; cancellation is recorded as an audited `ScheduleChange`.
- **Attendance integrity:** Any session with an existing student attendance row remains protected from cancellation.

## Files

- **Modified:** `application/web/app/Domains/Academic/Services/CancellationService.php`; `application/web/app/Http/Controllers/Academic/StudentAttendanceController.php`; `application/web/resources/views/academic/attendance/show.blade.php`; `application/web/routes/web.php`; `application/web/tests/Feature/Academic/CancellationServiceTest.php`
- **Added:** This manifest
- **Deleted:** None

## Verification

- `php artisan test tests/Feature/Academic/CancellationServiceTest.php tests/Feature/Academic/StudentAttendanceUiTest.php --compact`: PASS, 11 tests / 60 assertions
- `php artisan view:cache`: PASS
- PHP lint for controller and service: PASS

**Status:** DONE
