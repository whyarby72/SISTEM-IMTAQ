# CURRENT TASK CONTEXT

**Task:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2A`  
**State:** `READY_FOR_EXECUTION`  
**Current phase:** `ACADEMIC / PILOT PRIMARY TEACHER PARTICIPATION K2A`  
**Branch:** `chore/academic-wali-pilot-primary-k2a`  
**State-basis:** `754d5346128804f11772ae99f4c45a60916c37a7`

## Entry evidence

Post-K1 read-only re-verification is CLOSED / ACCEPTED:

- K1 = 10/10 expected PRIMARY;
- K1 attendance/correction facts = 0;
- non-target teacher participation baseline remained 83;
- K2A = 12 reportable sessions, 0/12 expected PRIMARY, 12/12 authoritative teaching assignments;
- K2B = 12 missing PRIMARY;
- K3A = 14 missing PRIMARY;
- K3B = no usable schedule/session;
- exact final-head CI run `36658209160` = SUCCESS.

## This atomic task

Provision only official class:

`IMTAQ-2026-2A`

Frozen horizon:

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

Maximum write budget: 12 new expected PRIMARY participation rows.

## Canonical write path

`TeacherParticipationRecorder::ensurePrimary()`

Teacher authority comes only from each session's canonical teaching assignment.

## Boundary

CONTROLLED PILOT DATA WRITE ONLY.

No source/test/schema/config/session/schedule/roster/attendance/correction/lock/
account/role/other-class/staging/production mutation.

## Required contract

`codex/TASK_CONTEXTS/ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2A.md`

## Exit

On PASS, create the K2A change manifest and route a read-only
`ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2A`, then STOP for ChatGPT
audit.

Public Academic AI remains OFF.
`IMP-S12-007` remains NOT_STARTED.
Canonical queue gate remains `SOC-MD-06`.
