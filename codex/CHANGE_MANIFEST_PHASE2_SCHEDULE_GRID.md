# Change Manifest — Phase 2 Schedule Grid

- **Change ID:** IMP-SCHEDULE-UI-PHASE2
- **Task ID:** IMTAQ_SCHEDULE_UI_UPGRADE_v1.2
- **Title:** Grid mingguan berbasis posisi waktu aktual
- **Date:** 2026-09-09
- **Owner module/workstream:** Academic / Schedule UI
- **Change class:** UI/read-layer only
- **Business outcome:** Jadwal dapat dipindai berdasarkan hari dan waktu nyata tanpa fixed session slot atau data buatan.

## Impact

- **Affected modules/workstreams:** Academic schedule administration
- **Source-of-truth entities/services affected:** None; existing ScheduleRule read relationships only
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** NO
- **Database migration:** NONE
- **Backward-compatibility impact:** List view and existing detail/edit routes preserved
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files

- **Files added:** None
- **Files modified:** `application/web/app/Http/Controllers/Admin/ScheduleRuleController.php`; `application/web/resources/views/admin/academic/schedules/index.blade.php`; `application/web/tests/Feature/Admin/ScheduleRuleAdminTest.php`; `codex/WORK_LOG.md`
- **Files deleted:** None
- **Protected zones touched:** None

## Verification

- **Automated tests run:** `php artisan test tests/Feature/Admin/ScheduleRuleAdminTest.php --compact`; `php artisan test tests/Feature/Academic --compact`
- **Regression scope:** Schedule admin plus Academic feature suite
- **Result:** PASS — 7 tests/27 assertions and 157 tests/561 assertions; Blade cache and PHP lint PASS
- **Staging result:** NOT_APPLICABLE
- **Smoke test:** PASS — weekly grid shows locked day order, actual time labels, readable cards, and Friday notice.

## Deployment

- **Deploy readiness:** STAGING_READY
- **Deployment notes:** No migration or seed step required.
- **Rollback / disable procedure:** Restore the modified controller, view, and test files from the previous Phase 1 checkpoint.
- **Database recovery dependency:** None

## Closeout

- **Known limitations/issues:** Selected-week operational occurrences and utility panels remain for later phases; mobile keeps the grid horizontally scrollable rather than forcing six columns into unreadable cards.
- **Documentation updated:** `codex/WORK_LOG.md`
- **Change Impact Register updated if required:** N/A
- **Status:** DONE
