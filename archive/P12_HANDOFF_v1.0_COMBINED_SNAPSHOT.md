# SISTEM IMTAQ — Academic Module Codex Handoff v1.0 (Combined)


---

# SISTEM IMTAQ — Academic Module Codex Handoff v1.0

## Purpose
This bundle is the authoritative development handoff for the first production module of SISTEM IMTAQ: Academic. It consolidates planning P1–P11 plus the Architecture Consistency Patch R1.

Baseline source: `IMTAQ CORE ENGINE BASE SOURCE v1.0`.

North Star:

`ONE STUDENT → ONE ID → ONE HISTORY → MANY ACTIVITIES → ONE SOURCE OF TRUTH → MANY REPORTS.`

## Mandatory reading order
Read files `00` through `15` before writing production code.

## Governance status vocabulary
- `DESIGN_LOCKED`: architecture decision approved for development.
- `MANAGEMENT_APPROVED`: formally approved institutional policy/SOP.
- `DESIGN_ASSUMPTION`: safe temporary design assumption.
- `POLICY_PENDING`: institutional decision still required; do not invent a default.
- `FUTURE`: intentionally outside MVP.
- `SUPERSEDED`: old decision; must not be implemented.

## Non-negotiable principles
1. Single Source of Truth.
2. One Student ID.
3. Record Once, Use Many.
4. Transaction Before Report.
5. Validation Before Publication.
6. Clear Data Ownership.
7. Audit Trail and versioning.
8. RBAC + least privilege.
9. Observable Before Scorable.
10. Actionable KPI.
11. Privacy & Security by Design.
12. AI is not a Source of Truth.
13. Human review for sensitive publications/decisions.
14. Education Before Technology.

## Development gate
Do not start feature coding merely from UI assumptions. Follow this order:

`Master Data → Transaction Data → Validation → Data Quality → KPI/Semantic Layer → Reporting → Alert → AI Assistance.`

P13 Sprint Roadmap is intentionally not included yet. If this bundle is given to Codex before P13, Codex may inspect and prepare implementation notes, but must not invent sprint sequence or unresolved policy.


---

# 01 — Product Scope

## Product
The application shell is **SISTEM IMTAQ**, not a standalone attendance app.

Architecture: **modular monolith first**.
- One Laravel-equivalent backend.
- One PostgreSQL database.
- One authentication/RBAC system.
- One audit infrastructure.
- One backup/recovery strategy.

## Academic MVP scope
1. Student/Class academic master references.
2. Teaching assignments.
3. Flexible class-specific scheduling.
4. Academic calendar exceptions.
5. Class session generation.
6. Student attendance.
7. Teacher obligation/participation structure.
8. Schedule changes: substitution, swap, reschedule, cancellation, extra session.
9. Semester final grades: one final grade per Student × Subject × Semester.
10. Academic report card.
11. Academic history.
12. Academic transcript.
13. KPI/reporting semantic layer.
14. Data Quality and alert infrastructure.
15. Historical migration framework.
16. Audit, correction and versioning.

## Explicit MVP exclusions
- Detailed Tugas/Quiz/UTS/UAS assessment engine.
- Remedial engine.
- Competency scoring engine.
- GPA/IP/IPK.
- Student ranking.
- Composite “Academic Score”.
- Composite student risk score.
- AI-based grading or student risk decisions.
- Student profile photo dependency.
- Cross-domain parent development report.

## Shared/Core objects
- students
- student identifiers
- student status history
- staff/users/RBAC
- academic years/semesters
- locations
- academic calendar
- audit/correction
- migration infrastructure
- shared alert infrastructure

## Domain ownership
Academic owns Academic transactions. Do not create `academic_students`; Academic references canonical `students.id`.

Future domains such as Tahfizh and Kesantrian must not be forced into Academic transaction tables.


---

# 02 — Architecture Decision Register

## Core ADRs — DESIGN_LOCKED
- ADR-CORE-001: SISTEM IMTAQ is the product.
- ADR-CORE-002: Modular Monolith First.
- ADR-CORE-003: Shared Student Master.
- ADR-CORE-004: Shared Staff Master.
- ADR-CORE-005: Shared Authentication and RBAC.
- ADR-CORE-006: Domain-Owned Transactions.
- ADR-CORE-007: No Universal Activity God Table.
- ADR-CORE-008: Shared Audit Infrastructure.
- ADR-CORE-009: Cross-Domain Reporting Is Derived.
- ADR-CORE-010: AI Outside Transaction Authority.
- ADR-CORE-011: Administrative Identifiers Are Mutable.
- ADR-CORE-012: Unknown Identifier Is NULL.
- ADR-CORE-013: Identifier Changes Are Audited.
- ADR-CORE-014: Student Code Remains Stable.
- ADR-CORE-015: Student Records Are Never Deleted Because of Exit.
- ADR-CORE-016: Student Status Is Effective-Dated.
- ADR-CORE-017: Exit Ends Future Eligibility, Not Historical Facts.
- ADR-CORE-018: Disciplinary Case and Student Status Have Separate Ownership.
- ADR-CORE-019: Exit Does Not Remove Administrative Identifiers.

## Academic scheduling ADRs — DESIGN_LOCKED
- ADR-ACA-001: Schedule Rule ≠ Class Session.
- ADR-ACA-002: `class_session_id` anchors Academic attendance context.
- ADR-ACA-003: Teacher-session relation uses `session_teacher_participations`.
- ADR-ACA-004: SUBSTITUTION ≠ SWAP.
- ADR-ACA-005: Approved swap is not teacher absence.
- ADR-ACA-006: Permanent schedule changes use effective dating/versioning.
- ADR-ACA-007: Cancelled class does not create student absence.
- ADR-ACA-008: Reports derive from transactions.
- ADR-ACA-009: Student attendance grain = Student × Class Session.
- ADR-ACA-011: Daily/monthly attendance is derived, not a source table.
- ADR-ACA-012: Cancelled/rescheduled source sessions are not attendance opportunities.
- ADR-ACA-013: Permission is reusable context, not automatic attendance.
- ADR-ACA-014-R2: Class-Specific Scheduling.
- ADR-ACA-015-R2: Variable Session Time.
- ADR-ACA-016-R2: Variable Daily Session Count.
- ADR-ACA-017: Extra/Ad-Hoc Academic Sessions supported.
- ADR-ACA-018: Participant Scope supports FULL_CLASS and SELECTED_STUDENTS.

