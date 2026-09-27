# Architecture Consistency Patch R1

This document records the patches applied before Codex handoff so implementation does not rediscover old contradictions.

## R1 patches — DESIGN_LOCKED
1. **Academic Calendar:** `academic_calendar_events` is part of the Academic/Core model. Session generation must check calendar exceptions. A holiday known before generation means no regular session is generated; an already-planned session that later cannot run uses cancellation/reschedule workflow.
2. **Base Engine amendments formalized:** administrative identifiers, enrollment, lifecycle, homeroom assignment and Academic attendance use the normalized/effective-dated models documented in the active schema.
3. **Superseded blacklist:** paper attendance is backup/verification only; `attendance_entry_batches` is not a required MVP table; routine online student attendance is not entered by Guru; routine attendance has no second human validator.
4. **Session completion:** Wali Kelas does not receive generic session-status edit permission. Successful `FinalizeStudentAttendance` may transition an eligible session to `COMPLETED` as a controlled system side effect.
5. **Semester-grade provenance:** grade grain remains `Student × Subject × Semester`. `source_teaching_assignment_id` is nullable provenance and must not force a false single-assignment claim when a student transfers mid-semester.
6. **Correction versioning:** `NEW_VERSION` means logical business version. MVP may update the canonical row with `version_no++` plus append-only old/new audit; published documents keep physical snapshot versions.
7. **Homeroom handover:** authority is effective-dated. A new Wali Kelas does not automatically gain normal edit rights over the previous Wali’s historical period. Unfinished historical work uses a controlled handover/exception command with audit.
8. **Permission integration:** `permission_event_id` remains nullable until institutional enforcement policy is approved. When present it must reference the same student and a valid event. Permission never auto-creates attendance.
9. **Grade-period lock:** the concept is required, but persistence grain/officialization authority remains `POLICY_PENDING`; Codex must not hard-code class×semester or teaching-assignment×semester without policy.
10. **Governance labels:** `DESIGN_LOCKED` is explicitly separate from `MANAGEMENT_APPROVED`.

## Additional vocabulary clarification
`EXCUSED` remains an Academic absence category distinct from `SICK` and `PERMISSION`, but operational examples/boundaries are `POLICY_PENDING` and must be clarified before relying on it for institutional policy.
