# 08 — Academic Operational SOP Contract

## Setup
### SOP-ACA-001 Academic year setup
Create academic year, semester, grade levels, year-specific classes/rombel, subjects, staff references, homeroom assignments and teaching assignments. Class creation uses separate `grade_level_id` + configurable `section_code`; adding A/B/C must not require code changes.

### SOP-ACA-002 Student placement
Use effective-dated `student_class_enrollments`. No current-class overwrite.

### SOP-ACA-003 Homeroom assignment
Effective-dated. Authorization follows assignment period. Historical unfinished work uses controlled handover exception.

### SOP-ACA-004 Teaching assignment
Create teacher × class × subject × effective period.

### SOP-ACA-005 Flexible schedule
Class-specific variable start/end/duration. Teacher/class conflicts are hard-blocking integrity checks. Use `SCHEDULE_CONFLICT_AND_CONSTRAINT_ENGINE.md`; UI preflight never replaces authoritative transactional revalidation.

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
Support recurring extra rule or one-off session. The same teacher/class conflict engine applies before creation.

### SOP-ACA-025A Schedule conflict resolution
When a hard conflict is found, do not use a routine override. Resolve through a valid time/teacher change, substitution, swap, reschedule or cancellation workflow. Resulting state must pass conflict validation before atomic apply.

## Student lifecycle / identifier
### SOP-ACA-026 Student exit
Stop future eligibility, preserve history.

### SOP-ACA-027 Unknown NIS/NISN
Allowed; NULL/no row.

### SOP-ACA-028 Identifier correction
Correct same student record, preserve prior value/audit.

### SOP-ACA-029 Class transfer
Close old enrollment, open new effective enrollment; preserve historical class context. Transfer between 1 A and 1 B never changes Student_ID.

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