## Attendance ADRs — DESIGN_LOCKED
- ADR-ACA-020: Online Student Attendance Is Entered by Homeroom Teacher.
- ADR-ACA-021-R1: Digital Attendance Is the Primary Transaction.
- ADR-ACA-022-R1: Printed Attendance Is Verification and Backup.
- ADR-ACA-023: Homeroom Teacher Is Online Attendance Inputter.
- ADR-ACA-024-R1: Paper/Digital Discrepancies Require Reconciliation.
- ADR-ACA-026: Homeroom Teacher Is Authoritative Attendance Inputter.
- ADR-ACA-027: Attendance Finalization Performs Deterministic Validation.
- ADR-ACA-028: Routine Attendance Has No Maker-Checker.
- ADR-ACA-029: Academic Admin Monitors Exceptions, Not Every Attendance Row.
- ADR-ACA-030: Post-Lock Correction Requires Elevated Control.

## Consistency Patch ADRs — DESIGN_LOCKED
- CP-001: Academic Calendar is checked before session generation.
- CP-002: Base Engine lifecycle may be composed across transaction, period and publication layers.
- CP-003: `FinalizeStudentAttendance` may set session to COMPLETED as a controlled system side-effect; Wali Kelas has no generic session-status edit permission.
- CP-004: Semester grade identity remains Student × Subject × Semester; teaching assignment provenance is nullable.
- CP-005: Logical correction versioning may use same entity row + `version_no` + append-only audit; published artifacts use immutable physical versions.
- CP-006: Historical unfinished attendance after homeroom handover uses explicit exception authority, not implicit access by the new homeroom teacher.
- CP-007: Permission linkage enforcement remains configurable; approved permission never auto-creates attendance.
- CP-008: Final persistence grain for grade-period lock is POLICY_PENDING.

## Base Engine amendments
The following refine the Base Engine and must be explicit in implementation documentation:
1. NIS/NISN are in effective/auditable identifier records, not identity keys.
2. Current class is derived from effective-dated enrollments.
3. Student status is historical/effective-dated.
4. Homeroom assignment is effective-dated.
5. Academic attendance is class-session based, not a generic god-table.
6. Routine Academic student attendance finalizes under Wali Kelas authority without second human validation.
7. Default lifecycle is a reference model; domain-specific lifecycle composition is permitted when audit, validation, lock and publication controls are preserved.


---

# 03 — Domain Model

## Main relationship chain

`Student → Enrollment → Teaching Assignment → Schedule Rule → Class Session → Participants → Attendance`

Parallel grade/report chain:

`Student + Semester + Subject → Semester Subject Grade → Report Card / Academic History → Transcript`

## Core master grains
- Student: 1 canonical person record.
- Student Identifier: 1 administrative identifier occurrence/version for a student.
- Student Status History: 1 effective status interval.
- Student Class Enrollment: 1 student × class × effective interval.
- Homeroom Assignment: 1 class × staff × effective interval.
- Teaching Assignment: 1 teacher × class × subject × effective period.
- Academic Calendar Event: 1 calendar exception/event × scope × period.

## Academic transaction grains
- Schedule Rule: 1 recurring academic scheduling rule.
- Class Session: 1 actual/planned KBM meeting.
- Schedule Change: 1 approved/reviewed change event.
- Teacher Participation: 1 teacher × class session.
- Student Participant: 1 student × class session expected/removed participant.
- Student Attendance: at most 1 attendance record per expected student participant.
- Semester Subject Grade: 1 student × subject × semester.
- Report Card: 1 student × semester × report type; multiple versions allowed.
- Transcript Line: 1 published transcript version × one official semester-subject grade snapshot.
- Alert: 1 deduplicated active condition occurrence with rule/version/owner/state.

## Student Attendance status vocabulary
- PRESENT
- LATE
- SICK
- PERMISSION
- EXCUSED
- ABSENT

Definitions:
- PRESENT: attended the session.
- LATE: attended but recorded late.
- SICK: absent because of sickness.
- PERMISSION: absence categorized as approved/recognized permission according to policy.
- EXCUSED: academically excused absence that is not SICK/PERMISSION; exact operational examples are POLICY_PENDING.
- ABSENT: explicit unexcused/other absence after required attendance is known; never inferred from missing data.

## Session status vocabulary
- PLANNED
- CONFIRMED
- COMPLETED
- CANCELLED
- RESCHEDULED

## Student participant status
- EXPECTED
- REMOVED

Participant basis:
- CLASS_ENROLLMENT
- SELECTED
- MANUAL_APPROVED

## Teacher participation
Role:
- PRIMARY
- SUBSTITUTE

Attendance status:
- PRESENT
- LATE
- EXCUSED
- ABSENT

Obligation type:
- ORIGINAL_SCHEDULE
- APPROVED_SWAP
- APPROVED_RESCHEDULE
- SUBSTITUTION
- EXTRA_SESSION

`SUBSTITUTE` is a role, not attendance status.

## Report artifacts
Published reports/transcripts are immutable snapshots. Correct source facts first, then regenerate/reissue artifacts.


---

# 04 — PostgreSQL Schema Specification

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

### `classes`
Year-specific class/group record. Do not store canonical `homeroom_staff_id`.

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
- obligation_type
- participation_status: EXPECTED/REMOVED
- attendance_status nullable: PRESENT/LATE/EXCUSED/ABSENT
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


---

# 05 — RBAC and Authorization Contract

Authorization model:

`USER → ROLE → PERMISSION → DATA SCOPE → RESOURCE STATE`

## Role design
Users may have multiple effective-dated roles. Do not use one `users.role` string.

Roles:
- SUPER_ADMIN — technical only; no business superuser semantics.
- KEPALA_UNIT
- WAKA_AKADEMIK
- SEKRETARIAT
- ADMIN_AKADEMIK
- GURU
- VALIDATOR_AKADEMIK — only where relevant; not routine student attendance.
- WALI_KELAS
- AUDITOR

## Scope vocabulary
- SELF
- ASSIGNED_SESSION
- ASSIGNED_CLASS
- HOMEROOM_CLASS
- DEPARTMENT
- UNIT
- INSTITUTION

Dynamic scope must use effective-dated business assignments.

## Permission naming
`module.resource.action`

Actions include:
`view/create/update/submit/finalize/validate/approve/lock/publish/correct/cancel/export/manage`.

## Student attendance
### GURU
- Online student attendance create/update/finalize: DENY.
- May view relevant session/attendance if operationally authorized.
- Paper attendance is operational backup only.

### WALI_KELAS
Scope: `HOMEROOM_CLASS` derived from effective assignment at the session date.
- view
- create/update draft
- bulk create
- finalize
- correct validated attendance while attendance period is OPEN with reason/audit
- no direct edit after lock

### ADMIN_AKADEMIK
- completeness/exception monitoring
- schedule/session administration
- controlled handover completion permission for unresolved historical attendance
- not routine attendance validator
- no unrestricted attendance override

Recommended exception permission:
`academic.student_attendance.handover_complete`

### WAKA_AKADEMIK
Oversight and elevated correction/change authority according to approved policy.

## Homeroom handover rule
New Wali Kelas does not automatically gain historical edit rights over sessions owned by a previous effective assignment. Unfinished historical work uses explicit exception/delegation workflow with audit.

