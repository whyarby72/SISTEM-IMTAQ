# Change Manifest

- **Change ID:** FIX-P1-R4-CONTROLLED-VOCABULARY-2026-09-12
- **Task ID:** P1-R4 — Pilot Governance Policy + Controlled Vocabulary Contract Correction
- **Title:** Correct unapplied PostgreSQL vocabulary contract before staging
- **Date:** 2026-09-12
- **Owner module/workstream:** Academic governance, session integrity, staging readiness
- **Change class:** `DATABASE_GLOBAL`, `MODULE_CONTRACT`, `SECURITY_GLOBAL`
- **Business outcome:** Make the PostgreSQL CHECK proposal match the implemented Academic contract and the active pilot governance policy before staging.
- **Git branch:** Not available; repository has no Git metadata
- **Git commit / release:** Not available

## Impact

- **Affected modules/workstreams:** Academic sessions, teacher participation, scheduling writers, RBAC/staging documentation
- **Source-of-truth entities/services affected:** `class_sessions.participant_scope`; `session_teacher_participations.role`; `session_teacher_participations.obligation_type`; `ExtraSessionCreator`
- **Shared/public contracts changed:** YES — Academic controlled vocabulary corrected before database application
- **RBAC/security/privacy impact:** Policy documentation sync only; no new restriction. `WAKA_AKADEMIK` full Academic authority, `SUPER_ADMIN` full institution authority, `ADMIN_AKADEMIK` retired.
- **Database migration:** YES — corrected unapplied `2026_09_11_000020_add_academic_controlled_vocabulary_checks.php`
- **Backward-compatibility impact:** `SELECTED_STUDENTS` is preserved; unsupported teacher-role/obligation values are rejected by the future CHECK. Existing real DB values must be audited first.
- **Environment/config variables changed:** NONE
- **Feature flags changed:** NONE

## Final constraint matrix

| Table | Column | Constraint | Allowed values | Nullable |
|---|---|---|---|---:|
| `class_sessions` | `session_status` | `chk_class_sessions_session_status` | `PLANNED`, `CONFIRMED`, `COMPLETED`, `CANCELLED`, `RESCHEDULED` | NO |
| `class_sessions` | `session_source` | `chk_class_sessions_session_source` | `SCHEDULED`, `EXTRA`, `RESCHEDULED`, `AD_HOC` | NO |
| `class_sessions` | `participant_scope` | `chk_class_sessions_participant_scope` | `FULL_CLASS`, `SELECTED_STUDENTS` | NO |
| `session_teacher_participations` | `role` | `chk_session_teacher_participations_role` | `PRIMARY`, `SUBSTITUTE` | NO |
| `session_teacher_participations` | `obligation_type` | `chk_session_teacher_participations_obligation_type` | `TEACHING_ASSIGNMENT`, `REPLACEMENT` | NO |
| `session_teacher_participations` | `participation_status` | `chk_session_teacher_participations_participation_status` | `EXPECTED` | NO |
| `session_teacher_participations` | `attendance_status` | `chk_session_teacher_participations_attendance_status` | `PRESENT`, `ABSENT`, `SICK`, `IZIN`, `OTHER` | YES |
| `student_attendance` | `attendance_status` | `chk_student_attendance_attendance_status` | `PRESENT`, `ABSENT`, `SICK`, `IZIN`, `LATE`, `EXCUSED` | YES |
| `student_attendance` | `workflow_status` | `chk_student_attendance_workflow_status` | `DRAFT`, `VALIDATED` | NO |

## Contract audit

- `FULL_CLASS` and `SELECTED_STUDENTS` are both supported by the application contract; `ExtraSessionCreator` now rejects any other scope before persistence.
- `JOINT_SCOPE` is used by schedule/class grouping as `scope_role`, not by `SessionTeacherParticipation.role`; it is `LEGACY_OR_INCORRECT` as a teacher participation role.
- Runtime writers use `TEACHING_ASSIGNMENT` for PRIMARY and `REPLACEMENT` for SUBSTITUTE. `SUBSTITUTION` remains a schedule change type, not a teacher participation obligation type.
- No real database values were changed or coerced.

## Files

- **Files added:** `codex/CHANGE_IMPACTS/FIX-P1-R4-CONTROLLED-VOCABULARY-2026-09-12.md`; this manifest
- **Files modified:** `application/web/database/migrations/2026_09_11_000020_add_academic_controlled_vocabulary_checks.php`; `application/web/app/Domains/Academic/Services/ExtraSessionCreator.php`; focused Academic tests; `docs/02_architecture/RBAC_MATRIX.md`; `docs/02_architecture/POSTGRESQL_SCHEMA.md`; staging SQL/runbook; prior migration manifest note; `codex/WORK_LOG.md`
- **Files deleted:** None
- **Protected zones touched:** Unapplied migration source only; no applied migration, database, seed, or RBAC runtime mutation

## Verification

- **Automated tests run:** Focused contract/writer tests; full `php artisan test tests/Feature/Academic`; `php artisan view:cache`; PHP lint; Pint
- **Regression scope:** Academic plus view cache and Pint/lint
- **Result:** Focused **44 tests / 245 assertions / 0 failures**; Academic **214 tests / 819 assertions / 0 failures**; view cache PASS; PHP lint PASS
- **Staging result:** NOT_APPLICABLE — explicitly deferred
- **Smoke test:** Not run against real PostgreSQL

## Deployment

- **Deploy readiness:** STAGING_READY after required read-only audit and backup gates
- **Deployment notes:** Run `P1_POSTGRES_PREFLIGHT_READONLY.sql` with a read-only role; abort on unexpected values. Do not run migration or seeder locally.
- **Rollback / disable procedure:** Approved backup/restore path; migration `down()` only in controlled staging if approved; no historical row rewrite
- **Database recovery dependency:** Verified staging backup/restore required

## Closeout

- **Known limitations/issues:** Real PostgreSQL distinct-value, constraint, and concurrency proof remain pending; actual DB role assignments were not inspected.
- **Documentation updated:** RBAC matrix, PostgreSQL schema, staging SQL, staging runbook, impact and manifests
- **Change Impact Register updated if required:** YES — impact record added
- **Status:** DONE — pre-staging correction complete; staging deferred by management
