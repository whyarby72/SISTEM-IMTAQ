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