## Grade access
### GURU
May enter/view grades for own teaching responsibility according to final grade workflow policy. Must not edit another teacher's grades without explicit authority.

### WALI_KELAS
May view grades for own homeroom class as needed for report preparation; cannot edit another subject teacher's score through report UI.

### ADMIN/WAKA
Monitoring/officialization/correction permissions depend on grade workflow POLICY_PENDING.

## Report card
- Generate draft: Wali Kelas for own class and/or Admin according to workflow.
- Edit score through report: DENY for everyone.
- Homeroom note: Wali Kelas normal owner.
- Review/approve/publish authority: POLICY_PENDING.
- Parent: PUBLISHED own-child artifact only when parent portal exists.

## Transcript
Generate/review/approve/publish authority: POLICY_PENDING; default design expects Academic-authorized staff, not ordinary homeroom authority.

## Security invariants
1. Backend enforces authorization; hidden buttons are not security.
2. Export is a separate permission.
3. Resource state matters: locked/published artifacts cannot be normal-edited.
4. Field-level data minimization applies; viewing student identity does not automatically reveal NISN, audit data or sensitive notes.
5. Role expiration/removal must take effect without manual data cleanup.
6. Technical admin is not automatically a business approver.


---

# 06 — Workflow and State Machines

## State dimensions must remain separate
### Session business state
`PLANNED / CONFIRMED / COMPLETED / CANCELLED / RESCHEDULED`

### Student attendance workflow
`DRAFT → VALIDATED`

### Attendance period control
`OPEN → LOCKED`

### Report publication
`DRAFT → REVIEWED → APPROVED → PUBLISHED → SUPERSEDED`

Do not collapse these into one status field.

## Student attendance flow
`Class Session → Wali Kelas Online Entry → DRAFT → FinalizeStudentAttendance → deterministic checks → VALIDATED`

`FinalizeStudentAttendance` may set session `COMPLETED` if session is eligible. It must fail for CANCELLED/RESCHEDULED source sessions.

Finalize checks:
- actor is authorized homeroom for class/session date, or controlled exception authority
- session eligible
- all EXPECTED + required participants resolved
- uniqueness
- status controlled
- permission reference consistency when present
- no attendance for REMOVED participant
- participant belongs to correct session/student
- optimistic concurrency version valid

Atomic: all succeed or rollback.

## Open-period correction
Authorized Wali Kelas may correct own VALIDATED attendance during OPEN period:
- reason required
- deterministic revalidation
- `version_no++`
- audit old/new/reason/actor/time
- no second human approval for routine open-period correction

## Post-lock correction
`CORRECTION_REQUEST → REVIEW → APPROVED/REJECTED → targeted apply → version++ → audit`

Do not unlock the whole month.

## Schedule change flows
### Substitution
Original teacher obligation remains; substitute participation added; student session remains one class session.

### Swap
Approved exchange changes effective teacher obligations. Do not mark either teacher absent merely because of the swap.

### Reschedule
Source session becomes RESCHEDULED; replacement is linked. Attendance belongs only to effective replacement.

### Cancellation
Session becomes CANCELLED; no student absence opportunity.

### Extra/Ad-hoc
Recurring extra uses schedule rule; one-off extra can be a session without recurring rule.

## Academic calendar flow
Before generating recurring sessions:
`Schedule Rule → Calendar Check → ALLOW / BLOCK / REVIEW_REQUIRED`

Known calendar BLOCK before generation means no regular session is created; later cancellation of an already planned session uses cancellation workflow.

## Student lifecycle
Effective-dated status change. Exit closes future eligibility/enrollment as appropriate but preserves historical attendance, grades, identifiers and documents.

## Grade flow
Current structure:
`Student + Subject + Semester → Semester Subject Grade → officialization/closure → Report Card / Academic History → Transcript`

Exact grade entry/finalization/validation/lock authority is POLICY_PENDING. Do not invent a maker-checker or direct-finalize policy.

## Report correction
Correct source facts first. If published artifact is affected:
`source corrected → source-changed flag → reissue decision → version n+1 → review/approve/publish → old version SUPERSEDED`.

## Alert lifecycle
`OPEN → ACKNOWLEDGED → IN_PROGRESS → RESOLVED → CLOSED`

Pure deterministic DQ alerts may system-resolve when condition clears. Student early warnings should prefer human resolution.


---

# 07 — Business Rules and Invariants

## Identity
1. Student name is never a primary identifier.
2. `student_code` is permanent canonical IMTAQ identity.
3. NIS/NISN are mutable administrative identifiers.
4. Unknown NIS/NISN = NULL/no record, never placeholder.
5. Identifier correction never creates a new student.
6. Student exit never deletes student history.

## Scheduling
1. Scheduling is class-specific.
2. Session times and daily session counts are variable.
3. Schedule Rule ≠ Class Session.
4. Academic calendar is checked before session generation.
5. Permanent schedule changes close old rule/create new effective version; no historical overwrite.
6. Teacher/class overlap is blocked; location conflict handled by policy.
7. Session generation is idempotent.

## Student attendance
1. Digital attendance is canonical primary transaction.
2. Normal online inputter = Wali Kelas.
3. Paper = verification/backup only.
4. Missing attendance ≠ ABSENT.
5. CANCELLED session creates no absence opportunity.
6. RESCHEDULED source creates no attendance opportunity.
7. Attendance grain = expected Student × Class Session.
8. Routine Finalize performs system validation and yields VALIDATED without admin revalidation.
9. Open-period correction requires reason + audit, not second approval.
10. Post-lock edits require controlled correction; no whole-period unlock.
11. Permission event never auto-creates attendance.
12. If `permission_event_id` exists, student/reference consistency is mandatory.
13. New Wali Kelas does not implicitly gain historical edit rights of former Wali assignment.

## Teacher participation
1. SUBSTITUTE is a role, not an attendance status.
2. Approved swap is not teacher absence.
3. Substitution does not erase original teacher obligation.
4. NULL teacher attendance ≠ ABSENT; missing teacher attendance is DQ.
5. Final teacher attendance workflow remains POLICY_PENDING.

## Grades
1. MVP grade grain = Student × Subject × Semester.
2. No Tugas/Quiz/UTS/UAS in MVP.
3. Missing grade ≠ zero.
4. Explicit zero is valid only if official factual score is zero.
5. Score range 0–100.
6. One current grade record per Student×Subject×Semester.
7. Teaching assignment provenance may be NULL; do not fabricate provenance for transferred students.
8. Grade correction preserves old/new values, version and audit.
9. Report and transcript do not become grade sources.
10. No KKM/mastery/remedial/ranking/GPA without approved policy and matching transaction model.

## Reports/transcripts
1. Report card = publication artifact, not grade source.
2. Transcript = versioned publication artifact from semester grades, not from report PDF.
3. Published artifact versions are immutable.
4. Correct source, then regenerate/reissue.
5. Published identity/grade/signatory values are snapshots.
6. Internal notes are not automatically parent-facing.
7. Parent only receives PUBLISHED own-child artifacts when portal is implemented.

