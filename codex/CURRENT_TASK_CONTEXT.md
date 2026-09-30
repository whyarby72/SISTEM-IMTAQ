# CURRENT TASK CONTEXT

**Task:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B`
**State:** `READY_FOR_EXECUTION`
**Current phase:** `ACADEMIC / PILOT PRIMARY TEACHER PARTICIPATION K2B`
**Branch:** `chore/academic-wali-pilot-primary-k2a`  
**Entry basis:** `b200948c51823d4698126aeb25250bfa5be7f02f`

## Entry evidence

`ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2A` completed read-only:

- PILOT identity proven;
- `transaction_read_only=on`;
- K1 = 10/10 expected PRIMARY;
- K2A = 12/12 expected PRIMARY with canonical semantics;
- K2B = 12 reportable sessions, 0/12 expected PRIMARY;
- K3A = 14 reportable sessions, 0/14 expected PRIMARY;
- K3B = 0 usable schedule rules and 0 sessions;
- current locks = 0;
- Waka authority = 1 effective role / 1 linked staff;
- final CI run `36660752193` = SUCCESS on exact HEAD.

## Next task

Provision only official class `IMTAQ-2026-2B` after a mandatory read-only
preflight over the frozen horizon:

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

Use only `TeacherParticipationRecorder::ensurePrimary()` and only after
proving the target PILOT, read-only transaction guard, exact target set,
authoritative teaching assignments, zero existing expected PRIMARY, zero
conflicts, no lock blocker, and K1/K2A stability.

No K3A/K3B, attendance, correction, lock, schedule, roster, account, role,
staging, production, AI/provider, source, migration, schema, or config
mutation is authorized.

Public Academic AI remains OFF.
`IMP-S12-007` remains `NOT_STARTED`.
Canonical queue marker remains `SOC-MD-06`.

## Required task context

`codex/TASK_CONTEXTS/ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B.md`

## Required entry evidence

`codex/REVIEWS/ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2A-2026-09-30.md`
