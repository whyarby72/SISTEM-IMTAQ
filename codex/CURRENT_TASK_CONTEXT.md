# CURRENT TASK CONTEXT

**Task:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-FOR-ONE-CLASS`  
**State:** `READY_FOR_EXECUTION`  
**Current phase:** `ACADEMIC / PILOT PRIMARY TEACHER PARTICIPATION PROVISIONING`  
**Branch:** `fix/academic-wali-pilot-primary-teacher-k1`  
**State-basis:** `2c2dccfb2961fd211b6c496514929919a3e9219a`

## Entry evidence

The read-only pilot provisioning verification is CLOSED / ACCEPTED:

- target PILOT proven;
- PostgreSQL read-only guard passed;
- decision: `PROVISIONING_GAP_FOUND`;
- Kelas 1: 20 active students, 11 sessions in the verified horizon, 10 reportable;
- Kelas 1 primary teacher participation: 0/10 reportable sessions;
- other shared gap: official Kelas 3B has no usable schedule/session;
- final-head repository CI: run `36642953383` = SUCCESS.

## This atomic task

Provision only the missing expected PRIMARY teacher participation records for
official class:

`IMTAQ-2026-1`

Verified business horizon:

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

Expected write budget: maximum 10 new participation rows.

## Canonical write path

Use the existing domain service:

`App\Domains\Academic\Services\TeacherParticipationRecorder::ensurePrimary()`

Do not insert rows directly and do not infer teacher identity from display names.
Teacher authority comes from each session's canonical `teachingAssignment.teacher_staff_id`.

## Boundary

CONTROLLED PILOT DATA WRITE ONLY.

No source, test, route, view, migration, schema, schedule, session, roster,
attendance-status, correction, lock, account, role, provider, deployment, or
production/staging mutation.

## Required contract

`codex/TASK_CONTEXTS/ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-FOR-ONE-CLASS.md`

## Exit

On PASS, create the change manifest and set next task to a read-only
post-write provisioning re-verification.

STOP for ChatGPT audit.