## KPI
1. One Metric, One Definition.
2. Official KPI uses official eligible transactions.
3. Missing transactions appear as Data Quality, not negative facts.
4. Attendance denominator = eligible Student × Session opportunities.
5. Aggregates use transaction denominator; avoid average-of-averages with unequal denominators.
6. Physical Presence = PRESENT + LATE over eligible opportunities.
7. Generic Attendance % is not official until policy-approved.
8. No composite Academic Score.
9. Metric threshold ≠ metric formula.

## Alerts
1. Alert derives from fact; never replaces fact.
2. DQ, operational exception and student early warning are separate.
3. Alert is not automatic punishment/diagnosis.
4. Every alert has owner, severity, status, due/resolution path.
5. Rules are transparent and versioned.
6. Deduplicate active identical conditions.
7. Closed alerts are retained.
8. No composite student risk score.
9. Student warning thresholds remain inactive until approved.

## Migration
1. Preserve original legacy grain.
2. Never manufacture historical precision.
3. Schedule context alone does not prove a historical session occurred.
4. Missing legacy attendance ≠ ABSENT.
5. Missing legacy grade ≠ zero.
6. Names do not auto-match identity.
7. Original source files retained/checksummed.
8. Every imported canonical record traceable to source batch/file/row.
9. Dry run + reconciliation before acceptance.
10. Import must be idempotent.


---

# 08 — Academic Operational SOP Contract

## Setup
### SOP-ACA-001 Academic year setup
Create academic year, semester, classes, subjects, staff references, homeroom assignments and teaching assignments.

### SOP-ACA-002 Student placement
Use effective-dated `student_class_enrollments`. No current-class overwrite.

### SOP-ACA-003 Homeroom assignment
Effective-dated. Authorization follows assignment period. Historical unfinished work uses controlled handover exception.

### SOP-ACA-004 Teaching assignment
Create teacher × class × subject × effective period.

### SOP-ACA-005 Flexible schedule
Class-specific variable start/end/duration. Detect teacher/class conflicts.

### SOP-ACA-006 WEEK_OF_MONTH
Support week numbers 1–5.

### SOP-ACA-007 Academic calendar
Maintain holidays/non-teaching/institutional exceptions. Session generator must check calendar policy.

### SOP-ACA-008 Session generation
Generate idempotently from published/effective schedule rules and calendar.

### SOP-ACA-009 Expected roster
For FULL_CLASS snapshot eligible active enrollments as of session date. Preserve snapshot/history.

## Student attendance
### SOP-ACA-010 Online attendance
Wali Kelas enters attendance online.

### SOP-ACA-011 Save draft
Draft may be incomplete; incomplete does not mean untrusted/absent.

### SOP-ACA-012 Finalize
Wali Kelas finalizes. System performs deterministic checks. On success attendance becomes VALIDATED and eligible session may become COMPLETED.

### SOP-ACA-013 Paper backup
Guru/Ketua Kelas may use paper as verification/backup, especially during system outage. Paper is not publication/report source.

### SOP-ACA-014 Discrepancy
Clarify discrepancy. If period OPEN, authorized correction + reason + audit. If LOCKED, correction workflow.

### SOP-ACA-015 Permission context
Permission supports attendance context; it does not auto-create attendance.

### SOP-ACA-016 Open correction
Wali Kelas corrects own open-period validated attendance with reason/audit.

### SOP-ACA-017 Completeness monitoring
Admin monitors missing/unfinalized sessions and exceptions; no routine row-by-row revalidation.

### SOP-ACA-018 Period close
Recommended DESIGN_ASSUMPTION: class × calendar month/date range. Lock only when prerequisites pass.

### SOP-ACA-019 After lock
Normal edit denied.

### SOP-ACA-020 Post-lock correction
Request/review/targeted apply; do not unlock whole month.

## Schedule exceptions
### SOP-ACA-021 Substitution
Retain original obligation; add substitute role; preserve audit.

### SOP-ACA-022 Swap
Approved exchange of effective obligations; not absence.

### SOP-ACA-023 Reschedule
Source RESCHEDULED + linked replacement.

### SOP-ACA-024 Cancellation
CANCELLED; no student absence opportunity.

### SOP-ACA-025 Extra/ad-hoc
Support recurring extra rule or one-off session.

## Student lifecycle / identifier
### SOP-ACA-026 Student exit
Stop future eligibility, preserve history.

### SOP-ACA-027 Unknown NIS/NISN
Allowed; NULL/no row.

### SOP-ACA-028 Identifier correction
Correct same student record, preserve prior value/audit.

### SOP-ACA-029 Class transfer
Close old enrollment, open new effective enrollment; preserve historical class context.

## Grades/reports
### SOP-ACA-030 Semester grade
One final score per Student×Subject×Semester. Grade workflow authority is POLICY_PENDING.

### SOP-ACA-031 Report generation
Generate from official semester grades + locked attendance periods + report note/identity snapshots.

### SOP-ACA-032 Report correction
Never edit grade inside report. Correct source and regenerate/reissue.

### SOP-ACA-033 Transcript
Generate from official locked semester-grade history, never from report PDF.

## Teacher attendance
Data structure exists, but inputter/finalizer/self-confirm/late threshold remain POLICY_PENDING. Do not activate official teacher-attendance KPI until policy is approved.


---

# 09 — Academic KPI Dictionary v1.0

All metrics must have one definition, source, eligibility and version.

## Attendance metrics
### `ACA_ATT_OPPORTUNITY_COUNT`
Count of `EXPECTED + required` student participants on effective `COMPLETED` sessions. Exclude CANCELLED, RESCHEDULED source, REMOVED/non-required participants and post-exit ineligible participants.

### `ACA_ATT_PRESENT_COUNT`
Validated PRESENT attendance among eligible opportunities.

### `ACA_ATT_LATE_COUNT`
Validated LATE attendance.

### `ACA_ATT_SICK_COUNT`
Validated SICK attendance.

### `ACA_ATT_PERMISSION_COUNT`
Validated PERMISSION attendance.

### `ACA_ATT_EXCUSED_COUNT`
Validated EXCUSED attendance.

### `ACA_ATT_ABSENT_COUNT`
Validated explicit ABSENT attendance only.

### `ACA_ATT_PHYSICAL_PRESENCE_PCT`
`(PRESENT + LATE) / eligible attendance opportunities * 100`.
Official use requires completeness 100% and relevant period LOCKED.

### `ACA_ATT_UNEXCUSED_ABSENCE_PCT`
`ABSENT / eligible attendance opportunities * 100`.

### `ACA_ATT_COMPLETENESS_PCT`
`validated attendance records / eligible attendance opportunities * 100`.
This is a Data Quality/operational metric, not a student-performance metric.

