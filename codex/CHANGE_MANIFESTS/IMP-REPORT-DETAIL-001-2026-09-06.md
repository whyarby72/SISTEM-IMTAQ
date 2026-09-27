# Change Manifest

- **Change ID:** `IMP-REPORT-DETAIL-001`
- **Task ID:** `IMP-REPORT-DETAIL-001`
- **Title:** Detail laporan membaca snapshot permanen
- **Date:** 2026-09-06
- **Owner module/workstream:** Academic reporting
- **Change class:** `MODULE_INTERNAL`
- **Business outcome:** pengguna dapat melihat rincian kehadiran bulanan tiap santri dari arsip snapshot permanen.
- **Git branch:** NOT_AVAILABLE
- **Git commit / release:** NOT_CREATED

## Impact
- **Affected modules/workstreams:** Admin/Wali monthly report detail.
- **Source-of-truth entities/services affected:** `monthly_student_attendance_snapshots` read-only.
- **Shared/public contracts changed:** NO
- **RBAC/security/privacy impact:** existing viewer authorization and Wali class scope preserved.
- **Database migration:** NONE — existing snapshot migration was applied in the prior checkpoint.
- **Backward-compatibility impact:** staging fallback remains for unavailable snapshot table.
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Files
- **Files added:** change impact and manifest.
- **Files modified:** `app/Http/Controllers/Admin/MonthlyAttendanceReportController.php`, `NEXT_ACTION.md`, `codex/WORK_LOG.md`.
- **Files deleted:** NONE
- **Protected zones touched:** NONE

## Verification
- **Automated tests run:** targeted import/integrity suite — 28 tests, 89 assertions, PASS.
- **Regression scope:** snapshot validator, import infrastructure, PHP lint, Blade cache.
- **Result:** PASS
- **Staging result:** NOT_APPLICABLE — local environment only.
- **Smoke test:** read-only class totals 20/19/10/15/20; total 84.

## Deployment
- **Deploy readiness:** STAGING_READY
- **Deployment notes:** deploy controller after snapshot table/data migration is present.
- **Rollback / disable procedure:** revert controller to previous read path only through a new reviewed change; no data rollback needed.
- **Database recovery dependency:** existing snapshot migration and import batch backup.

## Closeout
- **Known limitations/issues:** browser smoke across all roles remains a separate regression checkpoint.
- **Documentation updated:** change impact, manifest, work log, next action.
- **Change Impact Register updated if required:** YES
- **Status:** DONE
