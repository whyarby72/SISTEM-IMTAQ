# CURRENT TASK CONTEXT

**Task:** `ACADEMIC-WALI-PILOT-PROVISIONING-VERIFICATION`  
**State:** `READY_FOR_EXECUTION`  
**Current phase:** `ACADEMIC / WALI KELAS PILOT PROVISIONING VERIFICATION`  
**Branch:** `chore/academic-wali-pilot-provisioning-verification`  
**State-basis:** `467133e28c10cf6072468bc624bb2e82dd6ec172`

## Entry gate

The preceding Wali daily workflow review is closed:

`APPLICATION_READY_PROVISIONING_NOT_VERIFIED`

Application behavior is already evidenced. This task verifies only whether the
actual pilot environment contains the operational data needed for Wali Kelas
attendance use.

Exact current repository run:
- GitHub Actions run `36635904656`
- head `467133e28c10cf6072468bc624bb2e82dd6ec172`
- conclusion `SUCCESS`

## Verification target

Read-only pilot verification of:
- user ↔ staff linkage;
- effective WALI_KELAS role;
- effective homeroom/class assignment;
- active academic year/class;
- current teaching schedule / generated sessions;
- session participant roster/snapshot;
- teacher participation/obligation;
- attendance-period lock state;
- Waka correction/escalation authority.

## Safety boundary

PILOT READ-ONLY ONLY.

No write, seed, migrate, correction, attendance entry, session generation,
login side-effect test, or configuration mutation is authorized.

If the environment cannot be proven to be the intended pilot target, STOP.

## Required contract

`codex/TASK_CONTEXTS/ACADEMIC-WALI-PILOT-PROVISIONING-VERIFICATION.md`

## Product queue

If pilot provisioning passes, return to:

`ACADEMIC-WEB-GRADE-WORKFLOW-SURFACE-DESIGN`

If a provisioning gap exists, recommend one smallest provisioning-remediation
task; do not fix it in this task.

Public Academic AI remains OFF.
`IMP-S12-007` remains NOT_STARTED.
Canonical queue gate remains `SOC-MD-06`.

## Exit

Create the verification artifact, update evidence/state only, commit/push, then
STOP for ChatGPT audit.
