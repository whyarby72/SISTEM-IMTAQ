# Academic Wali Pilot Provisioning Re-verification After K1

**Task:** `ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K1`  
**Mode:** read-only  
**Branch baseline:** `bc1471f35d402260b7b28f5be5a333c41487a498`  
**Frozen horizon:** `[2026-09-30 00:00, 2026-10-07 00:00)` `Asia/Jakarta`

## Executive decision

`K1_REMEDIATION_VERIFIED_REMAINING_GAPS`

Kelas 1 remediation is independently verified at 10/10 expected PRIMARY with
the cancelled session untouched and no attendance or correction facts created
by the provisioning. The remaining clean authoritative PRIMARY gaps are 2A,
then 2B and 3A. Official 3B still has no usable schedule rule and no generated
session in the frozen horizon. The smallest next task is:

`ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2A`

This result is pilot provisioning evidence only. It is not a production
readiness or publication decision.

## PILOT identity and read-only transaction proof

Identity was proven before business queries using non-secret markers:

| Marker | Result |
|---|---|
| `APP_ENV` | `local` |
| Laravel default connection | `pgsql` |
| PostgreSQL database | `imtaq` |
| PostgreSQL major | `18` |
| PostgreSQL session timezone | `UTC` |
| Business boundary | explicit `Asia/Jakarta` `+07:00` instants |

The established local `imtaq` identity is the authorized PILOT lineage. No
staging or production target was queried. The business verification opened
`BEGIN TRANSACTION READ ONLY`; `SHOW transaction_read_only` returned `on`.
All business checks were aggregate `SELECT` queries and the transaction was
rolled back. No secret, DSN, username, email, token, or credential was
recorded.

## Kelas 1 remediation integrity

| Check | Result |
|---|---:|
| Total sessions in horizon | 11 |
| Reportable sessions | 10 |
| Cancelled/non-reportable sessions | 1 |
| Expected PRIMARY coverage | 10/10 |
| Missing expected PRIMARY | 0 |
| Duplicate/conflicting expected PRIMARY sessions | 0 |
| Canonical teacher mismatch | 0 |
| Role/obligation/participation semantic mismatches | 0 |
| `attendance_status` facts on provisioned obligations | 0 |
| Check-in/check-out facts | 0 |
| Student attendance facts | 0 |
| Direct correction requests for target sessions | 0 |
| Expected PRIMARY on cancelled session | 0 |

Each reportable session has exactly one expected PRIMARY whose
`teacher_staff_id` matches the non-null canonical
`teaching_assignments.teacher_staff_id`, with `role=PRIMARY`,
`obligation_type=TEACHING_ASSIGNMENT`, `participation_status=EXPECTED`, and
`attendance_status IS NULL`. The cancelled session remains excluded and has no
target PRIMARY obligation.

## Non-target side-effect result

The current aggregate contains 93 teacher-participation rows; 10 are the K1
target rows and 83 are non-target rows. The non-target aggregate therefore
remains `83`, matching the K1 manifest pre/postflight baseline. No K1-side
effect was observed in 2A, 2B, 3A, or 3B. No schedule, session, student
attendance, teacher attendance, correction, lock, role, or account mutation
was executed by this verification.

## Current PRIMARY coverage by official class

| Class | Total sessions | Reportable | Usable schedule rules | Exactly-one expected PRIMARY | Missing | Conflicts | Authoritative teaching assignment | Current-period lock |
|---|---:|---:|---:|---:|---:|---:|---|---:|
| `IMTAQ-2026-1` | 11 | 10 | 10 | 10 | 0 | 0 | 10/10 | 0 locked |
| `IMTAQ-2026-2A` | 12 | 12 | 12 | 0 | 12 | 0 | 12/12 | 0 locked |
| `IMTAQ-2026-2B` | 12 | 12 | 14 | 0 | 12 | 0 | 12/12 | 0 locked |
| `IMTAQ-2026-3A` | 14 | 14 | 14 | 0 | 14 | 0 | 14/14 | 0 locked |
| `IMTAQ-2026-3B` | 0 | 0 | 0 | N/A | N/A | N/A | N/A | 0 locked |

The missing-primary counts for 2A/2B/3A are current reportable-session
counts, not student counts. No teacher or student names were selected.

## Official 3B schedule/session gap

Within the frozen horizon, official `IMTAQ-2026-3B` has `0` usable approved or
published schedule rules, `0` generated sessions, and therefore no reportable
session or canonical teaching-assignment target to provision. The evidence
supports a schedule/session provisioning gap; it does not establish a
legitimate no-class exception. No schedule rule or session was generated or
repaired in this task.

## Attendance-fact separation

K1 provisioning created obligations only. The independent read-only query found
zero K1 teacher `attendance_status` values, zero check-in/check-out values,
zero linked `student_attendance` rows, and zero direct correction requests.
The cancelled session has zero expected PRIMARY rows. This confirms that
teacher-obligation provisioning did not enter student attendance or teacher
attendance facts.

## Lock and Waka escalation status

No current-date `LOCKED` attendance-period row was found for any official class
in the frozen check. The aggregate authority check found one effective
`WAKA_AKADEMIK` role assignment and one linked staff identity. This remains
compatible with the previously evidenced Wali workflow and Waka correction /
escalation authority. Authority was inspected only; no correction or lock
operation was invoked.

## Open provisioning gaps and next atomic task

1. 2A: 12 reportable sessions missing expected PRIMARY participation.
2. 2B: 12 reportable sessions missing expected PRIMARY participation.
3. 3A: 14 reportable sessions missing expected PRIMARY participation.
4. 3B: no usable schedule rule and no generated session.

Recommended next atomic task: `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2A`.
It must independently preflight and provision only 2A through the canonical
service, then return to this read-only re-verification pattern. The 3B schedule
gap remains a separate later provisioning decision.

## Privacy and safety

This review stores only class codes, aggregate counts, state labels, and
non-secret environment markers. It intentionally excludes student names,
teacher names, staff identifiers, users, emails, credentials, and secrets.

## Closeout

| Field | Result |
|---|---|
| `ACADEMIC_WALI_PILOT_PROVISIONING_REVERIFY_AFTER_K1` | `COMPLETED / READ_ONLY / K1_REMEDIATION_VERIFIED_REMAINING_GAPS` |
| Database write | `NONE` |
| Source changes | `NONE` |
| Next task | `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2A` |
| Production readiness | `NOT ASSESSED` |
