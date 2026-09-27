# Change Impact — WAKA-1C Canonical Attendance Semantic Metrics

Date: 2026-09-12  
Scope: Core service only  
Mode: Development + Real-Data Pilot

## Decision implemented

Standard attendance opportunity is an `EXPECTED` student participant with `is_required = true`. Optional participants remain outside mandatory KPI denominators and do not create missing opportunities.

## Runtime impact

- `AttendanceSemanticMetricsService::forClassPeriod()` now filters required opportunities explicitly.
- Physical presence and unexcused absence rates use resolved opportunities as their denominator.
- Completeness remains resolved divided by eligible required opportunities.
- With eligible opportunities but zero resolved rows, physical and absence rates are `null`; completeness is `0`.
- With no eligible opportunities, all three rates are `null`.

## Explicit non-scope

No dashboard aggregation, trend, export, UI, route, controller, migration, database, seeder, RBAC, schedule, or historical data changes were made.

## Data protection

No production/staging database write, migration execution, seed/import, or historical rewrite.
