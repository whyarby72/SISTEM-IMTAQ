# Change Manifest — Phase 1 Schedule UI

- **Change ID:** IMP-SCHEDULE-UI-PHASE1
- **Task ID:** IMTAQ_SCHEDULE_UI_UPGRADE_v1.2
- **Title:** Shell, branding, filter, dan list view Jadwal Akademik
- **Date:** 2026-09-09
- **Owner module/workstream:** Academic / Schedule UI
- **Change class:** UI-only, read/filter presentation
- **Business outcome:** Waka Akademik dapat menemukan dan membaca jadwal existing dengan konteks kelas, mapel, guru, periode, pola, dan status yang lebih jelas.

## Impact

- **Affected modules/workstreams:** Academic schedule administration, shared academic shell/navigation
- **Source-of-truth entities/services affected:** None; existing ScheduleRule query/read relationships only
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** NO; existing route authorization and Wali Kelas menu scope preserved
- **Database migration:** NONE
- **Backward-compatibility impact:** Existing list, filter, pagination, and detail/edit routes preserved
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files

- **Files added:** None
- **Files modified:** `application/web/app/Http/Controllers/Admin/ScheduleRuleController.php`; `application/web/resources/views/admin/academic/schedules/index.blade.php`; `application/web/resources/views/academic/partials/sidebar.blade.php`; `application/web/resources/views/academic/dashboard.blade.php`; `application/web/tests/Feature/Admin/ScheduleRuleAdminTest.php`; `codex/WORK_LOG.md`
- **Files deleted:** None
- **Protected zones touched:** None

## Verification

- **Automated tests run:** `php artisan test tests/Feature/Academic --compact`
- **Regression scope:** Academic feature suite
- **Result:** PASS — 157 tests, 561 assertions; Blade view cache PASS; PHP lint PASS for changed controller
- **Staging result:** NOT_APPLICABLE
- **Smoke test:** Browser accessibility and screenshot check passed for `/admin/academic/schedules`; sidebar, logo, filters, list, pagination, and Daftar/Mingguan switcher visible.

## Deployment

- **Deploy readiness:** STAGING_READY
- **Deployment notes:** No migration or seed step required.
- **Rollback / disable procedure:** Restore the modified Blade/controller files from the prior release.
- **Database recovery dependency:** None

## Closeout

- **Known limitations/issues:** Weekly grid is intentionally deferred to Phase 2; create/edit form visual harmonization remains outside this checkpoint.
- **Documentation updated:** `codex/WORK_LOG.md`
- **Change Impact Register updated if required:** N/A
- **Status:** DONE