## Session metrics
### `ACA_SESSION_COMPLETED_COUNT`
Effective completed sessions.

### `ACA_SESSION_CANCELLED_COUNT`
Effective cancelled sessions.

### `ACA_SESSION_EXTRA_COUNT`
Completed/planned extra sessions according to selected filter.

### `ACA_SESSION_COMPLETION_PCT`
`completed effective sessions / (completed + cancelled effective sessions) * 100`.
Do not double-count rescheduled source sessions.

## Grade metrics
### `ACA_GRADE_COMPLETENESS_PCT`
`official/finalized semester grades / expected Student×Subject grade facts * 100`.
Expected subjects derive from eligible enrollments + teaching assignments, collapsed to distinct subject per student/semester.

### `ACA_STUDENT_SEMESTER_MEAN`
Simple arithmetic mean of all official subject grades for the student in the semester. Official only when expected grades are complete. No subject weighting unless later approved.

### `ACA_SUBJECT_MEAN_SCORE`
Sum official subject scores / count eligible official subject grades for selected scope.

### `ACA_CLASS_MEAN_SCORE`
Sum all eligible official grade facts / count all eligible grade facts for class/scope. Do not average student averages when denominators differ.

### `ACA_SUBJECT_SCORE_CHANGE`
Current official score minus previous official score for the same student+subject across comparable semesters.

## Operational metrics
### `ACA_UNFINALIZED_ATTENDANCE_SESSION_COUNT`
Completed/eligible sessions whose student attendance is not finalized/validated.

### `ACA_REPORT_CARD_READINESS_PCT`
Eligible students satisfying all configured report readiness requirements / eligible students requiring report.

### `ACA_REPORT_PUBLISHED_COUNT`
Count published academic report cards in scope.

### `ACA_CRITICAL_DQ_OPEN_COUNT`
Count active CRITICAL DQ/integrity alerts in scope.

### `ACA_POST_LOCK_CORRECTION_COUNT`
Count approved/applied post-lock corrections in scope.

## Metrics explicitly not official in MVP
- Generic `ATTENDANCE_PCT` — POLICY_PENDING.
- SUBJECT_MASTERY — no approved threshold.
- REMEDIAL_COUNT — no remedial transaction engine.
- ASSIGNMENT_COMPLETION — no detailed assessment transactions.
- GPA/IP/IPK.
- Student/Class ranking.
- Teacher attendance percentage until workflow is approved.
- Composite Academic Score.

## Semantic layer requirements
Recommended views/services:
- `v_academic_session_fact`
- `v_student_attendance_fact`
- `v_semester_grade_fact`
- `v_attendance_completeness`
- `v_grade_completeness`
- `v_report_card_readiness`
- `v_student_academic_history`

Dashboard, export and API must use the same metric service/definition.


---

# 10 — Data Quality & Alert Rule Contract

## Categories
- DATA_QUALITY
- OPERATIONAL_EXCEPTION
- STUDENT_EARLY_WARNING

## Alert lifecycle
`OPEN → ACKNOWLEDGED → IN_PROGRESS → RESOLVED → CLOSED`

## Core alert fields
- rule code/version
- module/category/severity
- entity references
- optional student/class/session refs
- title/summary
- evidence snapshot
- triggered value/threshold snapshot when applicable
- first/last detected
- owner
- due_at
- status
- resolution metadata
- dedup_key

## Active MVP deterministic rules
### `ACA_DQ_MISSING_ATTENDANCE`
Completed effective session + required EXPECTED participant + no attendance record.
Owner: effective Wali Kelas; exception routing if assignment ended.
Blocks attendance finalization/period lock.

### `ACA_OP_ATTENDANCE_NOT_FINALIZED`
Attendance remains DRAFT/unfinalized for an eligible session.
Owner: Wali Kelas.

### `ACA_DQ_CANCELLED_HAS_ATTENDANCE`
Cancelled session has active attendance facts. HIGH.

### `ACA_DQ_INELIGIBLE_PARTICIPANT`
Participant is EXPECTED after student is no longer eligible. HIGH.

### `ACA_DQ_MISSING_HOMEROOM`
Active class lacks effective homeroom assignment. HIGH operational dependency.

### `ACA_DQ_SCHEDULE_CONFLICT`
Effective teacher/class overlap. HIGH.

### `ACA_OP_APPROVED_CHANGE_NOT_APPLIED`
Approved schedule change not applied when required.

### `ACA_DQ_MISSING_GRADE`
Expected Student×Subject×Semester has no official grade.
Owner normally responsible teacher; visibility Admin/Waka.

### `ACA_DQ_GRADE_OUT_OF_RANGE`
Score outside 0–100; should normally be blocked at DB/service boundary.

### `ACA_DQ_GRADE_PERIOD_INCOMPLETE`
Expected grade set incomplete; block official closure/publication.

### `ACA_DQ_PUBLISHED_SOURCE_CHANGED`
Source fact affecting a published report/transcript changed after publication; requires reissue review, not automatic reissue.

### `ACA_SEC_POST_LOCK_MUTATION`
Unauthorized direct mutation attempt against locked data; block + audit, HIGH/CRITICAL by context.

### `ACA_DQ_CALENDAR_SESSION_INCONSISTENCY`
Session exists/was generated in conflict with a BLOCK calendar event without approved exception.

## Inactive until management approval
- REPEATED_UNEXCUSED_ABSENCE
- REPEATED_LATENESS
- LOW_PHYSICAL_PRESENCE
- ATTENDANCE_DECLINE
- grade trend warning
- SLA overdue escalations

Codex must not invent thresholds.

## Deduplication
Same active condition should update `last_detected_at`, not create repeated alerts. A later recurrence after closure becomes a new alert occurrence.

## Resolution
Deterministic DQ may auto-resolve when condition clears. Student warnings should prefer human review. Alert resolution never rewrites underlying facts.


---

# 11 — Historical Migration Contract

## Principle
Preserve what is known, preserve what is unknown, never manufacture historical precision.

## Migration flow
`Source Inventory → Raw Preservation → Profile → Staging → Identity Mapping → Value Mapping → Validation → Dry Run → Reconciliation → Human Approval → Import → Post-Import Reconciliation → Close Batch`

## Source file rules
- Retain original source file.
- Store filename, checksum, owner/source period, received_at.
- Do not clean source in place; transform in staging.

## Legacy granularity vocabulary
- SESSION_LEVEL
- DAILY_LEVEL
- MONTHLY_SUMMARY
- SEMESTER_FINAL
- SCHEDULE_RULE
- ROSTER_SNAPSHOT
- DOCUMENT_ONLY
- UNKNOWN

## Attendance migration
### Session-level source
May map to canonical class sessions/participants/attendance only when the source actually supports session identity/context.

### Daily source
Store/read through a legacy daily layer if required. Do not expand one daily status into several session attendance facts.

