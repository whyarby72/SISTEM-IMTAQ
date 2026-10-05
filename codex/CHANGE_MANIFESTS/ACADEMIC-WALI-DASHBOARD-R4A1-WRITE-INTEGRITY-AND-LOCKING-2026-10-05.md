# Change Manifest — Academic Wali Dashboard R4A1

**Project:** SISTEM-IMTAQ
**Task:** `ACADEMIC-WALI-DASHBOARD-R4A1-WRITE-INTEGRITY-AND-LOCKING`
**Decision at local checkpoint:** `ACADEMIC_WALI_R4A1_IMPLEMENTED_PASS`

## Repository and safety boundary

- Repository: `whyarby72/SISTEM-IMTAQ`
- Branch: `feat/super-admin-user-access-preferences`
- Starting HEAD: `6418644c3cb2ab4472dfd665ae9287384c15770c`
- Remote branch at preflight: same SHA
- Tested executable HEAD: `eef7401acf9c98c012458be313a04e4a444d3da6`
- Final governance HEAD before this evidence closure: `4beaca1cec407f845f8993555a0af695cdc0b134`
- Exact GitHub Actions: run `37297519060` on executable HEAD `eef7401acf9c98c012458be313a04e4a444d3da6`, `SUCCESS`
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

Result: implementation present and covered by the successful disposable-PostgreSQL foundation run.

## P1-06 — joint correction lock scope

Added `SessionParticipantEffectiveClassResolver` as the narrow reusable authority for mapping a `SessionStudentParticipant` to exactly one effective enrollment class inside the canonical session scope on the session business date.

- zero or multiple effective matches fail closed;
- Wali submit checks the participant’s effective class, not the Wali partition union;
- approved apply re-resolves the participant class and rechecks the current lock;
- direct Waka/Super Admin correction checks the participant class and cannot bypass a locked participant period through the normal override primitive;
- `SessionAttendanceScopeResolver` reuses the resolver’s matching logic rather than maintaining a second enrollment predicate.

Result: implementation present and covered by the successful disposable-PostgreSQL foundation run.

## P1-07 — cancellation race

`CancellationService::apply()` now performs its decisive state and attendance checks inside a transaction after `ClassSession::lockForUpdate()`. The stale incoming model is no longer authoritative. Existing bulk cancellation already used the same session lock pattern and was left narrow.

Added `CancellationConcurrencyTest` using two independent PostgreSQL connections through a forked worker and an explicit socket barrier:

- attendance-first: attendance obtains the session lock, cancellation waits, then rejects after attendance commits;
- cancellation-first: cancellation obtains the session lock, attendance waits, then rejects after cancellation commits;
- final state and ScheduleChange count are asserted for both orders.

The concurrency test is proven on disposable PostgreSQL by exact run `37297519060`.

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

Local environment remains safely blocked from database execution:

- focused PostgreSQL feature tests: 29 discovered, 0 executed, 0 assertions, 29 guard errors; blocked because `.env` points to protected `imtaq`;
- no local disposable container was started because Docker was unavailable;
- no test command bypassed the guard.

Authoritative disposable-PostgreSQL verification:

- exact executable HEAD: `eef7401acf9c98c012458be313a04e4a444d3da6`;
- exact GitHub Actions run: `37297519060`;
- result: `SUCCESS`;
- foundation suite: 19 passed, 593 warnings, 2600 assertions;
- schema identity, migrations, focused Academic regression, both concurrency races, and full foundation verification passed.

No test command was allowed to bypass the guard. No PILOT query or write was performed.

## Current disposition

`ACADEMIC_WALI_R4A1_IMPLEMENTED_PASS`

The initial exact run `37293302925` failed at `Run foundation verification` because the test called the unsupported `db()` helper. Subsequent harness-only repairs isolated the test class from schema lifecycle contamination and removed the forked PostgreSQL connection invalidation. Exact run `37297519060` then passed the full disposable-PostgreSQL foundation suite.

## R4A1-CI concurrency evidence closure update

The exact run `37293302925` failure was confirmed against repository source:
`CancellationConcurrencyTest` called unsupported `db()`/`db()->purge()` helpers. The test now uses `Illuminate\Support\Facades\DB` only; production source is unchanged in this checkpoint.

The harness now requires `pcntl_fork`, `stream_socket_pair`, and `posix_kill`; disconnects the fixture connection before forking so parent/child do not share a libpq socket; creates independent PostgreSQL backends after fork; records distinct parent/child `pg_backend_pid()` values; observes the child in `pg_stat_activity` with `wait_event_type = Lock` before the parent commits; and serializes outcome plus exception class/message. Socket reads and lock observation are bounded; unexpected Throwable types are asserted as failures rather than converted to domain rejection. The child ACK handshake and hard termination prevent inherited Laravel shutdown callbacks from contaminating the parent test lifecycle.

Local focused result remains safely blocked by the protected-database guard: `2 tests, 0 assertions, 2 guard errors`; no PILOT query or write occurred.

Race evidence from exact run `37297519060`:

- attendance-first: `PASS`; child cancellation rejected with `InvalidArgumentException` and `Session changed before cancellation could be applied.`; final session remained `PLANNED`, one student attendance row existed, and schedule changes remained zero;
- cancellation-first: `PASS`; child attendance rejected with `InvalidArgumentException` and `Pelaksanaan KBM belum dikonfirmasi; kehadiran belum dapat diisi.`; final session was `CANCELLED`, no student attendance row was created, and exactly one schedule change existed;
- parent/child PostgreSQL connection isolation: `PASS`; distinct backend PID assertion passed;
- unexpected Throwable masking: `REMOVED`; exception class/message are transported and asserted;
- P1-01 teacher attendance lock: `CLOSED`;
- P1-06 joint correction lock scope: `CLOSED`;
- P1-07 cancellation race: `CLOSED`.

R4A1-CI current task status: `CLOSED / ACCEPTED`.

## Next atomic action

Return to ChatGPT for R4A1 audit. Do not access PILOT and do not start R4A2 automatically.
