# ACADEMIC WALI PILOT PROVISIONING — REVERIFY AFTER K1

**Task ID:** `ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K1`  
**Task type:** `READ_ONLY_POST_WRITE_PROVISIONING_REVERIFICATION`  
**State:** `READY_FOR_EXECUTION`  
**Branch:** `chore/academic-wali-pilot-provisioning-reverify-after-k1`  
**State-basis:** `d04fd53e7d68558dfdc38a0600a4e849299e83f6`

## Purpose

Independently verify the PILOT state after the controlled Kelas 1 PRIMARY
teacher-participation provisioning and determine the smallest next provisioning
step.

This task must not perform any provisioning write.

## Proven entry evidence

Kelas 1 controlled provisioning closed with:
- official class: `IMTAQ-2026-1`;
- horizon: `[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`;
- total sessions: 11;
- reportable sessions: 10;
- cancelled/non-reportable: 1;
- expected PRIMARY after write: 10/10;
- attendance_status populated by provisioning: 0;
- non-target participation before/after: 83/83;
- canonical write path:
  `TeacherParticipationRecorder::ensurePrimary()`;
- source/schema/config mutation: NONE;
- final-head CI: run `36646931993` on `d04fd53e7d68558dfdc38a0600a4e849299e83f6` = SUCCESS.

Earlier read-only pilot verification found:
- 2A reportable sessions: 12, expected PRIMARY baseline 0/12;
- 2B reportable sessions: 12, expected PRIMARY baseline 0/12;
- 3A reportable sessions: 14, expected PRIMARY baseline 0/14;
- 3B: 0 usable schedule rules and 0 sessions in the seven-day horizon.

Treat these as comparison baselines, not facts to force. Re-query current pilot
state and report drift if present.

## Environment gate

Before business-data queries:
- prove the same intended PILOT environment using non-secret markers;
- do not expose credentials, DSNs, usernames, emails, tokens, or secrets;
- do not access staging or production.

If target identity cannot be proven:
`STOP = TARGET_ENVIRONMENT_NOT_PROVEN`.

If access is unavailable:
`STOP = PILOT_ACCESS_NOT_AVAILABLE`.

## Mandatory PostgreSQL read-only guard

Open business verification in an explicit read-only transaction:

`BEGIN TRANSACTION READ ONLY;`

Verify:

`SHOW transaction_read_only;`

It must be `on`.

Use SELECT/SHOW only and end without writes.

No Artisan command that can migrate, seed, generate sessions, create login
sessions, write caches, dispatch modifying jobs, or mutate pilot state is
authorized.

## Jakarta time authority

Business timezone: `Asia/Jakarta`.

Reverify the same frozen comparison horizon:

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

Do not silently shift to a new rolling seven-day window. This task verifies the
exact target set that was provisioned and the exact peer-class baseline used to
make the remediation decision.

## Mandatory checks

### A. Kelas 1 remediation integrity

For `IMTAQ-2026-1`, verify:
- 11 total sessions remain in the frozen horizon;
- 10 remain reportable;
- the one cancelled/non-reportable session remains excluded;
- every reportable session has exactly one expected PRIMARY;
- coverage = 10/10;
- teacher_staff_id matches the canonical teaching assignment for each session;
- role = PRIMARY;
- obligation_type = TEACHING_ASSIGNMENT;
- participation_status = EXPECTED;
- attendance_status remains NULL on provisioned obligations;
- the cancelled/non-reportable session has not acquired a target PRIMARY
  obligation from the remediation;
- no duplicate/conflicting expected PRIMARY exists.

### B. Non-target side-effect check

Verify the controlled K1 write did not alter provisioning for:
- 2A;
- 2B;
- 3A;
- 3B.

At minimum compare aggregate teacher-participation counts against the prior
non-target baseline where the same predicate is applicable, and separately
report current expected PRIMARY coverage by official class.

Do not infer a problem merely because unrelated operational data legitimately
changed after the K1 task; distinguish K1-task side effect from external drift.

### C. Remaining PRIMARY gaps

Recalculate current reportable-session and expected-PRIMARY coverage for:
- `IMTAQ-2026-2A`;
- `IMTAQ-2026-2B`;
- `IMTAQ-2026-3A`.

