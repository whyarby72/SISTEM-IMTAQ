# ACADEMIC WALI PILOT — PRIMARY TEACHER PARTICIPATION FOR ONE CLASS

**Task ID:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-FOR-ONE-CLASS`  
**Task type:** `CONTROLLED_PILOT_DATA_PROVISIONING`  
**Selected class:** `IMTAQ-2026-1`  
**State:** `READY_FOR_EXECUTION`  
**Branch:** `fix/academic-wali-pilot-primary-teacher-k1`  
**State-basis:** `2c2dccfb2961fd211b6c496514929919a3e9219a`

## Purpose

Remove exactly one proven pilot provisioning blocker by creating the missing
expected PRIMARY teacher participation obligations for Kelas 1 only.

Do not touch the remaining classes and do not address the Kelas 3B
schedule/session gap in this task.

## Proven baseline

Read-only verification established:

- intended PILOT environment proven;
- PostgreSQL read-only verification passed;
- Kelas 1 active roster = 20;
- verified horizon = `[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`;
- Kelas 1 sessions in horizon = 11;
- reportable sessions = 10;
- one session is non-reportable/cancelled;
- expected PRIMARY teacher participation on the 10 reportable sessions = 0.

Repository final-head CI after verification:
- run `36642953383`
- head `2c2dccfb2961fd211b6c496514929919a3e9219a`
- conclusion `SUCCESS`.

## Source of truth

For every target session, the teacher must come only from:

`ClassSession -> teachingAssignment -> teacher_staff_id`

The write path must be:

`TeacherParticipationRecorder::ensurePrimary(ClassSession $session)`

That service is already idempotent, locks the session/teacher obligation, and
creates:

- role = `PRIMARY`
- obligation_type = `TEACHING_ASSIGNMENT`
- participation_status = `EXPECTED`

Do not use direct SQL INSERT, model create(), raw DB insert, seeder, or manual
teacher mapping.

## Environment gate

Before any write:

1. prove the target is the same authorized PILOT identity used by the preceding
   verification;
2. prove the current repository branch/HEAD is the expected routed task;
3. record only non-secret environment markers;
4. do not print credentials, DSNs, usernames, emails, passwords, or tokens.

If PILOT identity is not proven:

`STOP = TARGET_ENVIRONMENT_NOT_PROVEN`

## Mandatory read-only preflight

First use a read-only transaction to re-identify the Kelas 1 target set.

The target set must reproduce the exact verified business horizon:

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

and the same reportable-session predicate used by the verification artifact.

Preflight must prove all of the following:

- official class code is exactly `IMTAQ-2026-1`;
- 11 sessions exist in the horizon;
- exactly 10 are reportable;
- exactly one is excluded as non-reportable/cancelled;
- every target session has a canonical `teaching_assignment_id`;
- every target teaching assignment resolves exactly one non-null
  `teacher_staff_id`;
- no target session already has an expected PRIMARY participation;
- no target session has a conflicting PRIMARY expectation;
- no target session is locked or otherwise in a state that makes provisioning
  invalid under the current domain contract.

Do not list teacher names or student names in the artifact.

If any count or target membership differs from the verified baseline:

`STOP = TARGET_SET_DRIFT`

If teacher assignment is ambiguous/missing:

`STOP = TEACHER_ASSIGNMENT_NOT_AUTHORITATIVE`

If any existing/conflicting primary expectation indicates concurrent mutation:

`STOP = CONCURRENT_PROVISIONING_STATE_CHANGED`

No write is allowed after any STOP condition.

## Controlled write

Only after preflight PASS:

- open one outer database transaction;
- load the exact 10 target `ClassSession` records;
- for each target session call
  `TeacherParticipationRecorder::ensurePrimary($session)`;
- do not modify any returned row beyond what `ensurePrimary()` creates;
- do not set `attendance_status`;
- do not set presence/absence/check-in/check-out;
- do not mutate sessions or teaching assignments.

### Write budget

Maximum permitted net-new rows:

`10 session_teacher_participations`

If the transaction would create more than 10 rows or touch a non-target
session/class, rollback.

## In-transaction postconditions

Before commit, prove:

- target reportable session count = 10;
- expected PRIMARY participation count across target = 10;
- each target session has exactly one expected PRIMARY;
- each created primary teacher matches that session's canonical
  `teachingAssignment.teacher_staff_id`;
- each row has:
  - role = PRIMARY;
  - obligation_type = TEACHING_ASSIGNMENT;
  - participation_status = EXPECTED;
  - attendance_status IS NULL;
- no participation rows were created for the excluded cancelled session;
- no rows were created for classes 2A, 2B, 3A, or 3B by this task.

If any postcondition fails, rollback and return HOLD.

## Commit rule

Commit the database transaction only after every postcondition passes.

Do not attempt partial success.

## Independent read-only postflight

After commit, open a new PostgreSQL read-only transaction and verify:

- Kelas 1 reportable sessions = 10;
- Kelas 1 expected PRIMARY coverage = 10/10;
- cancelled/non-reportable Kelas 1 session remains without a newly-created
  target obligation from this task;
- non-target class participation counts are unchanged from preflight;
- no attendance status was populated by provisioning.

Record aggregate counts only.

## Operational audit evidence

Create:

`codex/CHANGE_MANIFESTS/ACADEMIC-WALI-PILOT-PRIMARY-TEACHER-K1-2026-09-30.md`

It must record:

- task and branch;
- redacted PILOT identity proof;
- Jakarta horizon;
- preflight counts;
- target write budget;
- canonical service used;
- transaction atomicity;
- actual rows created;
- aggregate postconditions;
- independent read-only postflight;
- explicit statement that no attendance fact/status was created;
- explicit statement that no source/schema/session/schedule/roster/other-class
  data changed;
- remaining known gaps.

Do not record student names, teacher names, secrets, credentials, or unnecessary
personal data.

## Existing source that must remain unchanged

This task is operational provisioning using the already-implemented contract.
Do not edit:

- `TeacherParticipationRecorder.php`;
- `SessionTeacherParticipation.php`;
- `ClassSession.php`;
- `TeachingAssignment.php`;
- tests;
- migrations;
- seeders;
- routes/views/controllers/services;
- runtime configuration.

Temporary local scratch code may be used only if untracked, deleted before
closeout, and it invokes the existing canonical service rather than recreating
business logic.

## Regression/evidence check

Source is unchanged. Still verify:

- git diff contains no application source/test/schema/config changes;
- repository structure check passes;
- exact final routing/closeout CI is recorded if triggered.

Do not run tests against the PILOT database.

## Remaining gaps after success

Success on Kelas 1 does NOT mean pilot is globally ready.

Known gaps expected to remain:
- primary teacher participation for reportable sessions in 2A, 2B, 3A;
- Kelas 3B schedule/session provisioning.

Therefore on PASS the next atomic task is:

`ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K1`

That next task must be read-only.

## Allowed repository writes

Only:
- `codex/CHANGE_MANIFESTS/ACADEMIC-WALI-PILOT-PRIMARY-TEACHER-K1-2026-09-30.md`
- `PROJECT_STATE.json`
- `EVIDENCE_INDEX.json`
- `TEST_MATRIX.csv`
- `codex/CURRENT_TASK_CONTEXT.md`
- `NEXT_ACTION.md`

## Authorized pilot write

Only the exact target Kelas 1 PRIMARY teacher participation rows described
above.

## Forbidden

- application source changes;
- direct SQL INSERT/UPDATE/DELETE;
- seeding;
- migration/schema changes;
- session generation;
- schedule changes;
- roster changes;
- attendance status entry;
- student attendance facts;
- teacher attendance facts;
- corrections/locks;
- account/role changes;
- other-class provisioning;
- staging/production access;
- AI/provider changes;
- deployment;
- main-branch merge.

## Acceptance criteria

- AWPTP1-AC-01 final verification CI/state metadata reconciled.
- AWPTP1-AC-02 PILOT target identity proven before write.
- AWPTP1-AC-03 read-only preflight reproduces 11 total / 10 reportable K1 sessions.
- AWPTP1-AC-04 each target session has authoritative canonical teacher assignment.
- AWPTP1-AC-05 preflight confirms 0 expected PRIMARY rows on exact target set.
- AWPTP1-AC-06 exactly one outer atomic transaction used for provisioning.
- AWPTP1-AC-07 canonical `ensurePrimary()` service used for every target.
- AWPTP1-AC-08 net-new write count <= 10 and target class only.
- AWPTP1-AC-09 postconditions prove 10/10 expected PRIMARY coverage.
- AWPTP1-AC-10 attendance_status remains NULL on provisioned rows.
- AWPTP1-AC-11 cancelled/non-reportable session not provisioned by this task.
- AWPTP1-AC-12 independent read-only postflight passes.
- AWPTP1-AC-13 no application/source/schema/config mutation.
- AWPTP1-AC-14 change manifest and next read-only re-verification task recorded.

## Closeout vocabulary

PASS:

`ACADEMIC_WALI_PILOT_PRIMARY_TEACHER_K1 = COMPLETED / PASS / 10_OF_10_EXPECTED_PRIMARY`

HOLD examples:
- `HOLD / TARGET_SET_DRIFT`
- `HOLD / TEACHER_ASSIGNMENT_NOT_AUTHORITATIVE`
- `HOLD / CONCURRENT_PROVISIONING_STATE_CHANGED`
- `HOLD / WRITE_OR_POSTCONDITION_FAILURE`

After closeout commit/push repository evidence only, STOP for ChatGPT audit.
