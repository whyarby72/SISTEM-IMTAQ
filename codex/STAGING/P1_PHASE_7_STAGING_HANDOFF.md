# P1-R4 staging handoff

This is a plan only. Do not execute any step as part of the local closeout or P1-R4.

## Preconditions

1. Confirm the target is staging only.
2. Take a staging backup/snapshot and verify restore capability.
3. Run `P1_POSTGRES_PREFLIGHT_READONLY.sql` using a read-only database role.
4. Abort if any non-NULL value is outside the final documented vocabulary: `FULL_CLASS`, `SELECTED_STUDENTS`; teacher roles `PRIMARY`, `SUBSTITUTE`; obligation types `TEACHING_ASSIGNMENT`, `REPLACEMENT`.
5. Review all `ADMIN_AKADEMIK` assignments; unresolved active assignments abort the gate.
6. Resolve the approved authority bootstrap procedure before running it.

## Final vocabulary contract

| Table.column | Allowed values | Nullable |
|---|---|---:|
| `class_sessions.participant_scope` | `FULL_CLASS`, `SELECTED_STUDENTS` | NO |
| `session_teacher_participations.role` | `PRIMARY`, `SUBSTITUTE` | NO |
| `session_teacher_participations.obligation_type` | `TEACHING_ASSIGNMENT`, `REPLACEMENT` | NO |

`JOINT_SCOPE` is a schedule/class grouping `scope_role`, not a teacher participation role. `SUBSTITUTION` is a schedule change type, not a participation obligation type.

## Execution order

1. Verify backup and rollback capability.
2. Run the read-only vocabulary and RBAC audit.
3. Bootstrap `academic.domain.manage` and `platform.institution.manage` using an approved, non-destructive procedure.
4. Verify effective WAKA_AKADEMIK, SUPER_ADMIN, and scoped WALI_KELAS authority.
5. Apply migration `2026_09_11_000020_add_academic_controlled_vocabulary_checks`.
6. Verify all nine named constraints exist.
7. Test valid values, invalid-value rejection in an isolated rollback transaction, and lawful NULL attendance values.
8. Run the full Academic, Shared/Auth, and Admin regressions plus `php artisan view:cache`.
9. Run the PostgreSQL concurrency scenarios below.
10. Inspect audit logs, final DB states, rollback evidence, and obtain staging sign-off.

## PostgreSQL concurrency scenarios

- Teacher attendance versus `COMPLETED`: transaction A locks the session and records attendance; transaction B completes the session. Expected final state: no post-`COMPLETED` teacher mutation.
- Two overlapping substitutions for the same teacher: both attempt the same candidate interval. Expected: advisory lock serializes them and at most one commits.
- Correction approve versus reject: both lock one `CorrectionRequest`. Expected: exactly one final decision and one decision audit.
- Duplicate apply: both attempt the same approved request. Expected: one attendance mutation; the second sees non-`APPROVED`.
- Cross-path substitution versus swap/schedule assignment: run concurrently with the same teacher and interval. Expected: no double-booked teacher. Source serialization is implemented through `TeacherObligationLockService`; real PostgreSQL contention proof remains pending.

Additional required races:

- Substitution versus reschedule for the same teacher and overlapping target: at most one conflicting obligation commits.
- Substitution versus extra/ad-hoc session or scheduled materialization: at most one conflicting obligation commits.
- Correction approve versus reject: exactly one decision and one decision audit.
- Duplicate correction apply: exactly one business mutation.

For each scenario record transaction order/blocking, final rows, and audit rows.

## Rollback

If migration rollback is approved, invoke only the migration's `down()` and verify that only the nine named constraints are removed. Do not rewrite business data or roll back unrelated migrations.
