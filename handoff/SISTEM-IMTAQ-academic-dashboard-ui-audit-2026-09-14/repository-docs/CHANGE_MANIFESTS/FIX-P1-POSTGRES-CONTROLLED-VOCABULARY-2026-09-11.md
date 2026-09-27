# Change Manifest

> R4 correction: this pre-staging manifest records the original proposal. Its participant-scope, teacher-role, and obligation-type values were audited and corrected by `P1-R4` before any PostgreSQL application. Use the R4 manifest as the final contract.

- **Change ID:** FIX-P1-POSTGRES-CONTROLLED-VOCABULARY-2026-09-11
- **Task ID:** P1 Phase 6 — PostgreSQL Controlled-Vocabulary Checks
- **Date:** 2026-09-11
- **Status:** DONE
- **Git branch/commit:** Not available; repository has no Git metadata

## Pre-migration inventory

Source, validation, migration, seeder, and Academic fixture evidence was audited before writing the migration. Local SQLite database inspection was attempted but the available file did not contain the application tables; therefore PostgreSQL distinct values are `NOT_AVAILABLE_LOCALLY` and must be audited before staging/production migration.

| Table.column | Approved source vocabulary | Nullable | Existing DB distinct values | Unexpected/legacy values | Proposed constraint |
|---|---|---:|---|---|---|
| `class_sessions.session_status` | `PLANNED`, `CONFIRMED`, `COMPLETED`, `CANCELLED`, `RESCHEDULED` | NO | NOT AVAILABLE LOCALLY | None found in source/fixtures | `chk_class_sessions_session_status` |
| `class_sessions.session_source` | `SCHEDULED`, `EXTRA`, `RESCHEDULED`, `AD_HOC` | NO | NOT AVAILABLE LOCALLY | None found in source/fixtures | `chk_class_sessions_session_source` |
| `class_sessions.participant_scope` | `FULL_CLASS` | NO | NOT AVAILABLE LOCALLY | None found in source/fixtures | `chk_class_sessions_participant_scope` |
| `session_teacher_participations.role` | `PRIMARY`, `SUBSTITUTE`, `JOINT_SCOPE` | NO | NOT AVAILABLE LOCALLY | None found in source/fixtures | `chk_session_teacher_participations_role` |
| `session_teacher_participations.obligation_type` | `TEACHING_ASSIGNMENT`, `REPLACEMENT`, `SUBSTITUTION` | NO | NOT AVAILABLE LOCALLY | None found in source/fixtures | `chk_session_teacher_participations_obligation_type` |
| `session_teacher_participations.participation_status` | `EXPECTED` | NO | NOT AVAILABLE LOCALLY | None found in source/fixtures | `chk_session_teacher_participations_participation_status` |
| `session_teacher_participations.attendance_status` | `PRESENT`, `ABSENT`, `SICK`, `IZIN`, `OTHER` | YES | NOT AVAILABLE LOCALLY | None found in source/fixtures | `chk_session_teacher_participations_attendance_status` |
| `student_attendance.attendance_status` | `PRESENT`, `ABSENT`, `SICK`, `IZIN`, `LATE`, `EXCUSED` | YES | NOT AVAILABLE LOCALLY | None found in source/fixtures | `chk_student_attendance_attendance_status` |
| `student_attendance.workflow_status` | `DRAFT`, `VALIDATED` | NO | NOT AVAILABLE LOCALLY | None found in source/fixtures | `chk_student_attendance_workflow_status` |

## Migration

Added `application/web/database/migrations/2026_09_11_000020_add_academic_controlled_vocabulary_checks.php`.

- PostgreSQL adds only the nine named CHECK constraints above.
- SQLite and other local non-PostgreSQL engines skip PostgreSQL-specific statements deliberately; this is documented as a test-environment limitation, not equivalent constraint proof.
- `down()` drops only these named constraints in reverse table order; it does not drop columns/tables or mutate rows.
- No enum, business vocabulary, nullable property, index, foreign key, table, or status was added.

## Verification

- Targeted application suite covering session, teacher participation/attendance, finalization, substitution, correction, and authorization: **49 tests / 153 assertions / 0 failures**
- Full `php artisan test --compact tests/Feature/Academic`: **209 tests / 811 assertions / 0 failures**
- `php artisan view:cache`: PASS
- Migration PHP lint/Pint: PASS
- PostgreSQL distinct-value audit: **NOT_AVAILABLE_LOCALLY**
- Real PostgreSQL CHECK constraint proof: **DEFERRED_TO_STAGING**
- Migration execution against production-style PostgreSQL: NOT RUN
- Seed execution: NONE
- Historical data rewrite: NONE
- P0/P1 regression: PASS through full Academic suite

## Staging dependencies

- `AUTHORITY_PERMISSION_BOOTSTRAP_REQUIRED_BEFORE_STAGING`
- Actual PostgreSQL `SELECT DISTINCT` inventory must be clean before applying migration.
- Apply migration in staging, then test valid values, invalid values, and lawful NULLs per target column.
- Run real PostgreSQL concurrency checks deferred from Phases 3–5.
