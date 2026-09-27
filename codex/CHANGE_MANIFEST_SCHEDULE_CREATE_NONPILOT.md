# Change Manifest — Official Class Selection in Schedule Creation

- **Change ID:** FIX-SCHEDULE-CREATE-NONPILOT
- **Date:** 2026-09-09
- **Scope:** Schedule creation read/filter guard
- **Outcome:** Form tambah jadwal menampilkan penugasan kelas resmi non-pilot dan mencegah direct request memakai penugasan pilot.

## Safety

- Database migration/seed/import: NONE
- RBAC: UNCHANGED
- Business rules and historical schedule facts: UNCHANGED
- Existing official create/store workflow: PRESERVED

## Files

- **Modified:** `application/web/app/Http/Controllers/Admin/ScheduleRuleController.php`; `application/web/tests/Feature/Admin/ScheduleRuleAdminTest.php`; `codex/WORK_LOG.md`
- **Added:** This manifest
- **Deleted:** None

## Verification

- `php artisan test tests/Feature/Admin/ScheduleRuleAdminTest.php --compact`: PASS, 8 tests / 31 assertions
- `php artisan view:cache`: PASS
- `php -l app/Http/Controllers/Admin/ScheduleRuleController.php`: PASS
- Browser smoke test: PASS, create form loads without pilot assignment options

**Status:** DONE
