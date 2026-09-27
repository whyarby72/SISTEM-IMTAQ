# Production-Equivalent Legacy Field Confirmation Checklist

Status: `PENDING_CONFIRMATION` — prepared from synthetic fixtures, not an approval to create production tables.

## Daily attendance source

| Check | Proposed field | Confirmation required | Status |
| --- | --- | --- | --- |
| Stable source row reference | `source_row_id` | Is this unique and stable across reruns? | PENDING |
| Source student key | `legacy_student_key` | Which field is the verified institutional key? | PENDING |
| Calendar grain | `attendance_date` | Does one row represent one student on one calendar date? | PENDING |
| Status value | `attendance_status` | Confirm allowed source values and their meanings. | PENDING |
| Notes | `source_note` | Should notes be retained verbatim? | PENDING |

## Monthly summary source

| Check | Proposed field | Confirmation required | Status |
| --- | --- | --- | --- |
| Stable source row reference | `source_row_id` | Is this unique and stable across reruns? | PENDING |
| Source student key | `legacy_student_key` | Which field is the verified institutional key? | PENDING |
| Summary grain | `summary_month` | Does one row represent one student and one calendar month? | PENDING |
| Aggregate denominator | `present_days`, `izin_days`, `sakit_days`, `absent_days` | Do these counts represent days, sessions, or another denominator? | PENDING |
| Notes | `source_note` | Should notes be retained verbatim? | PENDING |

## Required approval record

- Source filename and SHA-256 checksum:
- Source owner / period:
- Sample size reviewed:
- Confirmed daily grain:
- Confirmed monthly grain:
- Confirmed status dictionary:
- Confirmed aggregate denominator:
- Approved by:
- Approval date:

Until every applicable item is confirmed, retain the source in import infrastructure and do not create a legacy-grain migration.
