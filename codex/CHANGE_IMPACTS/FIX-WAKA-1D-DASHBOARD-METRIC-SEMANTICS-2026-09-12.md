# Change Impact — WAKA-1D Dashboard Aggregation & Trend Semantics

Date: 2026-09-12  
Scope: `AcademicRoleDashboardService` and directly relevant tests only  
Precondition: WAKA-1C canonical metrics authoritative

## Decision implemented

Dashboard overview, grade-level aggregation, and daily attendance trend now use the canonical resolved denominator for physical presence. Existing required-participant grain and session/time eligibility rules are preserved.

## Runtime impact

- Overview physical presence: aggregate `(PRESENT + LATE) / RESOLVED`.
- Grade-level physical presence: aggregate `(PRESENT + LATE) / RESOLVED` per grade, count-weighted across classes.
- Daily trend physical presence: `(PRESENT + LATE) / RESOLVED` per day.
- Completeness remains `RESOLVED / ELIGIBLE` at every level.
- Existing zero/null rate closure is preserved through the service rate helper.

## Non-scope

No changes to `AttendanceSemanticMetricsService`, dashboard Blade/UI, export, controller, routes, migrations, database, seeders, RBAC, schedule logic, or unrelated services.

## Data protection

No database write, migration execution, seed/import, or historical data rewrite.
