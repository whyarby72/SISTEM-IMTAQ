# Academic Wali Pilot Provisioning Re-verification After K2A

**Task:** `ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2A`  
**Mode:** `READ_ONLY_POST_WRITE_PROVISIONING_REVERIFICATION`  
**Audited branch:** `chore/academic-wali-pilot-primary-k2a`  
**Audited HEAD:** `b200948c51823d4698126aeb25250bfa5be7f02f`  
**Final CI:** run `36660752193`, SUCCESS, exact HEAD verified  
**Frozen horizon:** `[2026-09-30 00:00, 2026-10-07 00:00)` `Asia/Jakarta`

## Decision

`K2A_REMEDIATION_VERIFIED_REMAINING_GAPS`

K1 and K2A are stable after their controlled provisioning. K1 has 10/10
expected PRIMARY and K2A has 12/12. The remaining clean primary gaps are K2B
(12) and K3A (14). K3B still has no usable schedule rule and no generated
session. No K2B provisioning was executed.

## PILOT and read-only proof

The target was proven before business queries using non-secret markers:

| Marker | Result |
|---|---|
| `APP_ENV` | `local` |
| Laravel connection | `pgsql` |
| PostgreSQL database | `imtaq` |
| PostgreSQL major | `18` |
| PostgreSQL technical session timezone | `UTC` |
| Business boundary | explicit Jakarta `+07:00` instants |
| Transaction guard | `BEGIN TRANSACTION READ ONLY` |
| `transaction_read_only` | `on` |

The transaction was rolled back. All checks were aggregate SELECTs. No secret,
credential, username, email, student name, teacher name, or staff identifier
was recorded.

## Coverage and semantic verification

| Official class | Total sessions | Reportable | Usable rules | Expected PRIMARY | Missing | Conflicts | Semantic mismatches | Current lock |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| K1 | 11 | 10 | 10 | 10/10 | 0 | 0 | 0 | 0 |
| K2A | 12 | 12 | 12 | 12/12 | 0 | 0 | 0 | 0 |
| K2B | 12 | 12 | 14 | 0/12 | 12 | 0 | 0 | 0 |
| K3A | 14 | 14 | 14 | 0/14 | 14 | 0 | 0 | 0 |
| K3B | 0 | 0 | 0 | N/A | N/A | N/A | N/A | 0 |

For every K2A PRIMARY, the aggregate join verified the canonical chain
`ClassSession -> TeachingAssignment -> teacher_staff_id`, plus:

- `role=PRIMARY`;
- `obligation_type=TEACHING_ASSIGNMENT`;
- `participation_status=EXPECTED`;
- `attendance_status IS NULL`;
- duplicate/conflict count = 0.

K1 remains 10/10 with the same semantics. K2B and K3A remain unprovisioned,
with no conflicting PRIMARY records.

## Attendance/correction fact separation

For K1, K2A, K2B, K3A, and K3B in the frozen horizon, aggregate facts were:

| Fact type | Count |
|---|---:|
| Teacher `attendance_status` facts | 0 |
| Teacher check-in/check-out facts | 0 |
| Student attendance facts | 0 |
| Direct correction requests | 0 |

No correction or lock mutation occurred. Current-period locked rows for all
official classes = 0.

## K3B schedule/session gap

K3B has 0 usable approved/published schedule rules and 0 generated sessions
in the frozen horizon. This remains a schedule/session provisioning gap. No
schedule or session generation was performed.

## Waka escalation authority

The aggregate authority check found 1 effective `WAKA_AKADEMIK` role
assignment and 1 linked staff identity. This remains compatible with the
existing Wali correction/escalation workflow. No authority mutation or
correction operation was invoked.

## CI and repository evidence

The final-head GitHub Actions run was independently checked:

- run: `36660752193`;
- status: completed;
- conclusion: success;
- head SHA: `b200948c51823d4698126aeb25250bfa5be7f02f`;
- workflow: Application foundation.

Metadata reconciliation records this run as the exact current CI and includes
the K2A manifest, this review, and the next task context in evidence refs.

## Remaining gaps and next atomic task

1. K2B: 12 reportable sessions still missing expected PRIMARY.
2. K3A: 14 reportable sessions still missing expected PRIMARY.
3. K3B: no usable schedule rule and no generated session.

Next atomic task:

`ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B`

This review does not authorize or execute that provisioning.

## Closeout

| Field | Result |
|---|---|
| Decision | `K2A_REMEDIATION_VERIFIED_REMAINING_GAPS` |
| Database write in this task | `NONE` |
| Application/source/test/migration/schema/config changes | `NONE` |
| K1/K2A | `10/10`, `12/12` |
| K2B/K3A | `0/12`, `0/14` |
| K3B | `0` usable rules / `0` sessions |
| Public Academic AI | `OFF` |
| `IMP-S12-007` | `NOT_STARTED` |
| Canonical queue | `SOC-MD-06` |
| Safe to close for ChatGPT audit | `YES` |
