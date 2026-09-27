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
Effective teacher/class overlap. HIGH. Evidence must identify both conflicting rule/session/teacher-participation facts and exact overlap interval. When location exclusivity is later enabled, exclusive-location overlap is included. Same active pair/occurrence deduplicates; resolution never rewrites schedule facts automatically.

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
