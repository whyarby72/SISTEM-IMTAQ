# Session Occurrence Management Policy Register v1.1

Project: SISTEM IMTAQ  
Domain: Academic Attendance / Academic Session Occurrence  
Effective date: 2026-09-19  
Status: CURRENT GOVERNANCE POLICY

`SUPERSEDES_FOR_CURRENT_POLICY = SESSION_OCCURRENCE_MANAGEMENT_POLICY_REGISTER_v1.0.md`  
`HISTORICAL_V1_0_PRESERVED = YES`

“Supersedes” means current-policy authority only. It does not delete or rewrite policy register v1.0.

## 1. Purpose

This register freezes SOC-MD-01 through SOC-MD-06 for the Academic pilot and future session-occurrence architecture. It is a governance artifact only. It does not automatically make the current application compliant and does not authorize source, schema, migration, or database changes.

## 2. Version / Revision History

- v1.0 froze SOC-MD-01 through SOC-MD-05 and left SOC-MD-06 `OPEN`.
- v1.1 preserves v1.0 and freezes SOC-MD-06A partial session, SOC-MD-06B teacher lateness, SOC-MD-06B substitute teacher, SOC-MD-06C no lesson/cancellation, and SOC-MD-06D conflicting evidence.
- v1.1 changes `SOC_MD_06` from `OPEN` to `DECIDED` and `SOC_MDG_COMPLETE` from `NO` to `YES`.

## 3. Authority and Predecessor Gates

Authority order is: approved Core authority; explicit management decisions; frozen governance; current source/tests as implementation evidence; recovery and Change Manifest evidence; task/progress evidence; current pointers; historical snapshots.

Predecessors verified byte-for-byte:

- v1.0 policy SHA-256: `25491de6238d36cd25757cb69ffe41857ba9b09b9a460dea02b38fc8fbef3b65`.
- SOC-MDG v1.0 Change Manifest SHA-256: `5d96bc994207d0ae15672a46b9b58b61978b0a461937eb4648e054eae37ebd2b`.
- CIBS baseline v1.0 SHA-256: `37a9f4b4866fd70b4b1de4a3fec29503dc9368198a5437171cdb80f95b9f4d85`.

## 4. Current Operational Role Structure

`SUPER_ADMIN → WAKA_AKADEMIK → WALI_KELAS`

Routine digital HELD recorder: `WALI_KELAS`. Teachers have no website access, no direct database input, and no direct HELD certification. Teachers are offline operational-evidence providers. Waka has cross-class oversight, exception, and correction authority. Super Admin provides technical/emergency administration and is not the routine academic business certifier.

## 5. Canonical Concept Separation

The Core target occurrence vocabulary is exactly:

`SCHEDULED`, `HELD`, `CANCELLED`, `RESCHEDULED`.

Only officially `HELD` sessions relevant to eligible students may eventually form attendance opportunities. Do not create `PARTIALLY_HELD`, `DISPUTED`, `CONFLICTED`, or `UNDER_REVIEW` as canonical occurrence statuses. Partial, conflict, and pending-review conditions are workflow/exception concepts.

The following remain separate:

`SESSION_WORKFLOW_STATE ≠ SESSION_OCCURRENCE_STATE ≠ ATTENDANCE_COMPLETENESS ≠ ATTENDANCE_OUTCOME ≠ PUBLICATION_STATE`.

A joint institutional session has one occurrence truth, even when attendance attribution is partitioned by reporting class.

## 6. SOC-MD-01 — Routine HELD Authority

`SOC_MD_01 = DECIDED`.

`ROUTINE_HELD_RECORDER = WALI_KELAS`; `TEACHER_WEBSITE_ACCESS = NO`; `TEACHER_DIRECT_HELD_CERTIFICATION = NO`; `TEACHER_ROLE = OFFLINE_OPERATIONAL_EVIDENCE_PROVIDER`.

Waka Akademik provides cross-class oversight, exception authority, and correction authority. Super Admin is technical/emergency administration, not the routine academic certifier. A joint session remains `ONE_INSTITUTIONAL_SESSION → ONE_OCCURRENCE_TRUTH`.

## 7. SOC-MD-02 — Teacher Self-Certification

`SOC_MD_02 = DECIDED`.

Teacher direct website self-certification is `NOT_APPLICABLE`. `TEACHER_CAN_SET_HELD_IN_SYSTEM = NO`, `TEACHER_DIRECT_DATABASE_INPUT = NO`, and `TEACHER_OCCURRENCE_EVIDENCE_ROLE = YES`. Evidence provider and system recorder are different responsibilities.

## 8. SOC-MD-03 — HELD vs Attendance Completeness

