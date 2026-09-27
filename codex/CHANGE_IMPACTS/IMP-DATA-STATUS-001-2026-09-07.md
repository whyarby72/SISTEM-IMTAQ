# Change Impact — IMP-DATA-STATUS-001

Date: 2026-09-07
Scope: Backfill effective student status for historical July roster

## Authorized write
- Created 84 `student_status_history` rows.
- Target: 84 historical students with active enrollment.
- Effective from: `2026-07-01`.
- Status: `ACTIVE`.
- Reference: `BACKFILL-IMTAQ-2026-09-07`.

## Explicit exclusion
- 10 `PILOT-*` sample students were excluded.
- Pilot status rows created: 0.

## Safety
- Executed inside a database transaction.
- No migration, schema, RBAC, route, attendance fact, or historical attendance change.
- Dashboard verification showed 35 active students in the Waka scope (Kelas 3A/3B), as expected from scoped enrollment.
