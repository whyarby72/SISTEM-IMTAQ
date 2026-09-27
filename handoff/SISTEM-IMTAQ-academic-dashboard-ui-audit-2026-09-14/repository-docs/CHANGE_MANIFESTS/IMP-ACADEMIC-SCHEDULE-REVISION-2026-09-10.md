# Change Manifest

- **Change ID:** IMP-ACADEMIC-SCHEDULE-REVISION-2026-09-10
- **Task ID:** IMP-ACADEMIC-SCHEDULE-REVISION-2026-09-10
- **Title:** Revisi aturan jadwal mulai tanggal efektif
- **Date:** 2026-09-10
- **Owner module/workstream:** Academic scheduling
- **Change class:** MODULE_CONTRACT
- **Business outcome:** Admin dapat membuat revisi jadwal mulai 28 Juli 2026, memilih guru serta mata pelajaran baru, dan menghapus aturan yang belum memiliki sesi tanpa menghapus histori.
- **Git branch:** Not available in current local release workflow
- **Git commit / release:** Not available

## Impact

- **Affected modules/workstreams:** Admin schedules, session generation, attendance history
- **Source-of-truth entities/services affected:** ScheduleRule, ClassSession, ScheduleChange
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** Existing Waka Akademik/Super Admin authorization retained
- **Database migration:** NONE
- **Backward-compatibility impact:** Existing direct edit behavior for rules without sessions remains unchanged.
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files

- **Files added:** `application/web/app/Domains/Academic/Services/ScheduleRuleRevisionService.php`, `application/web/app/Domains/Academic/Services/ScheduleRuleArchiveService.php`, this manifest and impact record
- **Files modified:** `application/web/app/Http/Controllers/Admin/ScheduleRuleController.php`, `application/web/app/Domains/Academic/Services/ScheduleRuleRevisionService.php`, `application/web/resources/views/admin/academic/schedules/edit.blade.php`, `application/web/tests/Feature/Admin/ScheduleRuleAdminTest.php`, `codex/WORK_LOG.md`
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification

- **Automated tests run:** ScheduleRuleAdminTest: 15/15; Academic suite: 161/161; 579 assertions
- **Regression scope:** Academic domain and schedule admin workflow
- **Result:** PASS
- **Staging result:** NOT_APPLICABLE — local UAT only
- **Smoke test:** Blade templates cached successfully

## Deployment

- **Deploy readiness:** STAGING_READY
- **Deployment notes:** Use Edit Jadwal, choose “Berlaku mulai revisi” = 28/07/2026, set the new schedule values and reason, then save.
- **Rollback / disable procedure:** Do not delete historical sessions; apply a new corrective revision if business values need correction.
- **Database recovery dependency:** NONE for code-only local UAT; backup required before shared deployment.

## Closeout

- **Known limitations/issues:** A session with existing attendance remains tied to the original rule/date; the new rule skips occupied historical times to prevent duplicate facts. Rules with sessions cannot be deleted; archive marks the rule `ARCHIVED`, cancels only empty PLANNED/CONFIRMED sessions, and preserves sessions with attendance.
- **Documentation updated:** YES
- **Change Impact Register updated if required:** YES
- **Status:** DONE
