# Change Manifest — Academic Wali Dashboard R4A2

**Project:** SISTEM-IMTAQ  
**Task:** `ACADEMIC-WALI-DASHBOARD-R4A2-POINT-IN-TIME-WALI-AUTHORIZATION`  
**Decision:** `ACADEMIC_WALI_R4A2_IMPLEMENTED_PASS`

## Repository and safety boundary

- Repository: `whyarby72/SISTEM-IMTAQ`
- Branch: `feat/super-admin-user-access-preferences`
- Starting HEAD: `72949b259db742031e91d898a40df2dd7fc1b67a`
- Starting remote HEAD: same SHA
- Tested executable HEAD: `4e443dcce84efb2b6e8d1a9990fe33736f4efee8`
- Final governance HEAD: `558adda51ca7bcf013e97d281688e602f8134e02`
- Exact GitHub Actions: run `37323408359` = `SUCCESS` on tested executable HEAD
- Application source changed: `YES`, Academic dashboard read-scope only
- Migration: `NONE`
- Schema: `NONE`
- Dependency: `NONE`
- Runtime configuration: `NONE`
- PILOT access/write: `NONE/NONE`
- Public Academic AI: `OFF`
- Grade G3: `DEFERRED_BY_OWNER_PRIORITY`
- Human UAT: `DEFERRED`

The pre-existing untracked `codex/AUDITS/` directory was preserved and is not
part of this change.

## Current-role and data-entitlement contract

`AcademicRoleDashboardService` now resolves the dashboard role at the current
Asia/Jakarta business date. Selected historical/future `from`, `to`, `month`,
or semester values cannot resurrect an expired Wali role or grant a future
role.

`WaliClassEntitlementResolver` composes the existing UserStaffLink,
`WALI_KELAS` role assignment, and active `ClassHomeroomAssignment` records.
It returns per-class, possibly noncontiguous windows intersected with the
requested period. All windows use business-date grain and end-exclusive
semantics:

`effective_from <= business_date < effective_until`

When querying `ClassSession`, Asia/Jakarta local-day boundaries are converted
to UTC instants before binding against the UTC `planned_start_at` column.
Gaps remain gaps; windows are not merged across an unauthorized interval.

## Dashboard surfaces covered

The entitlement windows are applied before read-model construction to:

- class attendance and session metrics;
- attendance session lists;
- teacher attendance summaries;
- period due/finalized/in-progress/upcoming counters;
- daily attendance trends;
- current operational/today and next-session queues;
- current operational student/teacher/class counts;
- joint-session class labels and existing effective-enrollment participant partitioning;
- Academic dashboard CSV data.

Waka Akademik and Super Admin continue to use their existing institution-wide
scope. Wali grade metrics are suppressed because the current grade semantic
model has no safe point-in-time fact grain; no Grade G3 behavior was added.
Export permission is evaluated at the current action instant, while its CSV
dataset is produced by the same dashboard service and therefore the same
entitlement scope.

Existing per-session authorization and joint roster services remain in place;
no session action authorization or finalizer behavior was changed.

## Files changed

- `application/web/app/Domains/Academic/Services/WaliClassEntitlementResolver.php`
- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
- `application/web/app/Domains/Academic/Services/AcademicDashboardExportService.php`
- `application/web/app/Domains/Academic/Services/AttendanceSemanticMetricsService.php`
- `application/web/app/Domains/Academic/Services/CanonicalAttendanceSemanticService.php`
- `application/web/app/Domains/Academic/Services/SessionSemanticMetricsService.php`
- `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
- this change manifest

## Focused regression coverage added

- current Wali role cannot be resurrected through a historical filter;
- future Wali role does not authorize dashboard access today;
- sequential class transition scopes each class to its own authorized window;
- noncontiguous entitlement gap excludes sessions and metrics;
- historical export permission cannot grant the current export action.

Existing Wali joint partition, current-day operational, session, export, trend,
R3, and R4A1 tests remain in the regression set.

## Validation

Passed locally without opening the protected database:

- Composer validation: `composer validate --strict`;
- PHP lint for all changed PHP files and focused test file;
- Pint check for all changed PHP files and focused test file;
- `php artisan view:cache`;
- Academic route inspection;
- `python3 scripts/check_project_structure.py`;
- `git diff --check`.

The focused PHPUnit invocation was safely blocked before test setup by the
existing `TestDatabaseIdentityGuard`: local `.env` resolves to protected
database `imtaq`. Result was 50 guard errors, 0 assertions, 0 database writes.
No guard bypass, pilot query, or pilot write was performed. Disposable
PostgreSQL GitHub Actions is authoritative for feature and foundation tests.

## Rollback

Revert the R4A2 implementation commit(s) on this branch. No migration or
persistent schema change exists, so rollback requires no database operation.
The prior dashboard read behavior is retained by reverting the listed source
and test files; the existing `codex/AUDITS/` artifacts remain untouched.

## Closeout update

The exact disposable PostgreSQL workflow passed on commit
`4e443dcce84efb2b6e8d1a9990fe33736f4efee8` in run `37323408359`. The first
implementation run `37322682819` exposed one null-handling regression in the
CSV export path; the follow-up commit added the minimum null guard for the
intentional Wali grade-suppression representation and passed the full workflow.
The passing run covers the current-role authorization, point-in-time
class/session/metric/export scope, transitions, gaps, joint behavior,
Asia/Jakarta boundaries, and R4A1/R3 regressions. No PILOT access or write was
performed.

The implementation commit contains 17 passing tests and 0 failures in the
foundation verification step; GitHub Actions is the authoritative disposable
PostgreSQL result. The run emitted the repository's existing PHPUnit deprecation
and AI-runtime warning output; no warning was promoted to a failure.

**Next atomic task:** return to ChatGPT/project owner for R4A2 audit. Do not
start R4A3, R4B, Grade G3, AI, or PILOT work automatically.

**SAFE_TO_CLOSE:** `YES`
