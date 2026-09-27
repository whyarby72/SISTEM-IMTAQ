# 04 — PostgreSQL Schema Specification

> **Shared Core note (v1.5):** For Student/Guardian/Staff/Organization/Auth-RBAC-Audit shared-foundation implementation, `docs/10_shared_core/` is the authoritative system-wide refinement. This file remains the Academic schema contract for Academic-owned tables.


## Technical baseline
- PostgreSQL.
- One application schema initially.
- UUID PK using `gen_random_uuid()` / `pgcrypto`.
- `btree_gist` available for temporal exclusion where needed.
- `timestamptz` for actual timestamps; app timezone `Asia/Jakarta`.
- Effective intervals use `[from, until)` convention.
- Prefer varchar + CHECK constraints over PostgreSQL ENUM for mutable controlled vocabularies.
- Unknown = NULL/no row; never fake placeholders.
- `ON DELETE RESTRICT` default for business relationships.
- No destructive deletes of historical business facts.
- Multi-step commands use DB transactions.

## Platform tables
### `users`
Technical account identity; role is not a single string column.

### `audit_logs`
Append-only. Must support entity, actor, timestamp, old/new values, reason, version before/after, correlation/request ID and optional correction request.

### `correction_requests`
Generic controlled post-lock/high-risk correction workflow.

## Core tables
### `organizational_units`
Institutional/unit hierarchy reference.

### `students`
Recommended:
- id UUID PK
- student_code UNIQUE NOT NULL — permanent IMTAQ ID
- full_name
- basic active metadata needed for canonical identity
- created_at/updated_at

Do not use name, NIS or NISN as PK.

### `student_identifiers`
- id UUID PK
- student_id FK
- identifier_type: NIS/NISN
- identifier_value
- verification_status: UNVERIFIED/VERIFIED
- record_status: ACTIVE/SUPERSEDED/INVALIDATED
- valid_from/valid_until
- source_reference nullable
- notes nullable
- created_by/created_at

Constraints:
- one active identifier per student/type
- active identifier value uniqueness according to institutional scope
- NISN active uniqueness mandatory

### `student_status_history`
- id
- student_id
- status: ACTIVE/GRADUATED/WITHDRAWN/TRANSFERRED_OUT/DISMISSED/DECEASED
- effective_from/effective_until
- decision_reference nullable
- reason nullable
- audit metadata

Prevent overlapping current status intervals.

### `staff`
Canonical staff master.

### `locations`
Reusable location/room reference.

### `permission_events`
- id
- permission_code
- student_id
- permission_type
- start_at/end_at
- destination nullable
- guardian_reference nullable
- approval_status
- approved_by nullable
- return_status nullable
- notes
- audit fields
Constraint `end_at > start_at`.

### `academic_calendar_events`
- id
- academic_year_id
- event_type: HOLIDAY/NON_TEACHING_DAY/INSTITUTIONAL_EVENT/EXAM_PERIOD/OTHER
- title
- start_at/end_at
- organizational_unit_id nullable
- class_id nullable
- regular_session_policy: ALLOW/BLOCK/REVIEW_REQUIRED
- notes nullable
- workflow_status
- created_by/created_at/updated_by/updated_at

## Academic master/config tables
### `academic_years`
Year identity and active range.

### `semesters`
Belongs to academic year; effective dates.

### `grade_levels`
Academic level master, separate from rombel/section.
- id
- organizational_unit_id nullable/as applicable
- level_code
- display_name
- sequence_no
- status ACTIVE/INACTIVE
Do not encode section A/B/C in this table.

### `classes`
Year-specific class/rombel record. Do not store canonical `homeroom_staff_id`.
- id
- class_code UNIQUE
- academic_year_id
- organizational_unit_id
- grade_level_id
- section_code (configurable data such as A/B/C; not a hard-coded enum)
- display_name
- status
Recommended business uniqueness: `(academic_year_id, organizational_unit_id, grade_level_id, section_code)`.
The same label such as `Kelas 1 A` in a different academic year is a different class record. See `CLASS_MASTER_AND_ENROLLMENT.md`.

