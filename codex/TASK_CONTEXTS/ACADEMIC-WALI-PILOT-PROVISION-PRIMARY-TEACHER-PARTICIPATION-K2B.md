# ACADEMIC WALI PILOT — PRIMARY TEACHER PARTICIPATION K2B

**Task ID:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B`  
**Task type:** `CONTROLLED_PILOT_DATA_PROVISIONING`  
**Selected class:** `IMTAQ-2026-2B`  
**State:** `READY_FOR_FRESH_MANDATORY_PREFLIGHT`
**Branch:** `chore/academic-wali-pilot-primary-k2a`  
**Exact current HEAD:** `e0ba57a99c1ed6588caf5563fa2bd5ae3ea0af5b`
**Executable/evidence CI:** `36809446297` = SUCCESS on `f29763e739cc360821749c7cbfc60266adcf459b`

## Purpose

Provision only the missing expected PRIMARY teacher-participation obligations
for official class `IMTAQ-2026-2B`. This contract prepares the next
controlled PILOT write; it does not execute the write.

## Frozen horizon

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

## Mandatory read-only preflight before any write

Resolve and record the exact current Git HEAD before opening the write path.
Before any business-data write:

1. Prove the target is the authorized PILOT using non-secret markers.
2. Begin `BEGIN TRANSACTION READ ONLY`.
3. Prove `transaction_read_only=on`.
4. Reproduce exactly 12 K2B sessions in the frozen horizon.
5. Prove all 12 sessions are reportable.
6. Prove 12/12 canonical `TeachingAssignment` mappings resolve one non-null
   `teacher_staff_id`.
7. Prove existing expected PRIMARY = 0/12.
8. Prove duplicate/conflict = 0.
9. Prove current lock blockers = 0.
10. Prove K1 remains 10/10 expected PRIMARY.
11. Prove K2A remains 12/12 expected PRIMARY.
12. Prove K3A and K3B are untouched; K3A remains 0/14 and the K3B canonical
    baseline remains 12 joint sessions with expected PRIMARY 0/12.

If any preflight guard fails, stop without a write:

- `TARGET_ENVIRONMENT_NOT_PROVEN`;
- `TARGET_SET_DRIFT`;
- `TEACHER_ASSIGNMENT_NOT_AUTHORITATIVE`;
- `CONCURRENT_PROVISIONING_STATE_CHANGED`;
- `LOCK_BLOCKER`;
- `HOLD`.

## Authorized write

Maximum net-new write: **12 K2B PRIMARY obligations only**.

The only canonical write service is:

`App\Domains\Academic\Services\TeacherParticipationRecorder::ensurePrimary()`

Teacher identity must come only from:

`ClassSession -> TeachingAssignment -> teacher_staff_id`.

Required row semantics:

- `role=PRIMARY`;
- `obligation_type=TEACHING_ASSIGNMENT`;
- `participation_status=EXPECTED`;
- `attendance_status IS NULL`.

Use one outer database transaction. Load only the exact 12 K2B target
sessions. If the net-new count exceeds 12 or any non-K2B row would be touched,
rollback and stop.

## Forbidden

- direct SQL `INSERT`, `UPDATE`, or `DELETE`;
- raw model `create()` bypassing the canonical service;
- K1, K2A, K3A, or K3B provisioning;
- student attendance facts;
- teacher `attendance_status`, check-in, or check-out facts;
- corrections or lock mutation;
- schedule/session generation;
- roster changes;
- account or role changes;
- application source or test changes;
- migration, schema, or runtime config changes;
- AI/provider changes;
- staging/production access;
- deployment;
- main merge.

## Required in-transaction postconditions

Before commit prove:

- K2B = 12/12 expected PRIMARY;
- canonical teacher match = 12/12;
- duplicate/conflict = 0;
- `role=PRIMARY`;
- `obligation_type=TEACHING_ASSIGNMENT`;
- `participation_status=EXPECTED`;
- `attendance_status IS NULL`;
- no student attendance facts;
- no correction facts;
- K1 remains 10/10;
- K2A remains 12/12;
- K3A remains 0/14;
- K3B canonical baseline remains 12/12 joint sessions and 0/12 expected PRIMARY;
- no session, schedule, roster, account, role, or lock mutation.

Any failed postcondition requires rollback and `HOLD`.

## Independent postflight

After a successful commit, open a new `BEGIN TRANSACTION READ ONLY` and prove
`transaction_read_only=on`. Independently recheck K2B 12/12, canonical
semantics, duplicate/conflict = 0, `attendance_status` NULL, no student
attendance/correction facts, K1 10/10, K2A 12/12, K3A 0/14, and K3B canonical
joint sessions 12 with expected PRIMARY 0/12.
Use aggregates only and record no student or teacher names.

## Next task after successful K2B write

`ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2B`

The next task must be read-only and must independently verify the exact frozen
horizon.

## Repository boundaries

This routing contract authorizes no database write. For the future K2B write,
allowed repository changes are limited to its change manifest and state/evidence
artifacts. Do not modify application source, tests, migrations, schema, config,
AI/provider state, or deployment files.

Preserve:

- canonical queue marker `SOC-MD-06`;
- `IMP-S12-007=NOT_STARTED`;
- Public Academic AI = `OFF`.

## Acceptance IDs

- `AWPTP2B-AC-01` exact current HEAD resolved;
- `AWPTP2B-AC-02` PILOT identity proven;
- `AWPTP2B-AC-03` read-only transaction guard proven;
- `AWPTP2B-AC-04` exact 12 reportable K2B sessions;
- `AWPTP2B-AC-05` 12/12 authoritative teacher mappings;
- `AWPTP2B-AC-06` existing PRIMARY 0/12 and conflicts 0;
- `AWPTP2B-AC-07` no lock blocker and K1/K2A stability;
- `AWPTP2B-AC-08` one outer transaction and canonical service only;
- `AWPTP2B-AC-09` maximum 12 K2B-only net-new rows;
- `AWPTP2B-AC-10` K2B canonical semantic postconditions;
- `AWPTP2B-AC-11` no attendance/correction facts;
- `AWPTP2B-AC-12` K1/K2A/K3A/K3B invariants;
- `AWPTP2B-AC-13` independent read-only postflight;
- `AWPTP2B-AC-14` manifest, state, evidence, and next-task routing recorded.

## Closeout vocabulary

Success:

`ACADEMIC_WALI_PILOT_PRIMARY_TEACHER_K2B = COMPLETED / PASS / 12_OF_12_EXPECTED_PRIMARY`

Hold examples:

- `HOLD / TARGET_SET_DRIFT`;
- `HOLD / TEACHER_ASSIGNMENT_NOT_AUTHORITATIVE`;
- `HOLD / CONCURRENT_PROVISIONING_STATE_CHANGED`;
- `HOLD / WRITE_OR_POSTCONDITION_FAILURE`.

Current routing checkpoint itself:

- database write = `NONE`;
- status = `READY_FOR_EXECUTION`;
- next task after successful write = `ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2B`.
