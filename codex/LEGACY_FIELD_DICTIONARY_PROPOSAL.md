# Proposed Legacy Attendance Field Dictionary

Status: APPROVED for synthetic fixture profiling only; not approval of production schema.

## Daily grain fixture

Grain: one student × calendar date × source row. The fixture contains 5 rows and 4 distinct source students.

| Field | Type/example | Proposed meaning | Persistence rule |
| --- | --- | --- | --- |
| `source_row_id` | text, `DAILY-0001` | stable source-row reference | retain for traceability |
| `legacy_student_key` | text, `LEGACY-STU-001` | source identity key | never treated as canonical ID without review |
| `attendance_date` | date, `2026-07-01` | source calendar date | remain daily; no session expansion |
| `attendance_status` | enum-like text | source status such as `PRESENT`, `IZIN`, `ABSENT`, `SAKIT` | preserve original value until approved value mapping |
| `source_note` | nullable text | source explanation/note | retain verbatim; nullable |

## Monthly grain fixture

Grain: one student × calendar month × source row. The fixture contains 4 rows and each row reconciles to 22 aggregate days.

| Field | Type/example | Proposed meaning | Persistence rule |
| --- | --- | --- | --- |
| `source_row_id` | text, `MONTHLY-0001` | stable source-row reference | retain for traceability |
| `legacy_student_key` | text, `LEGACY-STU-001` | source identity key | never treated as canonical ID without review |
| `summary_month` | `YYYY-MM`, `2026-07` | source summary period | remain monthly; do not fabricate dates |
| `present_days` | non-negative integer | source aggregate present count | preserve as summary value |
| `izin_days` | non-negative integer | source aggregate permission count | preserve as summary value |
| `sakit_days` | non-negative integer | source aggregate sick count | preserve as summary value |
| `absent_days` | non-negative integer | source aggregate absent count | preserve as summary value |
| `source_note` | nullable text | source explanation/note | retain verbatim; nullable |

## Approval gates before migration

- Replace fixture assumptions with a real institutional sample and checksum.
- Confirm whether daily `attendance_status` values and monthly counters have an approved value dictionary.
- Confirm whether monthly aggregate days represent school days, sessions, or another denominator; do not infer.
- Approve target table names and lineage constraints.
- Add migration and tests only after the above decisions are recorded.

## Review decision — 2026-09-04

- Approved scope: use this dictionary to validate sample parsing, grain checks, and import-contract tests.
- Not approved: production field semantics, production table creation, canonical mapping rules, or migration deployment.
- Required next evidence: institutional sample or written confirmation that the fixture fields match the intended source format.