### Monthly summary
Store as legacy summary. Do not fabricate dates/sessions.

Known schedule alone is not proof a historical session occurred.

## Teacher attendance migration
Monthly/aggregate presence counts do not become session-level teacher participation attendance unless source evidence supports exact sessions.

## Semester grade migration
Final Student×Subject×Semester grade may map directly to canonical `semester_subject_grades` with `grade_source=IMPORTED` after identity/subject/semester validation.

Missing grade never becomes zero.

## Identity matching priority
1. Canonical Student_ID if present.
2. Verified institutional student code.
3. Exact verified NISN.
4. Exact verified NIS within valid scope.
5. Approved reusable legacy mapping.
6. Human review.

Name/fuzzy match may suggest candidates but must not auto-merge.

## Migration infrastructure requirements
- dry run creates no canonical rows
- every source row accounted for
- blocking vs warning error classification
- quarantine supported
- idempotent rerun
- canonical target traceable to source row/file/batch
- pre-acceptance rollback only under controlled dependency-safe conditions
- post-acceptance correction/enrichment uses a new audited batch or normal correction workflow

## Reconciliation equation
`Source Total = Imported + Rejected + Quarantined + Duplicate + Explicitly Excluded`

If totals do not reconcile, batch cannot close.

## Cutover
Go-live master/current-state migration and historical enrichment are separate workstreams. Do not maintain legacy spreadsheets as a permanent second Source of Truth after cutover.


---

# 12 — UAT and Verification Matrix

## Acceptance gate
- All P0 tests PASS.
- All in-scope P1 tests PASS.
- No open Critical/High integrity defect.
- Negative RBAC tests completed.
- Correction/audit proven.
- Reports/KPI reconciled to transactions.
- Migration batches used in production reconciled.
- Backup restore tested before production.
- POLICY_PENDING features remain disabled/unassumed.
- Business-owner sign-off retained.

## P0/P1 test catalogue
### Core identity/lifecycle
- CORE-001 unique permanent Student_ID.
- CORE-002 duplicate names allowed without identity merge.
- CORE-003 unknown NIS/NISN does not create placeholder.
- CORE-004 duplicate active NISN blocked.
- CORE-005 identifier correction preserves same student + audit.
- LIFE-001 effective-dated class transfer preserves history.
- LIFE-002 exit stops future eligibility, not historical facts.
- LIFE-003 historical attendance/grades remain after dismissal/graduation.

### Homeroom/RBAC
- HR-001 Wali X-A allowed own class attendance.
- HR-002 Wali X-A denied X-B attendance.
- HR-003 overlapping primary homeroom assignment blocked.
- HR-004 new Wali denied normal historical edit of prior Wali sessions.
- HR-005 controlled handover completion requires exception permission/reason/audit.

### Calendar/scheduling/session
- CAL-001 BLOCK calendar event prevents recurring session generation.
- CAL-002 REVIEW_REQUIRED produces exception path.
- SCH-001 variable time/daily session count supported.
- SCH-002 teacher/class overlap blocked.
- SCH-003 permanent changes preserve historical rule.
- SES-001 generator retry is idempotent.
- SES-002 roster snapshot preserves historical eligibility.
- SES-003 student exit removes only future eligibility.

### Student attendance
- ATT-001 Guru normal online create/finalize denied.
- ATT-002 Wali can save DRAFT.
- ATT-003 one missing required participant blocks finalize.
- ATT-004 missing record never becomes ABSENT.
- ATT-005 successful Finalize → attendance VALIDATED.
- ATT-006 no routine Admin revalidation step.
- ATT-007 Finalize may set eligible session COMPLETED.
- ATT-008 cancelled/rescheduled source cannot finalize normal attendance.
- ATT-009 duplicate attendance blocked.
- ATT-010 permission reference mismatch blocked when reference present.

### Paper
- PAPER-001 paper absent does not block valid online finalization.
- PAPER-002 outage paper workflow can later be entered digitally by Wali.
- PAPER-003 discrepancy does not auto-overwrite digital/source.

### Lock/correction
- LOCK-001 incomplete period cannot lock.
- LOCK-002 lock is scoped; does not lock unrelated class.
- LOCK-003 Wali direct edit after lock denied.
- COR-001 open correction requires reason + version/audit.
- COR-002 post-lock direct update denied.
- COR-003 approved targeted correction does not unlock entire month.
- COR-004 rejected correction leaves canonical data unchanged.

### Schedule exceptions
- SUB-001 substitution retains original obligation + adds substitute.
- SWAP-001 approved swap does not mark false absence.
- RES-001 source rescheduled + replacement linked; no double attendance opportunity.
- CAN-001 cancelled session creates no student absence.
- EXT-001 selected-student extra session does not mark others absent.

### Grades
- GRD-001 one Student×Subject×Semester grade.
- GRD-002 duplicate final grade blocked.
- GRD-003 missing grade ≠ zero.
- GRD-004 explicit zero accepted if factual.
- GRD-005 score outside 0–100 blocked.
- GRD-006 no Tugas/Quiz/UTS/UAS required in MVP.
- GRD-007 transfer with same subject still expects one final semester grade.
- GRD-008 ambiguous teaching-assignment provenance does not fabricate FK.
- Grade workflow/lock actor tests = BLOCKED_BY_POLICY until decided.

### Report/transcript
- RPT-001 missing expected grade blocks official report.
- RPT-002 attendance periods not locked block official publication.
- RPT-003 report UI cannot edit grade source.
- RPT-004 published snapshot remains unchanged after later source correction.
- RPT-005 reissue creates v2; v1 SUPERSEDED, not deleted.
- TRN-001 repeated subject across semesters remains separate history facts.
- TRN-002 transcript reads semester grades, not report PDF.
- TRN-003 published transcript snapshot immutable.

### KPI
Synthetic attendance dataset: 100 opportunities; PRESENT80/LATE10/SICK4/PERMISSION3/EXCUSED1/ABSENT2.
Expected Physical Presence=90%, Unexcused Absence=2%, completeness=100%.
- KPI-001 cancelled sessions do not change attendance denominator.
- KPI-002 rescheduled source not double-counted.
- KPI-003 missing attendance reduces completeness but does not increase absence.
- KPI-004 average-of-averages with unequal denominators is prohibited; aggregate from transaction grain.
- KPI-005 missing expected grade prevents official aggregate from being treated final.

### Alerts
- DQ-001 missing attendance creates alert.
- DQ-002 clearing deterministic condition resolves alert.
- DQ-003 repeated evaluation does not create duplicate active alerts.
- DQ-004 recurrence after closure creates new occurrence.
- DQ-005 inactive student-warning rule produces no warning even if raw values are high.