For each class, report:
- reportable sessions;
- sessions with exactly one expected PRIMARY;
- missing expected PRIMARY;
- conflicting/duplicate PRIMARY count;
- whether every missing target has an authoritative teaching assignment and
  non-null teacher_staff_id.

Aggregate counts only. Do not record teacher names.

### D. Kelas 3B schedule/session gap

For `IMTAQ-2026-3B`, reverify within the same frozen horizon:
- usable schedule rules;
- generated sessions;
- whether a legitimate no-class condition explains zero sessions;
- whether canonical teaching assignments exist for any usable schedule rule.

Do not generate or repair schedule/session data.

### E. Attendance-fact separation

Verify that the K1 provisioning did not create:
- teacher attendance_status facts;
- check-in/check-out facts;
- student attendance facts;
- corrections;
- lock mutations.

Only aggregate counts are needed.

### F. Lock and escalation readiness

Confirm current relevant period-lock state and effective Waka correction/
escalation authority remain compatible with the previously evidenced Wali
workflow.

## Required artifact

Create:

`codex/REVIEWS/ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K1-2026-09-30.md`

Required sections:
- executive re-verification decision;
- PILOT identity proof, redacted;
- read-only transaction proof;
- frozen Jakarta horizon;
- K1 remediation integrity matrix;
- non-target side-effect result;
- current per-class PRIMARY coverage matrix;
- 3B schedule/session status;
- attendance-fact separation;
- lock/escalation status;
- provisioning gaps still open;
- privacy statement;
- one smallest next atomic task.

## Decision vocabulary

Use exactly one:
- `K1_REMEDIATION_VERIFIED_REMAINING_GAPS`
- `K1_REMEDIATION_VERIFIED_NO_REMAINING_GAPS`
- `UNEXPECTED_POST_WRITE_DRIFT`
- `PILOT_ACCESS_NOT_AVAILABLE`
- `TARGET_ENVIRONMENT_NOT_PROVEN`
- `HOLD`

Do not claim global controlled-pilot readiness unless all previously known
provisioning gaps are independently shown resolved. K1 success alone is not
sufficient.

## Next-task rule

If decision is `K1_REMEDIATION_VERIFIED_REMAINING_GAPS` and 2A still has the
next smallest clean authoritative PRIMARY gap, recommend:

`ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2A`

If another dependency is now the first blocker, recommend exactly one smallest
task supported by current evidence.

If all provisioning gaps are unexpectedly already resolved, recommend a full
read-only Wali pilot readiness recheck before any live attendance transaction.

## Allowed repository writes

Only:
- the re-verification review artifact;
- `PROJECT_STATE.json`;
- `EVIDENCE_INDEX.json`;
- `TEST_MATRIX.csv`;
- `codex/CURRENT_TASK_CONTEXT.md`;
- `NEXT_ACTION.md`.

## Forbidden

- any PILOT database write;
- application source/test/view/route changes;
- migration/schema/config/dependency changes;
- schedule/session generation or edits;
- teacher participation creation/update/delete;
- attendance entry;
- corrections/locks;
- account/role mutation;
- staging/production access;
- AI/provider changes;
- deployment;
- main merge.

## Acceptance criteria

- AWPRK1-AC-01 final K1 commit and exact final CI reconciled.
- AWPRK1-AC-02 PILOT identity proven before business queries.
- AWPRK1-AC-03 transaction_read_only = on.
- AWPRK1-AC-04 frozen Jakarta horizon preserved.
- AWPRK1-AC-05 K1 10/10 PRIMARY coverage independently reverified.
- AWPRK1-AC-06 canonical teacher match and obligation semantics reverified.
- AWPRK1-AC-07 cancelled K1 session remains untouched by target provisioning.
- AWPRK1-AC-08 no attendance fact/status created by K1 provisioning.
- AWPRK1-AC-09 non-target side-effect check completed.
- AWPRK1-AC-10 current 2A/2B/3A PRIMARY gaps recalculated.
- AWPRK1-AC-11 3B schedule/session gap reverified.
- AWPRK1-AC-12 one decision and one smallest next atomic task recorded without
  database mutation.

## Closeout

Return:

`ACADEMIC_WALI_PILOT_PROVISIONING_REVERIFY_AFTER_K1 = COMPLETED / READ_ONLY / <DECISION>`

`DATABASE_WRITE = NONE`

Commit/push review/evidence only, then STOP for ChatGPT audit.