### `class_homeroom_assignments`
- id
- class_id
- staff_id
- effective_from/effective_until
- status ACTIVE/ENDED
- assigned_by/assigned_at
- reason nullable
Prevent overlapping primary assignments.

### `student_class_enrollments`
- id
- student_id
- class_id
- effective_from/effective_until
- status
- reason/source metadata
Prevent conflicting overlaps according to institutional rule.

### `subjects`
Canonical subject ID/code/name. Do not encode semester grades in subject master.

### `teaching_assignments`
- id
- assignment_code UNIQUE
- semester_id
- class_id
- subject_id
- teacher_staff_id
- effective_from/effective_until
- workflow_status
- version_no
- audit metadata

### `schedule_rules`
- id
- teaching_assignment_id
- weekday ISO 1–7
- start_time/end_time
- recurrence_type: EVERY_WEEK/WEEK_OF_MONTH/ODD_WEEK/EVEN_WEEK
- location_id nullable
- effective_from/effective_until
- workflow_status/version
Constraint start < end.

### `schedule_rule_week_numbers`
For WEEK_OF_MONTH, values 1–5.

### Schedule conflict / concurrency integrity
Authority: `SCHEDULE_CONFLICT_AND_CONSTRAINT_ENGINE.md`.

- All schedule/session time intervals use `[start,end)` semantics and require `start < end`.
- Rule-level teacher/class conflict checks are service-level because recurrence/effective-date semantics cannot be expressed safely as a simple row constraint.
- Authoritative schedule writes acquire deterministic transaction-scoped locks for affected teacher/class resources (PostgreSQL advisory transaction lock or equivalent), re-query overlaps, then persist atomically.
- `class_sessions` should add a PostgreSQL exclusion constraint using `btree_gist` to prevent overlapping active session ranges for the same `class_id` where feasible.
- Do not denormalize a single teacher FK into `class_sessions`; teacher delivery remains modeled through `session_teacher_participations` and schedule-change semantics.
- Teacher overlap protection therefore combines domain-service validation, transaction serialization and DQ detection.
- Location exclusion remains disabled until location exclusivity policy/configuration is approved.

Illustrative class-session defense (verify exact migration syntax against target PostgreSQL):

```sql
EXCLUDE USING gist (
  class_id WITH =,
  tstzrange(planned_start_at, planned_end_at, '[)') WITH &&
)
WHERE (session_status IN ('PLANNED','CONFIRMED','COMPLETED'));
```

## Academic transaction tables
### `class_sessions`
- id
- session_code UNIQUE
- teaching_assignment_id
- schedule_rule_id nullable
- class_id snapshot/reference
- subject_id snapshot/reference
- location_id nullable
- planned_start_at/planned_end_at
- actual_start_at/actual_end_at nullable
- session_source: SCHEDULED/RESCHEDULED/EXTRA/AD_HOC
- participant_scope: FULL_CLASS/SELECTED_STUDENTS
- session_status: PLANNED/CONFIRMED/COMPLETED/CANCELLED/RESCHEDULED
- rescheduled_from_session_id nullable
- notes nullable
- workflow/version/audit metadata
Recommended unique `(schedule_rule_id, planned_start_at)` when `schedule_rule_id IS NOT NULL`.

### `schedule_changes`
- id
- change_code UNIQUE
- change_type: SUBSTITUTION/SWAP/RESCHEDULE/TIME_CHANGE/CANCELLATION/EXTRA_SESSION
- source_session_id nullable
- related_session_id nullable
- original_teacher_id nullable
- replacement_teacher_id nullable
- new_start_at/new_end_at nullable
- reason
- requested_by/requested_at
- approved_by/approved_at nullable
- applied_by/applied_at nullable
- status: DRAFT/REQUESTED/APPROVED/REJECTED/APPLIED/CANCELLED