### Migration
- MIG-001 checksum/raw source retained.
- MIG-002 names without stable ID do not auto-match.
- MIG-003 daily attendance remains daily; known schedule does not fabricate sessions.
- MIG-004 monthly teacher attendance count does not fabricate session facts.
- MIG-005 semester final grade may import canonically with provenance.
- MIG-006 dry run creates no canonical records.
- MIG-007 rerun is idempotent.
- MIG-008 all source rows reconcile exactly.
- MIG-009 imported target traceable to row/file/batch.

### Concurrency/atomicity
- CON-001 optimistic concurrency rejects stale version save.
- CON-002 double-finalize yields one logical finalization.
- CON-003 schedule generator retry yields no duplicate.
- CON-004 schedule change apply retry is idempotent.
- CON-005 multi-step student status change is atomic.

## Production technical gate
Backup must be restored in a test environment and core records verified. A backup that has never been restored is not accepted as a recovery strategy.


---

# 13 — Policy Pending Register

Codex must not choose values or workflows for these items.

## Attendance
- POL-SOP-001 attendance completion SLA.
- POL-SOP-002 monthly attendance lock timing.
- POL-SOP-003 normal attendance lock actor.
- POL-SOP-004 final post-lock attendance correction approver.
- POL-SOP-013 student late threshold.
- POL-SOP-014 partial permission treatment.

## Teacher attendance / schedule governance
- POL-SOP-005 substitution approver.
- POL-SOP-006 swap approver.
- POL-SOP-007 reschedule approver.
- POL-SOP-008 cancellation approver.
- POL-SOP-009 extra session approver.
- POL-SOP-010 teacher attendance inputter.
- POL-SOP-011 teacher self-confirm allowed?
- POL-SOP-012 teacher late threshold.

## Grade workflow — highest remaining policy blocker
- Who enters semester grade?
- Does teacher finalization make it official or is second human validation required?
- Who may correct a finalized grade before closure?
- What is the grade officialization/closure state machine?
- What is the final persistence grain of grade-period locks?
- Who locks/closes grades?
- Who approves post-close grade corrections?

## Permission
- POL-PERM-001 must PERMISSION attendance always reference `permission_event_id`?
- POL-PERM-002 minimum time overlap/applicability rule.
- POL-PERM-003 permission owner/approver.
- Operational examples/boundary for EXCUSED.

## Report Card
- POL-REPORT-001 mid-semester exit report policy.
- POL-REPORT-002 reporting class after mid-semester transfer.
- POL-REPORT-003 final reviewer.
- POL-REPORT-004 publication approver.
- POL-REPORT-005 signatories.
- POL-REPORT-006 homeroom note mandatory/optional.
- POL-REPORT-007 whether EXCUSED displayed separately.
- POL-REPORT-008 decimal display.
- POL-REPORT-009 semester mean shown or not.
- POL-REPORT-010 identifier change reissue rule.
- POL-REPORT-011 official report numbering.
- POL-REPORT-012 physical/digital signature policy.

## Transcript
- retake/repeated-subject treatment.
- eligibility for withdrawn/dismissed student transcript.
- partial transcript for active students.
- generation/review/approval/publish roles.
- signatories.
- class display per semester.
- average display, if any.
- decimal display.
- reissue rules.
- document numbering.

## KPI/Early Warning
- generic Attendance % definition, if needed.
- treatment of LATE/SICK/PERMISSION/EXCUSED in generic attendance.
- Early Warning absence threshold/window.
- repeated lateness threshold/window.
- low physical presence threshold and minimum opportunities.
- alert SLA/escalation cadence.
- teacher attendance KPI activation.
- management reporting cadence.
- parent-facing metric list.

## Migration
- official go-live/cutover date.
- migration approver.
- partial batch acceptance policy.
- raw/staging retention period.
- human sample percentage.
- legacy daily attendance use in reports.
- legacy attendance parent visibility.
- historical teacher summary treatment.
- legacy document retention/use.
- rollback authority.
- unresolved ambiguous identity handling.
- priority when two legacy sources conflict.

## Policy handling rule
Until a policy is approved:
- feature remains disabled, constrained to non-official preview, or represented by an interface/configuration placeholder;
- do not invent thresholds, approvers, maker-checker, locks, labels or publication authority;
- corresponding UAT may be `BLOCKED_BY_POLICY`.


---

# 14 — MVP / Future / Superseded Register

## MVP — build when sprint plan authorizes
- canonical student/staff/class/subject/semester references
- student identifiers/status/enrollment history
- homeroom assignments
- teaching assignments
- academic calendar
- flexible schedule rules
- session generation
- schedule changes
- student session participants
- student attendance + open correction + period lock architecture
- teacher participation structure
- semester final grade structure
- report card versioning
- academic history and transcript versioning
- shared audit/correction
- KPI semantic definitions
- DQ/shared alert infrastructure
- migration infrastructure
- RBAC/least privilege

## FUTURE — do not build as MVP dependency
- detailed assessment engine: Tugas/Quiz/UTS/UAS/practical/oral etc.
- configurable grading schemes/weights.
- remedial attempts/engine.
- competency-based assessment engine.
- GPA/IP/IPK.
- rankings.
- full cross-domain IMTAQ Parent Development Report.
- Student 360 cross-domain UI beyond Academic needs.
- AI report drafting/summarization/queries.
- predictive models.
- student profile photo/file storage.
- digital signature graphics.
- complex data warehouse/streaming architecture.

## SUPERSEDED — DO NOT IMPLEMENT
- paper attendance as primary Source of Truth.
- mandatory paper-to-digital transcription batch workflow.
- mandatory `attendance_entry_batches`.
- Guru as normal online student-attendance inputter.
- routine Academic Admin validation of every student attendance row.
- `classes.homeroom_staff_id` as canonical homeroom source.
- `students.class_id_current` as canonical class source.
- `students.status` as sole lifecycle source.
- NIS/NISN as student identity/primary key.
- missing attendance auto-converted to ABSENT.
- cancelled session mass absence.
- fixed institution-wide academic time slots or fixed daily session count.
- Tugas/Quiz/UTS/UAS fields in the MVP grade table.
- grading weights/remedial as MVP.
- automatic KKM/mastery classification without policy.
- generic Attendance % with developer-invented formula.
- developer-invented Early Warning thresholds.
- composite Academic Score.
- composite Student Risk Score.
- Report Card as a grade-entry surface.
- Report PDF as Transcript source.


---

# 15 — Codex Build Instructions

## Mission
Implement the Academic module of SISTEM IMTAQ faithfully to this bundle and IMTAQ CORE ENGINE principles. Optimize for data integrity, auditability, simple operations for asatidz/Wali Kelas, and long-term extensibility.

## Hard rules
1. Do not invent business policy.
2. Read `13_POLICY_PENDING_REGISTER.md` before implementing any approval, threshold, lock or officialization path.
3. Never implement anything listed under `SUPERSEDED`.
4. Never make UI behavior the only security control.
5. No destructive deletion of historical business facts.
6. Use explicit domain commands/services, not pure generic CRUD controllers for stateful workflows.
7. Use database constraints in addition to service validation.
8. Make retryable commands idempotent.
9. Use optimistic concurrency (`version_no`) for editable transactional entities.
10. Write automated P0 tests before considering a workflow complete.
11. Reports, exports and dashboards consume canonical facts/semantic services; they do not maintain parallel formulas or values.
12. AI must not be used in MVP transaction authority.

