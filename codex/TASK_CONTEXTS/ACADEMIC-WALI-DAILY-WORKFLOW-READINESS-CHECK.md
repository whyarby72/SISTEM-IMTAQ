# ACADEMIC WALI DAILY WORKFLOW READINESS CHECK

**Task ID:** `ACADEMIC-WALI-DAILY-WORKFLOW-READINESS-CHECK`  
**Type:** `READ_ONLY_OPERATIONAL_READINESS_REVIEW`  
**State:** `READY_FOR_EXECUTION`  
**State-basis:** `bc5b242aa505b588ed8caf485b68ae5af095f476`

## Objective

Verify whether the Wali Kelas daily Academic attendance journey is ready for a
controlled pilot.

Review this journey end to end:

`sign in → dashboard → authorized class/session → attendance entry → teacher attendance → finalize → dashboard monitoring → correction/escalation`

## Process contract

- Process owner: Wali Kelas.
- Oversight: Waka Akademik.
- Student attendance grain: one student participant × one class session.
- Teacher attendance grain: one teacher participation × one class session.
- Source of truth: class sessions, participant snapshots, validated attendance
  transactions, effective Wali role/homeroom assignment, and Academic semantic
  metrics.
- AI is not required. Public Academic AI remains OFF.

## Required checks

Confirm with repository evidence that:

1. an effective Wali Kelas identity and homeroom assignment are required;
2. the dashboard scopes data to the authorized class;
3. the dashboard exposes the attendance session work queue;
4. the queue distinguishes Belum diisi, Belum lengkap, and Sudah disahkan;
5. the session link opens the attendance workflow;
6. student attendance draft can be saved;
7. teacher attendance can be recorded where applicable;
8. finalization rejects incomplete required attendance;
9. successful finalization produces validated attendance;
10. dashboard/completeness state reflects the finalized transaction;
11. another class outside Wali authority is denied;
12. correction and period-lock paths are explicit;
13. audit and data-quality controls are present;
14. the daily workflow is materially usable on responsive/mobile layout;
15. Waka can monitor exceptions/completeness without replacing the Wali transaction path.

## Operational prerequisites

Application readiness does not prove that real pilot data is already provisioned.

Identify prerequisites needed before first use:
- active user and effective Staff linkage;
- effective WALI_KELAS role;
- active homeroom assignment;
- active class/year;
- teaching schedule and generated session;
- participant roster/snapshot when required;
- teacher participation/obligation when required;
- no blocking period lock for the intended operation.

Do not access or change pilot/staging/production data.

If application behavior is evidenced but real pilot provisioning is not, use:

`APPLICATION_READY_PROVISIONING_NOT_VERIFIED`

## Evidence standard

For each major step record:
- route/view evidence;
- service/domain evidence;
- authorization evidence;
- test evidence;
- validation/failure behavior;
- audit/DQ behavior.

Allowed step statuses:
`PASS_EVIDENCED`, `PASS_WITH_OPERATIONAL_PREREQUISITE`, `PARTIAL`,
`BLOCKED`, `NOT_EVIDENCED`.

Safe focused tests may run only against a disposable test database. No source or
test edits are authorized.

## Required artifact

Create:

`codex/REVIEWS/ACADEMIC-WALI-DAILY-WORKFLOW-READINESS-2026-09-29.md`

Include:
- executive readiness decision;
- workflow checklist;
- role/scope matrix;
- dashboard-to-attendance navigation;
- draft/finalize and teacher-attendance evidence;
- correction/lock/audit/DQ findings;
- responsive/mobile evidence;
- operational prerequisites;
- safe focused-test evidence;
- first-day checklist;
- escalation rules;
- clear separation of application readiness, pilot provisioning, and production readiness.

Final decision must be exactly one of:
- `READY_FOR_CONTROLLED_PILOT`
- `APPLICATION_READY_PROVISIONING_NOT_VERIFIED`
- `CONDITIONALLY_READY`
- `HOLD`

Production readiness is not assessed.

If no Wali daily blocker is found, return to:
`ACADEMIC-WEB-GRADE-WORKFLOW-SURFACE-DESIGN`.

## Allowed writes

Only:
- the readiness review artifact;
- `PROJECT_STATE.json`;
- `EVIDENCE_INDEX.json`;
- `TEST_MATRIX.csv`;
- `codex/CURRENT_TASK_CONTEXT.md`;
- `NEXT_ACTION.md`.

## Forbidden

No application source, route, controller, service, model, view, test, migration,
schema, runtime config, live data, provider, deployment, or main-branch change.
No live attendance transaction.

## Acceptance criteria

- AWDR-AC-01 baseline/evidence reconciled.
- AWDR-AC-02 Wali identity/role/homeroom prerequisites evidenced.
- AWDR-AC-03 dashboard class scoping evidenced.
- AWDR-AC-04 session work queue evidenced.
- AWDR-AC-05 dashboard-to-attendance navigation evidenced.
- AWDR-AC-06 draft/finalize/incomplete rejection evidenced.
- AWDR-AC-07 teacher attendance evidenced.
- AWDR-AC-08 correction/lock/audit/DQ evidenced.
- AWDR-AC-09 cross-class denial evidenced.
- AWDR-AC-10 responsive/mobile usability evidenced.
- AWDR-AC-11 provisioning separated from code readiness.
- AWDR-AC-12 one readiness decision and one next atomic task returned.

Closeout:

`ACADEMIC_WALI_DAILY_WORKFLOW_READINESS = COMPLETED / REVIEW_ONLY / PASS`

or HOLD.

`IMPLEMENTATION_AUTHORIZATION = NOT_AUTHORIZED`

Commit/push evidence only, then STOP for ChatGPT audit.
