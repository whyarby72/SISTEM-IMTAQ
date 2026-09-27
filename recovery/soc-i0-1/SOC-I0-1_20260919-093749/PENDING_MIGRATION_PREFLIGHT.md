# SOC-I0.1 Pending Migration Preflight

## Migration A — `2026_09_11_000020_add_academic_controlled_vocabulary_checks.php`

Exact effect: adds nine PostgreSQL `CHECK` constraints:

- `class_sessions`: `session_status`, `session_source`, `participant_scope`;
- `session_teacher_participations`: `role`, `obligation_type`, `participation_status`, `attendance_status`;
- `student_attendance`: `attendance_status`, `workflow_status`.

It changes no columns and does not backfill rows. `up()` is PostgreSQL-only and issues direct `ALTER TABLE ... ADD CONSTRAINT`; existing rows must satisfy each rule, and table-level locking/constraint validation can block concurrent activity. It depends on the preceding class-session, teacher-participation, and student-attendance migrations. `down()` drops only these named constraints; it is destructive to the constraint layer but does not delete business rows. Re-running after successful application would fail because `up()` has no `IF NOT EXISTS`.

Read-only preflight found zero violations across all nine rules. NULL handling matches the source expressions: nullable status fields remain allowed where the expression explicitly permits NULL; required workflow/role fields were all populated with allowed values. No same-name `chk_*` constraints currently exist on the affected tables.

Decision: `SAFE_TO_APPLY_BEFORE_SOC`. Disposition remains `APPLY_BEFORE_SOC` only after a separately authorized migration gate and backup/restore review.

## Migration B — `2026_09_17_000001_create_attendance_semantic_foundation_tables.php`

Exact effect:

1. creates `attendance_source_certifications` with UUID primary key, source/period/scope/metric fields, certification status, optional user FKs, timestamps, reason/evidence fields, and two indexes;
2. creates `class_lineage_mappings` with UUID primary key, source reference, UUID FK to `classes`, mapping/effective-date/status fields, optional user FK, timestamps, and two indexes;
3. alters `session_student_participants` by adding nullable `eligibility_status` and `non_eligible_reason`, plus an `(eligibility_status, class_session_id)` index.

No existing business rows are copied or backfilled by the migration source. `down()` drops the new tables and removes the two participant columns/index; after use, that rollback could discard data stored in those new fields. Referenced tables and columns exist: `users.id`, `classes.id`, and `session_student_participants.id/is_required`. Target tables and equivalent structures are currently absent; no extension is required and no target index/constraint collision was found.

Decision: `SAFE_TO_APPLY_BEFORE_SOC` from schema/data compatibility, but disposition is `BLOCKED_PENDING_MANAGEMENT_DECISION` because source-authority policy, migration authorization, and recovery rehearsal remain open. It is optional parallel governance infrastructure rather than a direct prerequisite for the additive occurrence fact.

## Combined migration order recommendation

1. Preserve this evidence and perform a disposable restore rehearsal.
2. Review/authorize Migration A, apply it atomically, and verify constraints.
3. Review/authorize Migration B separately, apply it atomically, and verify tables/columns.
4. Create a post-migration checkpoint and rerun read-only evidence.
5. Only then design the additive occurrence persistence migration and its rollback.

No step above was executed by SOC-I0.1.
