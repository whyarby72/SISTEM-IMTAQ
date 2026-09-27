# Change Manifest — IMP-DATA-STATUS-001

Status: DONE — SAFE CHECKPOINT

## Result
- Created 84 effective `ACTIVE` student-status rows for the historical roster.
- Excluded all 10 sample/pilot students.

## Verification
- Created by reference: 84 rows.
- Pilot status rows: 0.
- Browser dashboard after reload: Waka scope displays 35 active students across Kelas 3A and Kelas 3B.

## Unchanged
- Database schema/migrations.
- RBAC, routes, attendance facts, attendance semantics, and reports.