`SOC_MD_03 = DECIDED`.

HELD does not require complete student attendance. `HELD_REQUIRES_COMPLETE_STUDENT_ATTENDANCE = NO`; `ATTENDANCE_COMPLETENESS_SEPARATE_FROM_OCCURRENCE = YES`; `MISSING_ATTENDANCE_DOES_NOT_CANCEL_HELD = YES`.

The valid future conceptual combination is `SESSION_OCCURRENCE = HELD` with `ATTENDANCE_COMPLETENESS = INCOMPLETE`. This register does not implement it.

## 9. SOC-MD-04 — Routine Waka Approval

`SOC_MD_04 = DECIDED`.

Routine HELD is recorded by Wali Kelas and does not require individual second approval by Waka: `ROUTINE_HELD_REQUIRES_WAKA_APPROVAL = NO`; `ROUTINE_HELD_OPERATIONALLY_VALID_AFTER_RECORDING = YES`.

`OPERATIONALLY_VALID ≠ LOCKED ≠ PUBLISHED`. Waka handles oversight, exceptions, conflicting evidence, and corrections.

## 10. SOC-MD-05 — Correction Authority

`SOC_MD_05 = DECIDED`.

`VALIDATED_ACADEMIC_DATA_CORRECTION_AUTHORITY = WAKA_AKADEMIK`; Waka can initiate and execute corrections without a second approver. Silent overwrite and history deletion are forbidden. Old value, new value, reason, actor, timestamp, and version history are required. Attendance correction and occurrence correction are separate transaction types.

## 11. SOC-MD-06A — Partial Sessions

`SOC_MD_06A_PARTIAL_SESSION = DECIDED`.

A session that actually occurs may remain HELD even when the full scheduled duration is not completed. Full duration is not required; no minimum-minute threshold is defined; exact actual start/end times are not required; partial occurrence is not automatically cancelled; and the partial condition must remain observable as an exception/note.

`PARTIAL_SESSION_CAN_BE_HELD = YES`; `FULL_SCHEDULED_DURATION_REQUIRED_FOR_HELD = NO`; `MINIMUM_MINUTE_THRESHOLD = NOT_DEFINED`; `EXACT_ACTUAL_START_TIME_REQUIRED = NO`; `EXACT_ACTUAL_END_TIME_REQUIRED = NO`; `PARTIAL_SESSION_AUTOMATICALLY_CANCELLED = NO`; `PARTIAL_SESSION_EXCEPTION_REQUIRED = YES`.

Do not create `PARTIALLY_HELD` as a canonical occurrence status.

## 12. SOC-MD-06B — Teacher Lateness

`SOC_MD_06B_TEACHER_LATENESS = DECIDED`.

If the teacher is present and KBM actually occurs, lateness does not prevent HELD and does not automatically cancel the session. The operational record may remain `HADIR + catatan TERLAMBAT`. Exact late duration and exact arrival time are not required; lateness remains separate from occurrence.

## 13. SOC-MD-06B — Substitute Teacher

`SOC_MD_06B_SUBSTITUTE_TEACHER = DECIDED`.

Existing substitution mechanics are preserved. A substitute-taught session may be HELD if KBM occurs; it is not automatically cancelled or rescheduled. Original session identity, primary teacher identity, substitute identity, reason, and audit lineage remain preserved. A substitute may be recorded as `PRESENT`, with `catatan TERLAMBAT` if applicable; exact start/end time is not required.

Routine individual-session substitution may be assigned by Wali Kelas within own authorized class/session scope or by Waka Akademik. Waka may correct substitution. Wali may not change permanent teaching assignment, master schedule teacher, or assign a substitute for another class. Super Admin is not the routine substitute-assignment owner. A joint-session substitution is one session-level fact; conflicting Wali assignments are forbidden and escalated to Waka.

## 14. SOC-MD-06C — No Lesson / Cancellation

`SOC_MD_06C_NO_LESSON = DECIDED`.

If the primary teacher does not teach, no substitute teaches, and KBM genuinely does not occur, the occurrence is `CANCELLED`. Cancellation requires a reason, creates no student attendance opportunity, and students are not marked absent for the cancelled session. Primary non-attendance may remain preserved. If a substitute teaches, the session is not cancelled. If it partially occurs, it is not automatically cancelled. If moved, it is `RESCHEDULED`, not cancelled.

Existing cancellation mechanics are preserved. Wali can cancel an own-class session; Waka can cancel cross-class and bulk sessions; Super Admin is not the routine cancellation owner. A joint/shared institutional session requires Waka or explicit shared-session authority.

Ordinary cancellation with existing student attendance is blocked. If evidence later proves the session did not occur, route to a conflict-correction workflow; do not delete attendance.

