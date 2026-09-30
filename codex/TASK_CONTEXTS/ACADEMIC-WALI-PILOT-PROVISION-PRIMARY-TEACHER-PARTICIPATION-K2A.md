# ACADEMIC WALI PILOT — PRIMARY TEACHER PARTICIPATION K2A

**Task ID:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2A`  
**Task type:** `CONTROLLED_PILOT_DATA_PROVISIONING`  
**Selected class:** `IMTAQ-2026-2A`  
**State:** `READY_FOR_EXECUTION`  
**Branch:** `chore/academic-wali-pilot-primary-k2a`  
**State-basis:** `754d5346128804f11772ae99f4c45a60916c37a7`

## Purpose

Provision only the missing expected PRIMARY teacher-participation obligations
for official class `IMTAQ-2026-2A`, using the same canonical service and safety
pattern that passed for Kelas 1.

Do not touch 2B, 3A, 3B, Kelas 1, or any unrelated pilot data.

## Proven baseline

The read-only re-verification after K1 established, for the frozen horizon
`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`:

- Kelas 2A total sessions = 12;
- reportable sessions = 12;
- expected PRIMARY = 0/12;
- missing expected PRIMARY = 12;
- conflicts/duplicates = 0;
- authoritative teaching assignment = 12/12;
- current-period lock = 0;
- Kelas 1 remains healthy at 10/10;
- 2B and 3A remain separate later gaps;
- 3B still has no usable schedule/session.

Exact final repository CI before this task:
- run `36658209160`;
- head `754d5346128804f11772ae99f4c45a60916c37a7`;
- conclusion `SUCCESS`.

## Source of truth

For each target session, the teacher must come only from:

`ClassSession -> teachingAssignment -> teacher_staff_id`

The only authorized write path is:

`TeacherParticipationRecorder::ensurePrimary(ClassSession $session)`

Expected created semantics:
- role = PRIMARY;
- obligation_type = TEACHING_ASSIGNMENT;
- participation_status = EXPECTED;
- attendance_status remains NULL.

No manual teacher mapping is authorized.

## Environment gate

Before any write:
- prove target is the same authorized PILOT environment;
- prove branch/HEAD matches this routed task;
- record only non-secret environment markers;
- do not print credentials, DSNs, usernames, emails, passwords, tokens, or secrets.

If PILOT identity is not proven:
`STOP = TARGET_ENVIRONMENT_NOT_PROVEN`.

## Mandatory read-only preflight

First run the K2A target selection inside an explicit PostgreSQL read-only
transaction and verify `transaction_read_only=on`.

The exact frozen horizon is:

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

Preflight must prove:
- class is exactly `IMTAQ-2026-2A`;
- exactly 12 sessions exist;
- exactly 12 are reportable;
- every target session has a canonical teaching_assignment_id;
- each target teaching assignment resolves one non-null teacher_staff_id;
- expected PRIMARY count is still 0/12;
- no conflicting/duplicate expected PRIMARY exists;
- no target is cancelled/non-reportable;
- no current lock/state invalidates provisioning;
- K1 expected PRIMARY remains 10/10 before K2A write.

If target count or membership drifts:
`STOP = TARGET_SET_DRIFT`.

If teacher assignment is missing/ambiguous:
`STOP = TEACHER_ASSIGNMENT_NOT_AUTHORITATIVE`.

If K2A already has concurrent PRIMARY provisioning:
`STOP = CONCURRENT_PROVISIONING_STATE_CHANGED`.

No write after any STOP.

## Controlled write

Only after preflight PASS:
- open one outer database transaction;
- load the exact 12 K2A target ClassSession records;
- for each target call `TeacherParticipationRecorder::ensurePrimary($session)`;
- do not edit returned rows;
- do not set attendance_status, presence, absence, reason, check-in, or check-out;
- do not mutate session, schedule rule, teaching assignment, roster, locks, roles, or accounts.

### Write budget

Maximum permitted net-new rows:

`12 session_teacher_participations`

If more than 12 rows would be created, any non-K2A row would be touched, or
any target changes during execution, rollback.

## In-transaction postconditions

Before commit verify:
- K2A reportable sessions = 12;
- expected PRIMARY coverage = 12/12;
- exactly one expected PRIMARY per target session;
- teacher matches canonical teaching_assignment.teacher_staff_id;
- role = PRIMARY;
- obligation_type = TEACHING_ASSIGNMENT;
- participation_status = EXPECTED;
- attendance_status IS NULL;
- K1 remains 10/10;
- 2B/3A/3B participation counts are unchanged from preflight;
- no source/session/schedule/roster/lock/attendance fact is changed.

If any postcondition fails: rollback and HOLD.

## Independent read-only postflight

After commit, open a new read-only transaction and verify:
- K2A 12/12 expected PRIMARY;
- no duplicate/conflicting PRIMARY;
- attendance_status count created = 0;
- K1 remains 10/10;
- 2B/3A/3B target counts unchanged;
- no student attendance/correction fact was created by this task;
- read-only guard = on.

Aggregate counts only. No student or teacher names in evidence.

## Required artifact

Create:

`codex/CHANGE_MANIFESTS/ACADEMIC-WALI-PILOT-PRIMARY-TEACHER-K2A-2026-09-30.md`

Record:
- target/branch and redacted PILOT identity;
- frozen Jakarta horizon;
- routing/final CI evidence available;
- preflight counts;
- canonical write service;
- transaction atomicity and write budget;
- actual rows created;
- postconditions;
- independent read-only postflight;
- no attendance fact/status creation;
- no source/schema/config mutation;
- no K1 or other-class mutation;
- remaining 2B/3A/3B gaps.

## Existing source must remain unchanged

Do not edit:
- TeacherParticipationRecorder.php;
- SessionTeacherParticipation.php;
- ClassSession.php;
- TeachingAssignment.php;
- routes/controllers/services/views;
- tests;
- migrations/schema;
- seeders;
- runtime config.

Temporary local scratch may exist only untracked and deleted before closeout,
and must invoke the existing canonical service rather than recreate business
logic.

## Next-task rule

On PASS, next task:

`ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2A`

That next task must be read-only.

## Allowed repository writes

Only:
- the K2A change manifest;
- PROJECT_STATE.json;
- EVIDENCE_INDEX.json;
- TEST_MATRIX.csv;
- codex/CURRENT_TASK_CONTEXT.md;
- NEXT_ACTION.md.

## Authorized PILOT write

Only the exact 12 K2A expected PRIMARY participation rows described above.

## Forbidden

No direct SQL INSERT/UPDATE/DELETE, no source changes, no seeding, no migration,
no session generation, no schedule/roster change, no attendance facts, no
corrections/locks, no account/role change, no 2B/3A/3B/K1 provisioning, no
staging/production access, no AI/provider changes, no deployment, no main merge.

## Acceptance criteria

- AWPTP2A-AC-01 final reverify CI/state metadata reconciled.
- AWPTP2A-AC-02 PILOT identity proven before write.
- AWPTP2A-AC-03 read-only preflight reproduces exact 12 K2A reportable sessions.
- AWPTP2A-AC-04 12/12 authoritative teaching assignments confirmed.
- AWPTP2A-AC-05 preflight confirms 0/12 expected PRIMARY and no conflicts.
- AWPTP2A-AC-06 one outer atomic transaction used.
- AWPTP2A-AC-07 canonical ensurePrimary service used for every target.
- AWPTP2A-AC-08 net-new write count <=12 and K2A only.
- AWPTP2A-AC-09 postconditions prove 12/12 expected PRIMARY.
- AWPTP2A-AC-10 attendance_status remains NULL.
- AWPTP2A-AC-11 K1 remains 10/10 and other classes unchanged.
- AWPTP2A-AC-12 independent read-only postflight passes.
- AWPTP2A-AC-13 no application/source/schema/config mutation.
- AWPTP2A-AC-14 change manifest and read-only next task recorded.

## Closeout

PASS:

`ACADEMIC_WALI_PILOT_PRIMARY_TEACHER_K2A = COMPLETED / PASS / 12_OF_12_EXPECTED_PRIMARY`

HOLD examples:
- `HOLD / TARGET_SET_DRIFT`
- `HOLD / TEACHER_ASSIGNMENT_NOT_AUTHORITATIVE`
- `HOLD / CONCURRENT_PROVISIONING_STATE_CHANGED`
- `HOLD / WRITE_OR_POSTCONDITION_FAILURE`

Commit/push repository evidence only, then STOP for ChatGPT audit.
