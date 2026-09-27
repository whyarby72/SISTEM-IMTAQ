# Change Manifest

- Change ID: FIX-P1-FINAL-REGRESSION-STAGING-HANDOFF-2026-09-11
- Status: PARTIAL — local gate complete; staging gate not cleared
- Git metadata: unavailable in repository workspace

## Changeset inventory

- Governance/docs: P1 Phase 1 policy/ADR/RBAC synchronization.
- Authorization: `AcademicAuthorizationService`, authority callers, and permission source seeder.
- Teacher attendance: `TeacherAttendanceService` and regression tests.
- Substitution: `SubstitutionService` and regression tests.
- Correction: `PostLockAttendanceCorrectionService` and regression tests.
- PostgreSQL: `2026_09_11_000020_add_academic_controlled_vocabulary_checks.php` and Phase 6 manifest/impact.
- Tests: Academic authorization, draft/finalizer, teacher attendance, substitution, correction, and related regression fixtures.
- Handoff: read-only vocabulary SQL and staging runbook.

No unexplained P1 attendance source file was found. No new P2 feature was started.

## Authorization audit

- WAKA full Academic authority: PASS through effective `academic.domain.manage`.
- SUPER_ADMIN full Academic/institution authority: PASS through effective `platform.institution.manage`.
- WALI_KELAS scope: PASS for effective homeroom scope.
- Expired/future assignments: covered by authorization tests and denied outside effective dates.
- Resource/workflow protections remain enforced for completed/cancelled/rescheduled, validated, locked, correction, and publication paths.
- Remaining raw role checks are classified as WORKFLOW_INTENTIONAL or LEGACY_DEFERRED in report/transcript/dashboard presentation paths; no P1 attendance authorization bug was identified.

## Permission bootstrap finding

`ConsolidateAcademicRolesSeeder` creates permissions idempotently and uses `syncWithoutDetaching` for approved permission grants, but it also deletes pilot user assignments and deletes `ADMIN_AKADEMIK` assignments/role. Therefore the seeder does **not** satisfy the Phase 7 non-destructive bootstrap requirement.

Status: `FAIL — DEFERRED MANAGEMENT/SECURITY DECISION REQUIRED`.

It was not executed and was not modified in this phase.

## Concurrency audit

- Teacher attendance resource-state source hardening: PASS.
- Real PostgreSQL teacher contention: DEFERRED_TO_STAGING.
- Substitution candidate advisory lock: PASS at source level. Key is PostgreSQL `hashtext('academic-substitution:' || teacher_id)`, deterministic for the same database input and stable across PHP processes; PostgreSQL advisory-lock collision risk is the normal 32-bit hash risk and must be accepted/tested in staging.
- Correction review/apply source hardening: PASS.
- Real PostgreSQL correction contention: DEFERRED_TO_STAGING.
- Cross-path teacher obligation serialization: `OPEN_STAGING_RISK`. `SwapService` and other assignment writers do not use the substitution advisory lock; no business change was made here.

## PostgreSQL migration audit

- Migration source: PASS.
- Constraint count: 9 expected / 9 declared.
- Nullable attendance semantics: preserved; NULL is explicitly accepted for both attendance-status checks.
- `down()` safety: PASS by source inspection; only the nine named constraints are dropped, in reverse order.
- Distinct-value audit: NOT RUN locally; available SQLite file lacks complete application tables.
- Real PostgreSQL checks: NOT RUN.

## Verification

- Targeted P1 suite: 49 tests / 153 assertions / 0 failures.
- Academic regression: 209 tests / 811 assertions / 0 failures.
- Shared/Auth regression: 62 tests / 263 assertions / 0 failures.
- View cache: PASS.
- PHP syntax checks: PASS.
- Migration execution: NOT RUN.
- Seeder execution: NOT RUN.

## Staging artifacts

- Read-only audit: `codex/STAGING/P1_POSTGRES_PREFLIGHT_READONLY.sql`
- Runbook: `codex/STAGING/P1_PHASE_7_STAGING_HANDOFF.md`

## Closeout

P1 local/UAT gate: CLEARED for local source/regression evidence; NOT CLEARED for staging.
Production ready: NO — PostgreSQL data audit, migration proof, non-destructive permission bootstrap, backup/rollback, monitoring, and real contention tests remain required.
Next: STOP. Do not begin P2 or staging automatically.
