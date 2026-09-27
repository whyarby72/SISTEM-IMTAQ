# Change Impact Record — IMP-REPORT-DETAIL-001

- Request / problem: halaman detail laporan bulanan perlu menampilkan rincian 84 santri dari snapshot permanen.
- Change ID / Task ID: `IMP-REPORT-DETAIL-001`
- Date: 2026-09-06
- Owner module: Academic reporting
- Change class: `MODULE_INTERNAL`
- Affected modules/workstreams: Admin/Wali monthly report detail read path.
- Source-of-truth entities/services affected: `monthly_student_attendance_snapshots` menjadi sumber detail; `student_attendance` dan `monthly_attendance_summaries` tidak berubah.
- Cross-module contracts touched: none; monthly snapshot contract is consumed read-only.
- Expected file/write scope: report controller, targeted compile/checks, change manifest/work log.
- Protected zones touched: none; no attendance transaction or applied migration edited.
- RBAC/privacy/security impact: existing report viewer authorization and Wali class scope remain in force.
- Migration/backward-compatibility impact: none; graceful empty-state remains if snapshot table is unavailable.
- Policy/management decision required?: no.
- Required regression scope: detail query source, per-class counts, role scope, view compilation.
- Evidence/tests: 84 snapshot rows read-only; class totals 20/19/10/15/20; targeted tests and view cache PASS.
- Decision/status: `DONE — SAFE CHECKPOINT`.
