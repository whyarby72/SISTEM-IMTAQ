# Change Impact — WAKA-1F Attendance Dashboard UI Semantics

Date: 2026-09-12  
Scope: Dashboard attendance presentation only

## Implemented

- Physical presence now explains its resolved denominator in the overview helper.
- Null physical presence is presented as an explicit unavailable state, distinct from `0%`.
- Completeness shows resolved, eligible, and missing mandatory attendance data.
- Class monitoring no longer calls student attendance opportunities “sesi”.
- Daily trend retains physical presence as the primary metric and adds completeness plus resolved/eligible context.
- Trend layout was adjusted only enough to keep the new semantic text readable on narrow screens.

## Preserved

- Attendance metric formulas and canonical eligibility remain in the services from WAKA-1C/WAKA-1D.
- Actual session completion wording remains session-based.
- Grade summary remains unchanged.
- Export remains unchanged.

## Data protection

No database write, migration, seed/import, RBAC, schedule, or historical data rewrite.
