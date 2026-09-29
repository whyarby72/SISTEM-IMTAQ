# Change Manifest — Kelas 1 Primary Teacher Participation

**Task:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-FOR-ONE-CLASS`  
**Branch:** `fix/academic-wali-pilot-primary-teacher-k1`  
**Routed HEAD:** `7309c1fa82bad49f4d8c8c49667cd8c73f449cc0`  
**Routing CI:** GitHub Actions run `36645721249` — `SUCCESS`  
**Selected class:** `IMTAQ-2026-1`  
**Business horizon:** `[2026-09-30 00:00, 2026-10-07 00:00)` Asia/Jakarta

## Target and identity guard

The target was proven before write using non-secret markers:

- Laravel-resolved database: `imtaq`;
- application environment: `local`;
- Laravel connection: `pgsql`;
- PostgreSQL major: `18`;
- technical PostgreSQL session timezone: `UTC`, consistent with the approved
  technical-session contract; business timestamps were bound explicitly with
  `+07:00` boundaries.

No credential, password, DSN, email, username, token, or secret was recorded.

## Read-only preflight

The preflight reproduced the exact target set in a read-only transaction:

| Check | Result |
|---|---:|
| `transaction_read_only` | `on` |
| Total Kelas 1 sessions | 11 |
| Reportable sessions | 10 |
| Excluded cancelled/non-reportable sessions | 1 |
| Reportable sessions with canonical teaching assignment and non-null teacher | 10/10 |
| Existing expected PRIMARY | 0 |
| Conflicting PRIMARY | 0 |
| Locked attendance-period rows | 0 |
| Non-target participation baseline | 83 |
| Cancelled-session participation baseline | 0 |

The target was therefore accepted for controlled provisioning.

## Controlled write

- One outer database transaction was used.
- Exactly 10 target `ClassSession` records were locked and processed.
- Existing `App\Domains\Academic\Services\TeacherParticipationRecorder::ensurePrimary()`
  was the only write path.
- No raw SQL INSERT, model `create()`, manual teacher mapping, seeder, or
  direct participation write was used.
- Net-new rows: **10**.
- No `attendance_status`, check-in, check-out, absence, presence, or reason was
  written.
- No session, schedule, roster, lock, account, role, provider, or other-class
  record was intentionally changed.

## In-transaction postconditions

All postconditions passed before commit:

- 10 reportable target sessions each had exactly one expected PRIMARY;
- each participation teacher matched the session's canonical
  `teaching_assignment.teacher_staff_id`;
- `role=PRIMARY`;
- `obligation_type=TEACHING_ASSIGNMENT`;
- `participation_status=EXPECTED`;
- `attendance_status IS NULL`;
- cancelled-session participation count remained 0;
- write count remained within the maximum budget of 10.

## Independent read-only postflight

A new transaction was opened after commit and verified:

| Check | Result |
|---|---:|
| Target sessions | 11 |
| Reportable sessions | 10 |
| Expected PRIMARY coverage | 10/10 |
| Cancelled-session participation before/after | 0 / 0 |
| Non-target participation before/after | 83 / 83 |
| Attendance-status rows created by provisioning | 0 |
| Postflight `transaction_read_only` | `on` |

Postflight result: `PASS`.

## Scope and remaining gaps

This was controlled pilot data provisioning only. No application source, test,
route, view, migration, schema, runtime configuration, staging/production
data, or credentials changed.

Known remaining provisioning gaps are unchanged:

- expected PRIMARY participation for reportable sessions in official classes
  `IMTAQ-2026-2A`, `IMTAQ-2026-2B`, and `IMTAQ-2026-3A`;
- official class `IMTAQ-2026-3B` still lacks schedule/session provisioning.

## Closeout

`ACADEMIC_WALI_PILOT_PRIMARY_TEACHER_K1 = COMPLETED / PASS / 10_OF_10_EXPECTED_PRIMARY`

**Next atomic task:** `ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K1`  
**Database write:** controlled PILOT write only  
**Production readiness:** not assessed

