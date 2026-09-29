# ACADEMIC WALI PILOT PROVISIONING VERIFICATION

**Task ID:** `ACADEMIC-WALI-PILOT-PROVISIONING-VERIFICATION`  
**Task type:** `READ_ONLY_PILOT_DATA_VERIFICATION`  
**Owner:** CODEX  
**State:** `READY_FOR_EXECUTION`  
**Branch:** `chore/academic-wali-pilot-provisioning-verification`  
**State-basis:** `467133e28c10cf6072468bc624bb2e82dd6ec172`

## Purpose

Close the remaining gap from the Wali Daily Workflow Readiness Review by
checking whether the actual authorized PILOT environment is provisioned for
controlled daily attendance use.

This is not an application implementation task and not production readiness.

## Prior decision

The application workflow is already:

`APPLICATION_READY_PROVISIONING_NOT_VERIFIED`

Do not repeat the source-level readiness review except where schema/model
inspection is required to write safe read-only verification queries.

## Verification authority

The Project Owner authorizes **read-only inspection of the intended pilot
environment only** for this task.

Authorized:
- inspect environment identity without exposing secrets;
- connect to the existing pilot PostgreSQL database if credentials/access are
  already available to Codex;
- run SELECT/read-only metadata queries;
- aggregate counts and effective assignment checks;
- inspect current operational provisioning for the Wali attendance workflow.

Not authorized:
- INSERT / UPDATE / DELETE / UPSERT;
- migrations or schema changes;
- seeders/factories;
- session generation;
- attendance draft/finalize;
- correction/lock mutation;
- account creation/reset;
- role/permission changes;
- login/browser actions that create session or audit side effects;
- provider/AI changes;
- staging/production access;
- deployment.

Do not request, print, commit, or expose passwords, tokens, connection strings,
emails, phone numbers, student names, or other unnecessary personal data.

## Environment identity gate

Before any business-data query, prove the target is the intended PILOT
environment using available non-secret evidence such as environment label,
database name, host classification, repository/local configuration lineage, or
other established project markers.

Record only redacted/non-secret identity evidence.

If the target might be staging or production, or pilot identity cannot be
established with confidence:

STOP = `TARGET_ENVIRONMENT_NOT_PROVEN`

Do not query business records.

If connection/access is unavailable:

STOP = `PILOT_ACCESS_NOT_AVAILABLE`

Do not create credentials or weaken guards.

## Read-only database guard

For PostgreSQL inspection, force or verify read-only transaction semantics
before business-data queries.

Preferred shape:

`BEGIN TRANSACTION READ ONLY;`

then verify:

`SHOW transaction_read_only;`

It must report `on` for the verification transaction.

Use only SELECT/SHOW/EXPLAIN without ANALYZE where needed.

Rollback/end the transaction without writes.

Do not run Artisan commands that could migrate, seed, queue, cache-write, create
sessions, or otherwise mutate the pilot.

## Date/time authority

Business date/time = `Asia/Jakarta`.

Evaluate effective-dated roles, homeroom assignments, sessions, and locks using
the current Jakarta business date/time at execution.

Do not reinterpret historical timestamps or perform historical rewrites.

## Mandatory provisioning checks

Verify the actual pilot state for the currently intended Academic Wali Kelas
workflow.

### A. Identity and authorization provisioning

For each active Wali Kelas assignment in the current Academic period, verify:
- active user exists;
- active/effective Staff linkage exists;
- effective `WALI_KELAS` role/permission exists;
- effective homeroom/class assignment exists;
- no duplicate/conflicting active homeroom authority.

Do not publish usernames, emails, credentials, or unnecessary personal data.
Use class code/name plus anonymized/stable staff reference or role count where
possible.

### B. Academic structure

Verify:
- current Academic year/period is active;
- each Wali-operated class is active;
- class membership/roster has active students;
- no class required for attendance is orphaned from a Wali assignment.

Report counts only; do not list student names.

### C. Schedule and session availability

For the current Jakarta date and an operational horizon of the next 7 calendar
days:
- identify expected Academic teaching schedule rules;
- verify class sessions/occurrences are generated where the system contract
  requires pre-generated sessions;
- distinguish a legitimate no-class day from missing provisioning;
- detect schedule rules with no usable session occurrence when one is expected.

Do not generate missing sessions.

### D. Participant roster/snapshot

For available pilot sessions in the verification horizon:
- verify participant roster/snapshot exists where required;
- verify participant count is non-zero for classes that have active students;
- identify count mismatches or missing snapshots using aggregates only.

Do not expose student names or attendance facts.

### E. Teacher participation

For sessions where teacher participation/obligation is required:
- verify assigned teacher/primary teacher relationship exists;
- verify required teacher participation records/obligations are provisioned
  according to the current application contract;
