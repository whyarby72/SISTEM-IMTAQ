# Change Manifest — Academic Wali Dashboard Maturity R2B-C1

## Task

`ACADEMIC-WALI-DASHBOARD-MATURITY-R2B-C1-PARTITIONED-FINALIZER-DOMAIN`

## Baseline and scope

- Starting branch: `feat/super-admin-user-access-preferences`
- Starting HEAD: `ae06a7a7fd6bb0a109765ea1b2fbf5875e45eb02`
- Scope: domain-layer partitioned student-attendance finalization only.
- Application source changed: `YES` — `StudentAttendanceFinalizer` only.
- Test source changed: `YES` — focused partitioned-finalizer regression coverage.
- Migration/schema/dependency/runtime configuration: `NONE`.
- PILOT access/write: `NONE / NONE`.
- Public Academic AI: `OFF`.
- G3: deferred by owner priority.

## Implemented contract

- Locks the physical `ClassSession` first and rechecks finalizable state inside the transaction.
- Resolves joint-session scope through `SessionAttendanceScopeResolver` using effective enrollment on the session date.
- Wali finalization targets only the authorized class partition; full Academic authority retains full-session scope.
- Rejects expected-version keys outside the authorized target participant set.
- Locks target participant and attendance rows before validation/mutation.
- Applies the existing period lock evaluator to the effective scope.
- Preserves the existing primary-teacher attendance prerequisite.
- Promotes only `DRAFT` attendance rows to `VALIDATED`; retries are idempotent and do not duplicate finalization audits.
- Completes the physical session only when global required-participant completeness is satisfied.
- Leaves the existing controller guard that blocks joint-session Wali HTTP finalization in place; this task does not activate the route.
- Ordinary one-class finalization compatibility is retained.

## Tests and evidence

- Added `StudentAttendancePartitionedFinalizerTest` covering:
  - sequential anchor/non-anchor Wali partition finalization;
  - session completion only after both partitions are resolved;
  - out-of-scope expected-version rejection;
  - unmapped joint participant fail-closed behavior.
- Existing `StudentAttendanceFinalizerTest` remains in the regression set.
- Focused PHPUnit execution was attempted but stopped at the repository database guard because the local resolved identity is the protected pilot; no business query/write was performed.
- `composer validate --strict`: PASS.
- `vendor/bin/pint --test` on changed PHP files: PASS.
- `php artisan view:cache`: PASS.
- `python3 scripts/check_project_structure.py`: PASS.
- Git diff check: PASS.
- First implementation CI `37211919259` on `2052185c73abeedd391bf2b65fb7af958e0239df`: failed only because the new regression fixture expected an uncreated B attendance row to be `DRAFT`.
- The fixture was corrected without production changes.
- Final exact implementation CI `37212105492` on `7ba316397dde0c9de27ab1fd9e23fa1c28124c71`: SUCCESS; 15 passed, 569 warnings, 2418 assertions.

## Not changed

- `StudentAttendanceController` joint-finalization guard remains unchanged.
- `StudentAttendanceFinalizer` does not create roster, teacher participation, student attendance, correction, lock, schedule, or session records.
- No pilot/staging/production database was accessed or mutated.
- No migration or schema change was made.

## Rollback

Revert the implementation commit to restore the prior finalizer and remove the focused test/manifest. No database rollback is required because this change performs no data migration or provisioning.

## Closeout state

- R2B-C1 decision: `IMPLEMENTED / PASS`
- Next atomic task after exact green CI: `ACADEMIC-WALI-DASHBOARD-MATURITY-R2B-C2`
- Safe to close: `YES`
