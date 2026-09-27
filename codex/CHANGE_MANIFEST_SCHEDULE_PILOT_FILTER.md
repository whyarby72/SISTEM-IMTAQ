# Change Manifest — Schedule Pilot Filter

- **Change ID:** FIX-ADMIN-SCHEDULE-PILOT-OPTIONS
- **Date:** 2026-09-09
- **Scope:** Admin Academic schedule list and weekly view
- **Outcome:** Data guru pilot/sample tidak lagi tampil pada data operasional maupun filter guru.

## Safety

- **Database migration/seed/import:** NONE
- **Data deletion:** NONE; pilot records remain available as historical data.
- **Authorization:** Unchanged.

## Files

- **Modified:** `application/web/app/Http/Controllers/Admin/ScheduleRuleController.php`; `application/web/tests/Feature/Admin/ScheduleRuleAdminTest.php`; `codex/WORK_LOG.md`
- **Added:** This manifest
- **Deleted:** None

## Verification

- `php artisan test tests/Feature/Admin/ScheduleRuleAdminTest.php --compact`: PASS
- `php artisan view:cache`: PASS

**Status:** DONE
