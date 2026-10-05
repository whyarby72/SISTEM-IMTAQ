# Change Manifest — Academic Wali Dashboard R4A1

**Project:** SISTEM-IMTAQ
**Task:** `ACADEMIC-WALI-DASHBOARD-R4A1-WRITE-INTEGRITY-AND-LOCKING`
**Decision at local checkpoint:** `ACADEMIC_WALI_R4A1_EVIDENCE_PARTIAL`

## Repository and safety boundary

- Repository: `whyarby72/SISTEM-IMTAQ`
- Branch: `feat/super-admin-user-access-preferences`
- Starting HEAD: `6418644c3cb2ab4472dfd665ae9287384c15770c`
- Remote branch at preflight: same SHA
- Tested executable HEAD: `f8dad35b06bfbfc2e5817d4c9d49af7bb6f0c8a9`
- Final governance HEAD: `4beaca1cec407f845f8993555a0af695cdc0b134`
- Exact GitHub Actions: run `37293302925` on the exact HEAD, `FAILURE` at `Run foundation verification`
- Application source changed: `YES`, only R4A1 scope
- Migration: `NONE`
- Schema: `NONE`
- Dependency: `NONE`
- Runtime configuration: `NONE`
- PILOT access/write: `NONE/NONE`
- Public Academic AI: `OFF`
- Grade G3: `DEFERRED_BY_OWNER_PRIORITY`
- Human UAT: `DEFERRED`
- Academic Web: `4/10 = 40% COMPLETE_EVIDENCED`

The existing local R4 audit documents under `codex/AUDITS/` were preserved as known audit artifacts. No audit document was rewritten by this implementation.

## P1-01 — teacher attendance lock

`TeacherAttendanceService` now evaluates `AttendanceScopeLockEvaluator` at the domain transaction boundary after locking the authoritative `ClassSession`.

- ordinary session: the single canonical class is checked;
- joint session: every class returned by `AcademicClassScopeResolver::forSession()` is checked;
- if any participating class is locked, the shared teacher fact is rejected;
- controller read-model uses the same all-class teacher lock scope;
- normal post-lock teacher correction remains intentionally unimplemented.

Result: implementation present; focused database tests pending disposable PostgreSQL.

## P1-06 — joint correction lock scope

Added `SessionParticipantEffectiveClassResolver` as the narrow reusable authority for mapping a `SessionStudentParticipant` to exactly one effective enrollment class inside the canonical session scope on the session business date.

- zero or multiple effective matches fail closed;
- Wali submit checks the participant’s effective class, not the Wali partition union;
- approved apply re-resolves the participant class and rechecks the current lock;
- direct Waka/Super Admin correction checks the participant class and cannot bypass a locked participant period through the normal override primitive;
- `SessionAttendanceScopeResolver` reuses the resolver’s matching logic rather than maintaining a second enrollment predicate.

Result: implementation present; focused joint PostgreSQL tests pending disposable PostgreSQL.

## P1-07 — cancellation race

`CancellationService::apply()` now performs its decisive state and attendance checks inside a transaction after `ClassSession::lockForUpdate()`. The stale incoming model is no longer authoritative. Existing bulk cancellation already used the same session lock pattern and was left narrow.

Added `CancellationConcurrencyTest` using two independent PostgreSQL connections through a forked worker and an explicit socket barrier:

- attendance-first: attendance obtains the session lock, cancellation waits, then rejects after attendance commits;
- cancellation-first: cancellation obtains the session lock, attendance waits, then rejects after cancellation commits;
- final state and ScheduleChange count are asserted for both orders.

The concurrency test is not claimed as PASS until it runs on disposable PostgreSQL.

## Lock-order/deadlock assessment

Affected normal write order is:

`ClassSession FOR UPDATE → participant/attendance or teacher participation FOR UPDATE → mutation/audit`

Cancellation and canonical occurrence cancellation retain the `ClassSession`-first order. No reverse `attendance → ClassSession` order was introduced in the changed code. Final deadlock assessment remains pending executable PostgreSQL regression.

## Files changed

- `application/web/app/Domains/Academic/Services/SessionParticipantEffectiveClassResolver.php`
- `application/web/app/Domains/Academic/Services/SessionAttendanceScopeResolver.php`
- `application/web/app/Domains/Academic/Services/TeacherAttendanceService.php`
- `application/web/app/Domains/Academic/Services/PostLockAttendanceCorrectionService.php`
- `application/web/app/Domains/Academic/Services/CancellationService.php`
- `application/web/app/Http/Controllers/Academic/StudentAttendanceController.php`
- `application/web/resources/views/academic/attendance/show.blade.php`
- `application/web/tests/Feature/Academic/TeacherAttendanceServiceTest.php`
- `application/web/tests/Feature/Academic/PostLockAttendanceCorrectionServiceTest.php`
- `application/web/tests/Feature/Academic/CancellationServiceTest.php`
- `application/web/tests/Feature/Academic/CancellationConcurrencyTest.php`

## Validation status

Passed locally without opening the protected database:

- PHP lint for all changed PHP files;
- `composer validate --strict`;
- Pint check for all changed PHP files;
- `php artisan view:cache`;
- attendance route inspection;
- `git diff --check`.

Not completed locally:

- focused PostgreSQL feature tests: 29 discovered, 0 executed, 0 assertions, 29 guard errors; blocked because `.env` points to protected `imtaq`;
- disposable PostgreSQL replay: Docker daemon was unavailable, so no disposable container was started;
- exact GitHub Actions run `37293302925`: exact SHA matched, but foundation verification failed; no PASS claim is made.

No test command was allowed to bypass the guard. No PILOT query or write was performed.

## Current disposition

`ACADEMIC_WALI_R4A1_EVIDENCE_PARTIAL`

The source remediation is implemented, but P1-07 and the overall R4A1 decision remain unclosed until focused disposable-PostgreSQL tests, full regression, and a successful exact-head GitHub Actions run are available. The exact run `37293302925` failed at `Run foundation verification`; its failure log was not publicly retrievable from the unauthenticated API endpoint.

## Next atomic action

Run the changed and full foundation suites against disposable PostgreSQL 18.6, then publish the exact executable commit for GitHub Actions verification. Do not access PILOT and do not start R4A2 automatically.
