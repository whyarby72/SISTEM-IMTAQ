# Change Impact Record

- Request / problem: KPI Dashboard Waka memakai seluruh master sehingga tidak selaras dengan lima kelas resmi dan 84 santri pada rekap Juli.
- Change ID / Task ID: IMP-WAKA-KPI
- Date: 2026-09-08
- Owner module: Academic admin dashboard
- Change class: `MODULE_INTERNAL`
- Affected modules/workstreams: Waka academic dashboard KPI presentation and read queries
- Source-of-truth entities/services affected: Official non-pilot academic classes, teaching assignments, July student snapshots
- Cross-module contracts touched: None
- Expected file/write scope: Admin dashboard controller/view/test and change records
- Protected zones touched: NONE
- RBAC/privacy/security impact: None; existing Waka/Super Admin authorization unchanged
- Migration/backward-compatibility impact: None
- Policy/management decision required?: No
- Required regression scope: Admin dashboard tests and Blade rendering
- Staging/deployment impact: Local UAT only
- Rollback/feature-disable/compatibility plan: Revert read-query scope and KPI labels
- Evidence/tests: Targeted tests, Blade cache, browser Waka smoke
- Decision/status: COMPLETE — safe checkpoint
