# Change Manifest — Academic Wali Dashboard Maturity R2B-A

Date: 2026-10-04  
Task: `ACADEMIC-WALI-DASHBOARD-MATURITY-R2B-A-JOINT-SCOPE-AUTHORIZATION`  
Branch: `feat/super-admin-user-access-preferences`  
Starting HEAD: `10a8f99d730c0354ac0fa6f328252801e0a71b12`

## Scope

R2B-A implements the canonical attendance read/scope boundary for one
physical `ClassSession`. It does not implement partitioned finalization,
global session completion, correction/lock changes, migrations, or PILOT
provisioning.

## Implemented

- Added `SessionAttendanceScopeResolver` as the server-owned scope contract.
- Reused `AcademicClassScopeResolver` for canonical session class IDs.
- Intersected effective Wali homeroom assignments with the session scope.
- Partitioned joint participants by effective `StudentClassEnrollment` on the
  session date.
- Failed closed for unmapped or ambiguous joint participant mapping.
- Allowed anchor and non-anchor Wali access to the same physical joint session
  while filtering the detail HTML/read model to the authorized partition.
- Preserved full-session visibility for Waka/Super Admin authority.
- Applied the same scope guard to HTTP draft participant IDs.
- Held Wali joint finalization fail-closed until the separately authorized
  partitioned-finalization task; `StudentAttendanceFinalizer` itself was not
  refactored.
- Reused the joint-aware authorization boundary for draft and grooming-note
  services without changing attendance facts or finalizer semantics.

## Tests added/updated

- `StudentAttendanceUiTest`: anchor/non-anchor Wali partition, full Waka view,
  unrelated Wali denial, and unmapped joint session denial.
- `SessionAttendanceScopeResolverTest`: unmapped and ambiguous joint mapping
  fail-closed behavior.
- Existing ordinary single-class attendance, teacher participation, repeated
  GET, and no-duplicate behavior remain in the focused suite.

## Verification

- PHP lint: PASS for all changed PHP files.
- Pint: PASS for all changed PHP/test files.
- Blade view cache: PASS.
- `python3 scripts/check_project_structure.py`: PASS.
- `git diff --check`: PASS.
- Local focused PHPUnit: safely blocked before database access by the existing
  protected PILOT identity guard (`47 tests, 0 assertions executed`). No guard
  bypass was attempted.
- Disposable PostgreSQL CI: first implementation run `37208245208` exposed a
  test-fixture-only `ClassSessionGroup::createMany()` incompatibility; the
  fixture was corrected without production changes. Final exact-head run
  `37208377746` on `9fb5f8816b1f7aa3a7606d0a1ad2eb1b508286b5` passed.

## Safety boundary

No migration, schema/config change, schedule/session/roster/account/role data
write, attendance fact write, teacher participation write, AI/provider change,
PILOT access/write, deployment, or Grade G3 work occurred. Existing GET lazy
materialization behavior was not expanded by this scope resolver.

## Rollback

Revert the implementation commit. No database rollback is required because no
database mutation occurred. If joint finalization remains held, the prior
session-level finalizer remains unchanged.

## Decision

Implementation result: `R2B_A_IMPLEMENTED_PASS`  
Final tested HEAD: `9fb5f8816b1f7aa3a7606d0a1ad2eb1b508286b5`  
Final exact CI: `37208377746` = SUCCESS  
Next task after exact CI: `ACADEMIC-WALI-DASHBOARD-MATURITY-R2B-B-PARTITIONED-FINALIZATION`  
PILOT access/write: `NONE / NONE`  
Public Academic AI: `OFF`  
Grade G3: `DEFERRED_BY_OWNER_PRIORITY`
