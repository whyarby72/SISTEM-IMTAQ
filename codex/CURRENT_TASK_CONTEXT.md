# CURRENT TASK CONTEXT

**Task:** `ACADEMIC-WALI-DAILY-WORKFLOW-READINESS-CHECK`  
**State:** `READY_FOR_EXECUTION`  
**Current phase:** `ACADEMIC / WALI KELAS DAILY OPERATIONAL READINESS`  
**Branch:** `chore/academic-wali-daily-workflow-readiness-check`  
**State-basis:** `bc5b242aa505b588ed8caf485b68ae5af095f476`

## Why this task

The Academic Web Completion Review established that attendance and scheduling
are complete-evidenced, while overall Academic Web completion is 4/10
end-to-end capabilities.

The Project Owner requested a focused operational check before Wali Kelas uses
daily attendance in practice.

## Exact journey

`sign in → dashboard → authorized class/session → attendance entry → teacher attendance → finalize → dashboard monitoring → correction/escalation`

## Required contract

`codex/TASK_CONTEXTS/ACADEMIC-WALI-DAILY-WORKFLOW-READINESS-CHECK.md`

## Important distinction

Separate:
- application workflow readiness;
- operational pilot provisioning;
- production readiness.

Do not infer live provisioning from source/tests alone.

## Existing next product task

If no Wali daily blocker is found, return to:

`ACADEMIC-WEB-GRADE-WORKFLOW-SURFACE-DESIGN`

## Boundary

REVIEW ONLY.

No source/test/view/route/schema/database/provider/deployment mutation.
Public Academic AI remains OFF.
`IMP-S12-007` remains NOT_STARTED.
Canonical queue gate remains `SOC-MD-06`.

## Exit

Create the readiness review artifact, update evidence/state only, commit/push,
then STOP for ChatGPT audit.
