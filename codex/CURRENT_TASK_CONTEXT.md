# CURRENT TASK CONTEXT

**Task:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K3A`
**State:** `COMPLETED / PASS / 14_OF_14_EXPECTED_PRIMARY`
**Current phase:** `ACADEMIC / K3A CONTROLLED PILOT PROVISIONING CLOSED`
**Branch:** `chore/academic-wali-pilot-primary-k2a`
**Entry basis:** `27ca62aab15e46fdffe44e8a919b7b0d51d8d4f2`
**Preflight CI:** `36925985111` = SUCCESS on exact entry HEAD

K2B provisioning and independent read-only re-verification are closed. K3A
controlled provisioning created exactly 14 expected PRIMARY rows through the
canonical service and passed an independent read-only postflight.

The preceding CI blocker was a wall-clock month-boundary fixture defect in
`AttendanceSemanticMetricsServiceTest`. It was stabilized with a fixed
Asia/Jakarta test clock; production attendance semantics were unchanged.

## Entry evidence

K2A post-write re-verification is CLOSED / ACCEPTED:

- K1 = 10/10 expected PRIMARY;
- K2A = 12/12 expected PRIMARY with canonical semantics;
- K2B = 12/12 expected PRIMARY;
- K3A = 14 reportable sessions, 0/14 expected PRIMARY;
- K3B = 14 canonical joint rules and 12 reportable joint sessions; 12/12 shared PRIMARY;
- current locks = 0;
- Waka authority = 1 effective role / 1 linked staff;
- Public Academic AI = OFF;
- `IMP-S12-007` = `NOT_STARTED`;
- canonical queue marker = `SOC-MD-06`.

## Completed controlled provisioning

The K3A write covered `[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`.
Fresh preflight proved PILOT `imtaq`, PostgreSQL 18.6,
`transaction_read_only=on`, exact 14 standalone reportable sessions,
14/14 authoritative mappings, zero pre-existing participation, conflicts,
locks, attendance facts, and correction facts. One outer transaction called
`TeacherParticipationRecorder::ensurePrimary()` exactly once per target and
persisted 14 K3A-only rows. Independent read-only postflight passed all
semantic, non-target, and side-effect guards.

## Required contract

`codex/CHANGE_MANIFESTS/ACADEMIC-WALI-PILOT-PRIMARY-TEACHER-K3A-2026-10-02.md`

## Next task

`ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K3A` — read-only only.
Do not execute it automatically in this task.
