# Initial P0/P1 UAT Coverage Map

Status: baseline mapping for `IMP-S12-001`; not production sign-off.

## Covered by automated evidence

- Core identity/lifecycle: `CORE-001/002/004/005`, `LIFE-001`, and effective status interval behavior — Core Student, Identifier, Status History, and Academic enrollment tests.
- Homeroom/RBAC: `HR-001/002/003/004/005` — Wali resolver, dashboard, handover, and post-lock correction tests.
- Scheduling/session integrity: `CAL-001`, `SCH-001`, `SCH-CF-001/002/003/004/005/006/007/008/009/010`, `SES-001/002/003` — calendar, schedule, conflict, generator, snapshot, and exception tests.
- Attendance: `ATT-001/002/003/004/005/006/007/008/009/010` — draft/finalize/correction/UI/teacher attendance tests.
- Lock/correction: `LOCK-001/002/003`, `COR-001/002/003/004` — period lock and correction service tests.
- Substitution/swap/reschedule/cancellation/extra: `SUB-001`, `SUB-CF-001`, `SWAP-001`, `SWAP-CF-001`, `RES-001`, `RES-CF-001`, `CAN-001`, `EXT-001`, `EXT-CF-001`.
- Grades/report/transcript: `GRD-001/002/003/004/005/007/008`, `RPT-001/002/003/004/005`, `TRN-001/002/003`.
- KPI/alerts: `KPI-001/002/003/004/005`, `DQ-001/002/003/004/005`.
- Migration: `MIG-001/002/003/004/006/007/008/009`; import infrastructure, dry-run, identity review, lineage, and reconciliation tests.
- Concurrency/atomicity: `CON-001/002/003/004/005` coverage exists across correction, finalization, schedule, and status-history tests.

## Partial or requiring explicit follow-up

- `CLS-006`: covered by a dedicated dashboard assertion grouping classes by canonical `grade_level_id`.
- `SCH-CF-011`: covered by a dedicated imported-candidate overlap assertion through `ScheduleRuleConflictChecker`; the checker returns a blocking structured conflict before acceptance.
- `PAPER-001/002/003`: paper/outage workflow has no dedicated automated test file.
- `MIG-005`: covered by a dedicated canonical semester-grade import test preserving teaching-assignment provenance.
- Production acceptance gates: backup/restore, performance, business-owner sign-off, and real-source migration remain outside automated application tests.

The next implementation slice should add only these confirmed gaps, keeping `POLICY_PENDING` items disabled rather than marking them passed.
