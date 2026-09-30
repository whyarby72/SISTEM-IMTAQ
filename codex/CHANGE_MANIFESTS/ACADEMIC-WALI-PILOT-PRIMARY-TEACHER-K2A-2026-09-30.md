# Change Manifest — Academic Wali Pilot Primary Teacher K2A

**Task:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2A`  
**Target:** `IMTAQ-2026-2A`  
**Branch:** `chore/academic-wali-pilot-primary-k2a`  
**Repository baseline:** `b11c0b66b50e8ba80df10d55d08e45d99c9b0214`  
**Frozen horizon:** `[2026-09-30 00:00, 2026-10-07 00:00)` `Asia/Jakarta`

## Result

`ACADEMIC_WALI_PILOT_PRIMARY_TEACHER_K2A = COMPLETED / PASS / 12_OF_12_EXPECTED_PRIMARY`

The authorized PILOT write created exactly 12 net-new expected PRIMARY teacher
participation obligations for K2A and no other class. The source repository,
schema, runtime configuration, sessions, schedules, rosters, locks, attendance
facts, corrections, accounts, and roles were not changed.

## Environment and preflight

Non-secret identity markers were verified before the write:

| Marker | Result |
|---|---|
| `APP_ENV` | `local` |
| Laravel default connection | `pgsql` |
| PostgreSQL database | `imtaq` |
| PostgreSQL major | `18` |
| PostgreSQL session timezone | `UTC` |
| Business boundary | explicit `Asia/Jakarta` `+07:00` instants |
| Read-only transaction | `on` |

Preflight in `BEGIN TRANSACTION READ ONLY` proved:

| Check | Result |
|---|---:|
| K2A total sessions | 12 |
| K2A reportable sessions | 12 |
| K2A authoritative teaching assignments | 12/12 |
| Existing expected PRIMARY | 0/12 |
| Conflicts/duplicates | 0 |
| Current lock blockers | 0 |
| K1 total/reportable | 11/10 |
| K1 expected PRIMARY | 10/10 |

No credentials, DSNs, usernames, emails, staff identifiers, or secrets were
recorded.

## Authorized write

One outer database transaction loaded the exact 12 K2A reportable
`ClassSession` records and called only:

`App\Domains\Academic\Services\TeacherParticipationRecorder::ensurePrimary()`

The service-derived canonical chain was:

`ClassSession -> TeachingAssignment -> teacher_staff_id`.

No raw SQL INSERT, model `create()`, manual teacher mapping, or returned-row
mutation was used. Actual net-new rows: **12** (within the maximum budget of
12).

## In-transaction postconditions

| Postcondition | Result |
|---|---:|
| K2A expected PRIMARY | 12/12 |
| Canonical teacher match | 12/12 |
| `role=PRIMARY` | 12/12 |
| `obligation_type=TEACHING_ASSIGNMENT` | 12/12 |
| `participation_status=EXPECTED` | 12/12 |
| `attendance_status IS NULL` | 12/12 |
| Duplicate/conflicting PRIMARY | 0 |
| K1 expected PRIMARY | 10/10 |
| 2B/3A/3B participation coverage | unchanged at 0/12, 0/14, 0/0 |

## Independent read-only postflight

A new guarded read-only transaction returned `transaction_read_only=on`
and independently verified:

- K2A = 12/12 expected PRIMARY;
- duplicate/conflicting PRIMARY = 0;
- all canonical teacher/semantic checks passed;
- K1 remained 10/10;
- 2B remained 0/12, 3A remained 0/14, and 3B remained 0/0;
- K2A `attendance_status` facts = 0;
- K2A student attendance facts = 0;
- K2A direct correction requests = 0.

The pre-existing K1 and non-target class records were not touched. The
cancelled K1 session remained outside the target set and unchanged.

## Remaining gaps

- 2B: 12 reportable sessions still missing expected PRIMARY participation.
- 3A: 14 reportable sessions still missing expected PRIMARY participation.
- 3B: no usable schedule rule and no generated session in the frozen horizon.

The next task is read-only:

`ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2A`.

## Safety closeout

| Field | Result |
|---|---|
| Database write | authorized PILOT write only, 12 rows |
| Application source changed | no |
| Migration/schema/config changed | no |
| Attendance facts/corrections/locks changed | no |
| Student/teacher names recorded | no |
| Public Academic AI | OFF |
| Production readiness | NOT ASSESSED |