## Required domain commands/services
### Core/identity
- AddStudentIdentifier
- CorrectStudentIdentifier
- ApplyStudentStatusChange

### Schedule/session
- GenerateClassSessions
- RequestScheduleChange
- ApproveScheduleChange
- ApplySubstitution
- ApplySwap
- ApplyReschedule
- ApplyCancellation
- CreateExtraSession
- EvaluateAcademicCalendarForSchedule

### Attendance
- SaveStudentAttendanceDraft
- FinalizeStudentAttendance
- CorrectOpenPeriodAttendance
- LockAttendancePeriod
- RequestLockedAttendanceCorrection
- ApproveAttendanceCorrection
- ApplyAttendanceCorrection
- CompleteHistoricalAttendanceHandover

### Grade
- SaveSemesterSubjectGradeDraft / equivalent structure
- officialization/finalize service MUST remain behind POLICY_PENDING workflow contract until policy is supplied
- CorrectSemesterSubjectGrade with audit/version rules

### Report/Transcript
- CheckReportCardReadiness
- GenerateStudentReportCardDraft
- GenerateClassReportCardDrafts
- AddHomeroomReportNote
- ReviewReportCard
- ApproveReportCard
- PublishReportCard
- RenderReportCardPdf
- GenerateCorrectedReportVersion
- GetStudentAcademicHistory
- CheckTranscriptReadiness
- GenerateTranscriptDraft
- ReviewTranscript
- ApproveTranscript
- PublishTranscript
- RenderTranscriptPdf
- GenerateCorrectedTranscriptVersion

### KPI/alert
- CalculateAttendanceMetrics
- CalculateSessionMetrics
- CalculateSemesterGradeMetrics
- GetAttendanceCompleteness
- GetGradeCompleteness
- GetReportCardReadiness
- EvaluateAlertRules

### Migration
- CreateImportBatch
- ProfileImport
- MapImportRows
- ValidateImportBatch
- DryRunImport
- ExecuteImport
- ReconcileImportBatch
- CloseImportBatch

## Transaction boundaries
Commands that update multiple business records must be atomic. Examples:
- Finalize attendance.
- Apply schedule change.
- Student exit/lifecycle update.
- Post-lock correction apply.
- Import execution unit.

## Event/job guidance
Use Laravel events/jobs; do not introduce Kafka/RabbitMQ in MVP.
Examples:
- AttendanceFinalized → reevaluate DQ/completeness.
- StudentStatusChanged → future-participant eligibility evaluation.
- SemesterGradeChanged → grade/report readiness evaluation.
- ScheduleChangeApplied → session/teacher participation consistency checks.

## UI philosophy
Start from operational decision/task, not database table screens.
Examples:
- Wali Kelas: “today’s class sessions → attendance → finalize → exceptions”.
- Admin: completeness/conflicts/corrections queue.
- Waka: class-level exception/management view.

Avoid exposing internal database state names when a clearer operational label exists, but backend state must remain exact.

## Development stop conditions
Stop and ask for policy rather than guessing when:
- teacher attendance official workflow is required;
- semester-grade finalization/lock authority is required;
- permission must be enforced as mandatory reference;
- report/transcript final approvers/signatories are required;
- Early Warning thresholds/SLA are required;
- any new scoring/ranking/mastery rule is proposed.

## Definition of Done for a feature
A feature is not done until it has:
- business process/use case
- schema/constraints
- authorization/scope
- validation and error handling
- audit/versioning where relevant
- DQ behavior
- automated tests
- UAT traceability
- privacy review
- backup/recovery impact where relevant
- documentation update


---

# MASTER PROMPT FOR CODEX

```text
SISTEM IMTAQ — ACADEMIC MODULE CODEX HANDOFF v1.0

You are implementing the Academic module inside the wider SISTEM IMTAQ product.

Before writing production code, read every file in this handoff bundle in numeric order from 00_READ_ME_FIRST.md through 15_CODEX_BUILD_INSTRUCTIONS.md. Treat the bundle as the current design contract and IMTAQ CORE ENGINE v1.0 as the constitutional baseline.

North Star:
ONE STUDENT → ONE ID → ONE HISTORY → MANY ACTIVITIES → ONE SOURCE OF TRUTH → MANY REPORTS.

Critical behavior:
- Modular monolith + PostgreSQL.
- One canonical student master; never module-specific student masters.
- Academic scheduling is class-specific with variable session times and variable daily session counts.
- Schedule Rule is not Class Session.
- Academic Calendar must be checked before recurring session generation.
- Student attendance grain is Student × Class Session through session_student_participants.
- Wali Kelas is the normal authoritative online student-attendance inputter.
- Paper attendance is verification/backup only.
- Missing attendance is never ABSENT.
- FinalizeStudentAttendance performs deterministic validation and yields VALIDATED attendance; there is no normal Admin row-by-row revalidation.
- Cancellation creates no student absence opportunity.
- Swap is not substitution and approved swap is not teacher absence.
- Open-period corrections require reason/version/audit; locked-period corrections require controlled request/review/apply without unlocking the whole period.
- Student identifiers and status/class assignments are effective/auditable; no historical deletion.
- Academic MVP grade = one final score per Student × Subject × Semester. No Tugas/Quiz/UTS/UAS/remedial/KKM/ranking/GPA in MVP.
- Teaching-assignment provenance on semester grade may be null; do not fabricate provenance when a student changes class mid-semester.
- Report Card and Transcript are immutable versioned publication artifacts; neither is a grade source.
- KPIs use canonical transaction facts and one semantic definition. No composite Academic Score.
- Data Quality, operational exceptions and student early warning are separate. Alert ≠ punishment. No student risk score.
- Migration preserves source granularity; never fabricate historical sessions from daily/monthly summaries.
- AI is not transaction authority.

Governance status meanings:
DESIGN_LOCKED = implementable architecture.
MANAGEMENT_APPROVED = institutionally approved policy.
POLICY_PENDING = do not guess.
FUTURE = do not make MVP dependency.
SUPERSEDED = do not implement.

IMPORTANT: Several official workflows are still POLICY_PENDING, especially teacher attendance, semester-grade finalization/locking, permission enforcement details, report/transcript final authority and Early Warning thresholds. Build safe structures/interfaces/feature flags where appropriate, but do not create developer defaults.

Do not begin coding until the user gives an explicit implementation/sprint instruction. When asked to implement, first state which bundle sections and ADRs the change touches, identify policy dependencies, then proceed with tests and schema/domain logic before presentation polish.

```