## 15. SOC-MD-06D — Conflicting Evidence

`SOC_MD_06D_CONFLICTING_EVIDENCE = DECIDED`.

Genuine evidence conflict requires Waka review. Wali may report and submit evidence but has no final conflict-resolution authority; Waka has final authority. Conflict does not automatically produce HELD or CANCELLED. Insufficient evidence is `PENDING_REVIEW`; unresolved conflict cannot be published. `PENDING_REVIEW` is a workflow/review state, not an occurrence status.

After review: actually occurred → `HELD`; did not occur → `CANCELLED`; moved → `RESCHEDULED`. If attendance exists but the session is later proven cancelled, use versioned correction: preserve history, require reason/actor/audit, and recalculate denominators after approved occurrence correction. This is policy only and is not implementation authorization.

## 16. Joint-Session Rules

A joint institutional session has one occurrence resolution. It is forbidden for the same shared ClassSession to be HELD for one reporting class and CANCELLED for another. Attendance attribution may remain class-partitioned. Joint-session conflict resolution belongs to Waka Akademik.

## 17. Attendance vs Occurrence Correction

Student/teacher attendance correction and session-occurrence correction remain separate transaction types. Neither permits silent overwrite or deletion. Occurrence correction affecting an existing attendance record requires versioned, audited correction with denominator recalculation after approval.

## 18. Existing Implementation Preservation

`PRESERVE_EXISTING_APPLICATION = YES`.

Do not rebuild student attendance, teacher attendance, `SubstitutionService`, `CancellationService`, `RescheduleService`, joint-session implementation, attendance correction/versioning, dashboard attendance metrics, or dashboard export. Future work is a targeted occurrence-state rebase around the existing pilot implementation.

## 19. Known Policy-to-Code Deltas

Policy decided does not mean code implemented. The existing substitution authorization differs from the newly approved Wali scoped-substitution policy and is classified `SUBSTITUTION_RBAC_POLICY_IMPLEMENTATION = PENDING_FUTURE_REBASE`. Future occurrence support is `SESSION_OCCURRENCE_POLICY_IMPLEMENTATION = NOT_STARTED`. No delta is silently implemented by this freeze.

## 20. Remaining Non-SOC Blockers

`SESSION_OCCURRENCE_CANONICALIZATION = NOT_COMPLETED`; `SOURCE_AUTHORITY_ENFORCEMENT = PARTIAL`; `WAKA_SCOPE_CANONICALIZATION = NOT_COMPLETED`; `REAL_POSTGRES_RUNTIME = NOT_VERIFIED`; `HISTORICAL_EXCUSED_RECONCILIATION = NOT_COMPLETED`; `CLASS_LINEAGE_GOVERNANCE = NOT_COMPLETED`; `FOLLOWUP_DOMAIN = NOT_IMPLEMENTED`; `MD_01_DISPENSASI = OPEN`; `MD_02_NON_ELIGIBLE_AUTHORITY = OPEN`; `FULL_ATTENDANCE_CANONICALIZATION = NO`.

## 21. Implementation Gate

SOC-MDG completion does not authorize application changes. `SESSION_OCCURRENCE_IMPLEMENTATION_AUTHORIZED = NO`; `SOURCE_MUTATION_AUTHORIZED = NO`; `DATABASE_MIGRATION_AUTHORIZED = NO`; `HISTORICAL_REWRITE_AUTHORIZED = NO`.

The next step is architecture specification and implementation planning, followed by recovery/rollback design and explicit source-mutation authorization—not immediate migration.

## 22. AI Gate

`AI_IMPLEMENTATION_STARTED = NO`; `OPENAI_API_CALLED = NO`; `TYPED_AI_TOOLS = NOT_STARTED`; `OPENAI_API_INTEGRATION = BLOCKED`; `LLM_ORCHESTRATION = BLOCKED`; `AI_UI = BLOCKED`.

## 23. Policy Status

SOC-MD-01 through SOC-MD-06 are decided. `SOC_MDG_COMPLETE = YES`. This register is governance authority for future planning, while the current application remains transitional and unchanged.

## Machine-readable state

