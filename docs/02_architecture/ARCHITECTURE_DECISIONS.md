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
- ADR-ACA-019: Grade Level and Class Section are separate data dimensions; class/rombel records are academic-year-specific and section codes are configurable data, not hard-coded enums.
- ADR-ACA-SCHED-001: Teacher and class overlap are hard integrity conflicts, not dismissible UI warnings.
- ADR-ACA-SCHED-002: Schedule conflict evaluation uses half-open time ranges plus effective dates and canonical recurrence occurrences.
- ADR-ACA-SCHED-003: Preflight is advisory; authoritative conflict validation is repeated inside the atomic write transaction.
- ADR-ACA-SCHED-004: Substitution/swap/reschedule/extra-session conflict checks evaluate the resulting effective delivery state before apply.
- ADR-ACA-SCHED-005: Concurrency protection serializes competing writes for affected teacher/class resources and rechecks before commit.
- ADR-ACA-SCHED-006: Data Quality detects effective schedule conflicts as a backstop for import/integration/anomaly; prevention remains primary.

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
- CP-009: Management authority policy — `WAKA_AKADEMIK` has full Academic business authority and `SUPER_ADMIN` has full institution-wide authority across enabled domains; both remain subject to resource state, workflow, audit and versioning, and neither is an automatic approver.

## Base Engine amendments
The following refine the Base Engine and must be explicit in implementation documentation:
1. NIS/NISN are in effective/auditable identifier records, not identity keys.
2. Current class is derived from effective-dated enrollments.
3. Student status is historical/effective-dated.
4. Homeroom assignment is effective-dated.
5. Academic attendance is class-session based, not a generic god-table.
6. Routine Academic student attendance finalizes under Wali Kelas authority without second human validation.
7. Default lifecycle is a reference model; domain-specific lifecycle composition is permitted when audit, validation, lock and publication controls are preserved.
## Shared Communication ADRs — DESIGN_LOCKED FUTURE
- ADR-COMM-001: Shared Communication is a provider-neutral shared platform, not a domain-owned WhatsApp integration.
- ADR-COMM-002: Communication purpose distinguishes `ANNOUNCEMENT`, `REMINDER`, and `ACTION_REQUEST`.
- ADR-COMM-003: Business Event First, Communication Second; messages never replace calendar/schedule/report Source of Truth.
- ADR-COMM-004: Institutional audiences resolve from canonical Staff/Organization/Role/Assignment data or audited managed groups.
- ADR-COMM-005: Teacher availability confirmation is not teacher attendance. `NO_RESPONSE` is not ABSENT and `CONFIRMED` is not PRESENT.
- ADR-COMM-006: An `UNAVAILABLE` teacher response creates an operational exception for human Academic decision; it never auto-applies substitution/reschedule/cancellation.
- ADR-COMM-007: Provider outage/failure never changes source-domain facts.
- ADR-COMM-008: Manual broadcast/send authority is explicit permission + scope; executive read-only hierarchy does not imply send rights.
- ADR-COMM-009: Action-request generation and provider/webhook processing must be idempotent and auditable.
- ADR-COMM-010: Shared Communication implementation remains `DEFERRED_FUTURE` until identity/RBAC/queue/privacy and provider gates are satisfied.
