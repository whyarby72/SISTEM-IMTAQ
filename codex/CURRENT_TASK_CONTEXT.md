# CURRENT TASK CONTEXT

**Task:** `ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K1`  
**State:** `READY_FOR_EXECUTION`  
**Current phase:** `ACADEMIC / PILOT POST-WRITE REVERIFICATION`  
**Branch:** `chore/academic-wali-pilot-provisioning-reverify-after-k1`  
**State-basis:** `d04fd53e7d68558dfdc38a0600a4e849299e83f6`

## Entry checkpoint

Controlled Kelas 1 provisioning is CLOSED / ACCEPTED:

- 11 sessions;
- 10 reportable;
- 10/10 expected PRIMARY after write;
- one cancelled session untouched;
- attendance_status rows created = 0;
- non-target participation = unchanged in recorded postflight;
- final-head CI run `36646931993` = SUCCESS.

## This task

Read-only independent re-verification of:
- Kelas 1 remediation integrity;
- absence of K1 write side effects;
- current PRIMARY gaps for 2A/2B/3A;
- 3B schedule/session gap;
- lock/escalation readiness.

Use the exact frozen comparison horizon:

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

## Required contract

`codex/TASK_CONTEXTS/ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K1.md`

## Boundary

PILOT READ-ONLY ONLY.

No database write, source change, session generation, provisioning, attendance
transaction, correction, deployment, or staging/production access.

Public Academic AI remains OFF.
`IMP-S12-007` remains NOT_STARTED.
Canonical queue gate remains `SOC-MD-06`.

## Exit

Create the re-verification artifact, reconcile evidence/state, commit/push
review/evidence only, then STOP for ChatGPT audit.