```text
SOC_POLICY_REGISTER_VERSION = v1.1
PREDECESSOR_POLICY_VERSION = v1.0
SOC_MD_01 = DECIDED
SOC_MD_02 = DECIDED
SOC_MD_03 = DECIDED
SOC_MD_04 = DECIDED
SOC_MD_05 = DECIDED
SOC_MD_06A_PARTIAL_SESSION = DECIDED
SOC_MD_06B_TEACHER_LATENESS = DECIDED
SOC_MD_06B_SUBSTITUTE_TEACHER = DECIDED
SOC_MD_06C_NO_LESSON = DECIDED
SOC_MD_06D_CONFLICTING_EVIDENCE = DECIDED
SOC_MD_06 = DECIDED
SOC_MDG_COMPLETE = YES
ROUTINE_HELD_RECORDER = WALI_KELAS
TEACHER_WEBSITE_ACCESS = NO
HELD_REQUIRES_COMPLETE_STUDENT_ATTENDANCE = NO
ROUTINE_HELD_REQUIRES_WAKA_APPROVAL = NO
VALIDATED_ACADEMIC_DATA_CORRECTION_AUTHORITY = WAKA_AKADEMIK
PARTIAL_SESSION_CAN_BE_HELD = YES
FULL_SCHEDULED_DURATION_REQUIRED_FOR_HELD = NO
MINIMUM_MINUTE_THRESHOLD = NOT_DEFINED
TEACHER_LATE_RECORDING = HADIR_PLUS_CATATAN_TERLAMBAT
EXACT_LATE_DURATION_REQUIRED = NO
WALI_KELAS_CAN_ASSIGN_SUBSTITUTE = YES
WALI_SUBSTITUTE_SCOPE = OWN_AUTHORIZED_CLASS_SESSION_ONLY
WAKA_AKADEMIK_CAN_ASSIGN_SUBSTITUTE = YES
SUBSTITUTE_TAUGHT_SESSION_CAN_BE_HELD = YES
NO_LESSON_OCCURRED_WITHOUT_SUBSTITUTE = CANCELLED
CANCELLED_SESSION_CREATES_STUDENT_ATTENDANCE_OPPORTUNITY = NO
WALI_KELAS_CAN_CANCEL_OWN_CLASS_SESSION = YES
WAKA_AKADEMIK_CAN_CANCEL_CROSS_CLASS = YES
GENUINE_EVIDENCE_CONFLICT_REQUIRES_WAKA_REVIEW = YES
WAKA_FINAL_CONFLICT_RESOLUTION_AUTHORITY = YES
CONFLICT_AUTOMATICALLY_HELD = NO
CONFLICT_AUTOMATICALLY_CANCELLED = NO
INSUFFICIENT_EVIDENCE = PENDING_REVIEW
UNRESOLVED_CONFLICT_CAN_BE_PUBLISHED = NO
CONFLICT_IS_WORKFLOW_STATE_NOT_CANONICAL_OCCURRENCE_STATUS = YES
DELETE_EXISTING_ATTENDANCE_HISTORY = NO
VERSION_HISTORY_REQUIRED = YES
DENOMINATOR_RECALCULATION_AFTER_APPROVED_OCCURRENCE_CORRECTION = YES
REBUILD_ATTENDANCE_WORKFLOW = NO
REBUILD_TEACHER_ATTENDANCE = NO
REBUILD_SUBSTITUTION = NO
REBUILD_CANCELLATION = NO
REBUILD_RESCHEDULING = NO
REBUILD_JOINT_SESSION = NO
REBUILD_ATTENDANCE_CORRECTION = NO
REBUILD_DASHBOARD_METRICS = NO
REBUILD_DASHBOARD_EXPORT = NO
SESSION_OCCURRENCE_REBASE_DIRECTION = SPLIT_WORKFLOW_AND_OCCURRENCE_STATE
SESSION_OCCURRENCE_CANONICALIZATION = NOT_COMPLETED
SUBSTITUTION_RBAC_POLICY_IMPLEMENTATION = PENDING_FUTURE_REBASE
SESSION_OCCURRENCE_POLICY_IMPLEMENTATION = NOT_STARTED
SOURCE_AUTHORITY_ENFORCEMENT = PARTIAL
WAKA_SCOPE_CANONICALIZATION = NOT_COMPLETED
REAL_POSTGRES_RUNTIME = NOT_VERIFIED
HISTORICAL_EXCUSED_RECONCILIATION = NOT_COMPLETED
CLASS_LINEAGE_GOVERNANCE = NOT_COMPLETED
FOLLOWUP_DOMAIN = NOT_IMPLEMENTED
MD_01_DISPENSASI = OPEN
MD_02_NON_ELIGIBLE_AUTHORITY = OPEN
FULL_ATTENDANCE_CANONICALIZATION = NO
SESSION_OCCURRENCE_IMPLEMENTATION_AUTHORIZED = NO
SOURCE_MUTATION_AUTHORIZED = NO
DATABASE_MIGRATION_AUTHORIZED = NO
HISTORICAL_REWRITE_AUTHORIZED = NO
AI_IMPLEMENTATION_STARTED = NO
OPENAI_API_CALLED = NO
```
