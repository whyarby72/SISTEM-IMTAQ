# Change Manifest — Session Teacher Substitution UI

- **Change ID:** IMP-ACADEMIC-SESSION-SUBSTITUTION-UI
- **Date:** 2026-09-09
- **Scope:** Waka session operation UI and route
- **Outcome:** Waka Akademik dapat mencatat guru pengganti untuk satu sesi tanpa mengubah jadwal reguler.

## Safety

- Database migration/seed/import: NONE
- RBAC: Existing all-class resolver reused; Waka/Super Admin only for substitution
- Business rules: Existing `SubstitutionService` and conflict validation reused
- Historical facts: Preserved through existing `ScheduleChange` and `SessionTeacherParticipation`

## Files

- **Modified:** `application/web/app/Http/Controllers/Academic/StudentAttendanceController.php`; `application/web/resources/views/academic/attendance/show.blade.php`; `application/web/routes/web.php`; `application/web/tests/Feature/Academic/StudentAttendanceUiTest.php`; `codex/WORK_LOG.md`
- **Added:** This manifest
- **Deleted:** None

## Verification

- `php artisan test tests/Feature/Academic/StudentAttendanceUiTest.php --compact`: PASS, 7 tests / 53 assertions
- `php artisan test tests/Feature/Academic/SubstitutionServiceTest.php --compact`: PASS, 3 tests / 13 assertions
- `php artisan view:cache`: PASS
- `php -l app/Http/Controllers/Academic/StudentAttendanceController.php`: PASS

**Status:** DONE