### `session_teacher_participations`
- id
- class_session_id
- teacher_staff_id
- role: PRIMARY/SUBSTITUTE
- obligation_type: TEACHING_ASSIGNMENT/REPLACEMENT
- participation_status: EXPECTED/REMOVED
- attendance_status nullable: PRESENT/ABSENT/SICK/IZIN/OTHER
- reason nullable
- schedule_change_id nullable
- checkin_at/checkout_at nullable
- notes nullable
Unique session+teacher; partial unique one EXPECTED PRIMARY recommended.

Teacher attendance workflow is POLICY_PENDING; table structure is DESIGN_LOCKED.

### `session_student_participants`
- id
- class_session_id
- student_id
- participant_basis
- participant_status: EXPECTED/REMOVED
- is_required boolean
- removal_reason nullable
- removed_by/removed_at nullable
Unique session+student.

### `student_attendance`
- id
- session_student_participant_id UNIQUE FK
- attendance_status
- reason_code nullable
- permission_event_id nullable
- arrival_at/departure_at nullable
- notes nullable
- workflow_status: DRAFT/VALIDATED
- version_no >=1
- entered_by/entered_at
- finalized_by/finalized_at nullable
- updated_by/updated_at

Do not duplicate student_id/session_id here unless needed as denormalized immutable support; canonical identity is participant FK.

### `attendance_period_locks`
Current DESIGN_ASSUMPTION grain: one class × calendar month/date range.
- id
- class_id
- period_start/period_end
- locked_by/locked_at
- lock_reason nullable
- created_at
Existence of lock record means locked. Do not mass-update attendance rows to LOCKED.
Actor/timing policy remains POLICY_PENDING.

## Semester grade tables
### `semester_subject_grades`
Canonical grain: Student × Subject × Semester.
- id
- student_id
- semester_id
- subject_id
- score numeric(5,2) nullable only while incomplete workflow permits
- grade_source: DIRECT_ENTRY/IMPORTED/CALCULATED(future)
- source_teaching_assignment_id nullable
- responsible_staff_id nullable
- workflow_status
- version_no
- entered_by/entered_at
- finalized_by/finalized_at nullable
- updated_by/updated_at
Unique `(student_id, semester_id, subject_id)`.
Check `score >= 0 AND score <= 100` when present.

Final grade workflow and grade-period lock persistence grain are POLICY_PENDING.

## Report Card tables
### `report_cards`
Stable identity: Student × Semester × Report Type.

### `report_card_versions`
Immutable publication version; identity/class/semester/source cutoff snapshots; review/approval/publication fields; optional storage key/checksum.

### `report_card_subject_lines`
One subject snapshot per report version; references semester grade ID/version and stores score snapshot.

### `report_card_attendance_lines`
Status/count snapshot per report version.

### `report_card_notes`
Parent-facing/report-specific notes only; support `approved_for_parent_report`.

### `report_card_signatories`
Role/staff/name/title snapshots.

## Transcript tables
### `academic_transcripts`
Stable logical transcript identity.

### `academic_transcript_versions`
Version, scope, cutoff, identity snapshots, review/approval/publication, storage/checksum.

### `academic_transcript_lines`
One snapshot line per official semester-subject grade included in the transcript.

### `academic_transcript_signatories`
Signatory snapshots.

## Shared alert tables
- `alert_rules`
- `alerts`
- `alert_status_history`
- `alert_actions`

## Migration tables
- `data_import_batches`
- `data_import_files`
- `data_import_rows`
- `data_import_row_errors`
- `data_import_value_mappings`
- `data_import_entity_links`

Create legacy-grain tables only when source inventory proves they are required, e.g.:
- `legacy_student_attendance_daily`
- `legacy_student_attendance_summaries`
- `legacy_teacher_attendance_summaries`
- `legacy_class_roster_snapshots`
- `legacy_documents`

## Recommended views
- `v_current_student_status`
- `v_current_student_identifiers`
- `v_current_class_enrollment`
- `v_active_students`
- `v_current_class_homeroom`
- `v_student_academic_history`
- semantic views from file 09.
