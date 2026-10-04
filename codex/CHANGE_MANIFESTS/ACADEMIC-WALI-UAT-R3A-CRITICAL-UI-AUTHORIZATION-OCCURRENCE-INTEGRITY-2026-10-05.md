# Change Manifest — ACADEMIC-WALI-UAT-R3A-CRITICAL-UI-AUTHORIZATION-OCCURRENCE-INTEGRITY

Date: 2026-10-05
Project: SISTEM-IMTAQ
Branch: `feat/super-admin-user-access-preferences`
Starting HEAD: `63ca719bc9b12d39a94764feb4cc59237a4309e7`
Tested executable HEAD: `31f59cb851a44f2145f15ed71aeed0df89798419`

## Scope

Implemented only the R3A critical UI, authorization, historical-write acknowledgement, and canonical occurrence-integrity changes. R3B, Grade G3, pilot provisioning, migrations, schema changes, dependency changes, and AI/provider changes are out of scope.

## Changed application files

- `application/web/app/Domains/Academic/Services/AcademicSessionExecutionStateResolver.php`
- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
- `application/web/app/Domains/Academic/Services/SessionOccurrenceAuthorizationService.php`
- `application/web/app/Domains/Academic/Services/StudentAttendanceDraftService.php`
- `application/web/app/Domains/Academic/Services/StudentAttendanceFinalizer.php`
- `application/web/app/Domains/Academic/Services/TeacherAttendanceService.php`
- `application/web/app/Http/Controllers/Academic/StudentAttendanceController.php`
- `application/web/resources/views/academic/attendance/show.blade.php`
- `application/web/resources/views/academic/dashboard.blade.php`

## Changed test files

- `application/web/tests/Feature/Academic/CanonicalAttendanceSemanticContractTest.php`
- `application/web/tests/Feature/Academic/StudentAttendancePartitionedFinalizerTest.php`
- `application/web/tests/Feature/Academic/StudentAttendanceUiTest.php`

## Behavioral contract

- Canonical sessions without an effective occurrence, or with `SCHEDULED`, do not create an attendance obligation or permit attendance input.
- `HELD` and partial `HELD` sessions permit attendance obligation/input.
- `CANCELLED` and `RESCHEDULED` sessions do not permit attendance obligation/input.
- Wali may manage routine held execution only; physical cancel/reschedule remains full Academic-authority only.
- Historical Wali attendance writes require explicit `historical_session_ack=accepted`.
- Dashboard and attendance read models use the same canonical execution-state resolver.
- Legacy sessions preserve prior attendance behavior, subject to existing cancelled/rescheduled guards.
- Rendered attendance UI contains no Blade directive leakage or server expression leakage.

## Safety boundaries

- No pilot, staging, or production database was accessed or written.
- No migration, schema, dependency, runtime configuration, AI/provider, or deployment change was made.
- No student/teacher names, credentials, secrets, or external data were added to evidence.
- Public Academic AI remains OFF; Grade G3 remains deferred; SOC-MD-06 remains unchanged.

## Validation evidence

- PHP lint: PASS for all files changed in the final compatibility fix.
- Pint: PASS for all files changed in the final compatibility fix.
- `git diff --check`: PASS.
- Local PHPUnit: NOT RUN; protected pilot database identity guard denied local execution, so no bypass was used.
- Exact GitHub Actions workflow: `Application foundation`.
- Exact CI run: `37244157885`.
- Exact CI commit: `31f59cb851a44f2145f15ed71aeed0df89798419`.
- Exact CI result: SUCCESS.
- Foundation verification: `17 passed`, `578 warnings`, `2490 assertions`; no test failures.
- PostgreSQL 18.6 disposable service, UTC session check, identity guard, migration-from-zero, schema/extension checks: PASS.

## Known non-blocking CI evidence

GitHub Actions emitted existing platform annotations for Node.js 20 deprecation and the scheduled `ubuntu-latest` image migration. PostgreSQL logs also contain expected negative-test constraint/FK and transaction-aborted diagnostics; the foundation verification itself passed and no constraint was weakened.

## Decision

`ACADEMIC_WALI_R3A_IMPLEMENTED_PASS`

Next atomic task: `RETURN_TO_CHATGPT_FOR_R3A_AUDIT`

Database write: `NONE`
SAFE_TO_CLOSE: `YES`
