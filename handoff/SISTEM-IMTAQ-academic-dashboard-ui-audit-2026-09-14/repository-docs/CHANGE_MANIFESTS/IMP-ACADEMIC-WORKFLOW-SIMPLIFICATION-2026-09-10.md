# Change Manifest

- **Change ID:** IMP-ACADEMIC-WORKFLOW-SIMPLIFICATION-2026-09-10
- **Task ID:** IMP-ACADEMIC-WORKFLOW-SIMPLIFICATION-2026-09-10
- **Title:** Simplifikasi persiapan jadwal dan roster
- **Date:** 2026-09-10
- **Owner module/workstream:** Academic Admin / Attendance Operations
- **Change class:** MODULE_INTERNAL
- **Business outcome:** Waka dapat menambah mapel dari menu resmi, menemukan shortcut penyiapan data dari form jadwal, dan membuat roster langsung dari sesi yang terdeteksi tanpa roster.
- **Git branch:** Tidak tersedia pada workspace lokal
- **Git commit / release:** Local UAT only

## Impact
- **Affected modules/workstreams:** Academic subjects, scheduling, attendance exceptions.
- **Source-of-truth entities/services affected:** Subject, ClassSession, SessionStudentParticipant, SessionParticipantSnapshotter.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** Existing Waka/Super Admin checks retained.
- **Database migration:** NONE
- **Backward-compatibility impact:** Existing routes and stored records remain compatible.
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files
- **Files added:** `application/web/app/Http/Controllers/Admin/SubjectController.php`; subject admin views; change records.
- **Files modified:** admin routes, schedule create view, attendance exception view, tests, work log.
- **Files deleted:** None
- **Protected zones touched:** NONE

## Verification
- **Automated tests run:** ScheduleRuleAdminTest 21/78; AttendanceExceptionUiTest 6/28; StudentAttendanceCompletenessCheckerTest 3/9; full Academic suite 164/588.
- **Regression scope:** Academic feature tests.
- **Result:** PASS
- **Staging result:** NOT_APPLICABLE
- **Smoke test:** Blade templates cached successfully.

## Deployment
- **Deploy readiness:** NOT_READY
- **Deployment notes:** Local UAT only; canonical Git repository and staging target remain pending.
- **Rollback / disable procedure:** Revert application changes; no database rollback required.
- **Database recovery dependency:** None

## Closeout
- **Known limitations/issues:** Inline schedule wizard and bulk roster creation for a date range remain candidates for the next checkpoint.
- **Documentation updated:** Work log and change records.
- **Change Impact Register updated if required:** N/A
- **Status:** DONE