- identify missing teacher linkage without creating it.

### F. Lock/correction readiness

Verify:
- current attendance period is not unexpectedly locked for normal daily entry,
  or document the legitimate lock state;
- at least one effective Waka Akademik authority exists for correction/review
  and escalation;
- no read-only evidence suggests Wali must bypass the governed correction path.

### G. Data-quality contradictions

Detect only provisioning-level contradictions relevant to first use:
- orphan user/staff link;
- role without homeroom assignment;
- homeroom without active class;
- active class with zero active roster;
- expected session missing;
- required roster snapshot missing;
- required teacher linkage missing;
- unexpected active-period lock;
- duplicate active authority.

Do not broaden into a general database audit.

## Per-class readiness matrix

Produce one row per current Wali-operated class with:

- class reference;
- active Wali authority: PASS/GAP;
- active roster count;
- schedule availability: PASS/GAP/NO_CLASS_EXPECTED;
- session availability: PASS/GAP/NOT_APPLICABLE;
- roster snapshot: PASS/GAP/NOT_APPLICABLE;
- teacher participation: PASS/GAP/NOT_APPLICABLE;
- lock state: OPEN/LOCKED_EXPECTED/GAP;
- final class readiness.

Do not include student names.

## Final decision vocabulary

Use exactly one:

- `READY_FOR_CONTROLLED_PILOT`
- `PROVISIONING_GAP_FOUND`
- `PILOT_ACCESS_NOT_AVAILABLE`
- `TARGET_ENVIRONMENT_NOT_PROVEN`
- `HOLD`

`READY_FOR_CONTROLLED_PILOT` requires:
- application readiness already evidenced;
- pilot identity proven;
- read-only guard proven;
- required Wali identity/role/homeroom provisioning present;
- active class/roster present;
- current/near-horizon schedule/session provisioning usable;
- required roster and teacher relationships available;
- no unexplained lock blocker;
- escalation authority available.

It does not mean production-ready.

## Required artifact

Create:

`codex/REVIEWS/ACADEMIC-WALI-PILOT-PROVISIONING-VERIFICATION-2026-09-30.md`

Required sections:
- executive verification decision;
- environment identity evidence (redacted);
- read-only transaction proof;
- Jakarta effective timestamp;
- per-class readiness matrix;
- identity/role/homeroom findings;
- active roster counts;
- 7-day schedule/session findings;
- roster-snapshot findings;
- teacher-participation findings;
- lock/correction authority findings;
- provisioning DQ gaps;
- privacy statement confirming no unnecessary PII was recorded;
- production-readiness separation;
- one recommended next atomic task.

## Next-task rule

If decision = `READY_FOR_CONTROLLED_PILOT`:
next task = `ACADEMIC-WEB-GRADE-WORKFLOW-SURFACE-DESIGN`.

If decision = `PROVISIONING_GAP_FOUND`:
recommend ONE smallest provisioning-remediation task based on the first
dependency blocker. Do not remediate it here.

If access/environment identity blocks verification:
recommend only the smallest access/identity gate needed to complete this
verification.

## Allowed repository writes

Only:
- `codex/REVIEWS/ACADEMIC-WALI-PILOT-PROVISIONING-VERIFICATION-2026-09-30.md`
- `PROJECT_STATE.json`
- `EVIDENCE_INDEX.json`
- `TEST_MATRIX.csv`
- `codex/CURRENT_TASK_CONTEXT.md`
- `NEXT_ACTION.md`

## Forbidden repository writes

No application source, route, controller, service, model, view, test,
migration, schema, workflow runtime, config, dependency, provider, deployment,
or production-state change.

## Acceptance criteria

- AWPV-AC-01 current repository/CI baseline reconciled.
- AWPV-AC-02 pilot environment identity proven before business-data queries.
- AWPV-AC-03 database verification executed read-only or task stops safely.
- AWPV-AC-04 current Wali user/staff/role provisioning verified.
- AWPV-AC-05 effective homeroom/class authority verified.
- AWPV-AC-06 active class and roster provisioning verified by aggregate.
- AWPV-AC-07 7-day schedule/session availability verified.
- AWPV-AC-08 required participant snapshot/roster availability verified.
- AWPV-AC-09 required teacher participation/linkage verified.
- AWPV-AC-10 lock/correction/escalation readiness verified.
- AWPV-AC-11 no secret/unnecessary student PII recorded.
- AWPV-AC-12 one final decision and one next atomic task returned without
  provisioning mutation.

## Closeout

Return:

`ACADEMIC_WALI_PILOT_PROVISIONING_VERIFICATION = COMPLETED / READ_ONLY / <DECISION>`

`IMPLEMENTATION_AUTHORIZATION = NOT_AUTHORIZED`

Commit/push review/evidence only, then STOP for ChatGPT audit.
