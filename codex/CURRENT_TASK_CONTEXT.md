# CURRENT TASK CONTEXT

**Task:** `ACADEMIC-WALI-PILOT-K3B-SCHEDULE-RULE-DRIFT-RECONCILIATION`
**State:** `COMPLETED / HOLD / OPTION_C`
**Current phase:** `ACADEMIC / K3B READ-ONLY SCHEDULE PROVENANCE RECONCILIATION`
**Branch:** `chore/academic-wali-pilot-primary-k2a`
**Entry basis:** `734d849e1bafd13f44a1a33d136e2ef9ce79a3c8`
**Final evidence CI:** `36788526133` = SUCCESS on exact entry HEAD

K2B remains blocked because current K3B schedule state is invalid/conflicting:
14 published rules, 0 sessions, 2 same-class-scope overlap pairs, and a
single K3B group on each rule despite K2B-anchored assignments.

The preceding CI blocker was a wall-clock month-boundary fixture defect in
`AttendanceSemanticMetricsServiceTest`. It was stabilized with a fixed
Asia/Jakarta test clock; production attendance semantics were unchanged.

## Entry evidence

K2A post-write re-verification is CLOSED / ACCEPTED:

- K1 = 10/10 expected PRIMARY;
- K2A = 12/12 expected PRIMARY with canonical semantics;
- K2B = 12 reportable sessions, 0/12 expected PRIMARY;
- K3A = 14 reportable sessions, 0/14 expected PRIMARY;
- K3B = 0 usable schedule rules and 0 sessions;
- current locks = 0;
- Waka authority = 1 effective role / 1 linked staff;
- Public Academic AI = OFF;
- `IMP-S12-007` = `NOT_STARTED`;
- canonical queue marker = `SOC-MD-06`.

## Current executable task

Prepare for controlled PILOT provisioning of official class `IMTAQ-2026-2B`
over:

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

Before any write, prove exact current HEAD, PILOT identity,
`BEGIN TRANSACTION READ ONLY`, `transaction_read_only=on`, exactly 12
reportable sessions, 12/12 authoritative teaching assignments, existing
PRIMARY 0/12, conflicts 0, lock blockers 0, K1 10/10, K2A 12/12, and K3A/K3B
unchanged.

The only future write path is:
`App\Domains\Academic\Services\TeacherParticipationRecorder::ensurePrimary()`
with a maximum of 12 K2B-only rows and required semantics
`PRIMARY/TEACHING_ASSIGNMENT/EXPECTED/attendance_status=NULL`.

No database write is authorized by this routing checkpoint. No source, test,
migration, schema, config, schedule, roster, account, role, AI/provider,
staging, production, deployment, or main-merge change is authorized.

## Required contract

`codex/TASK_CONTEXTS/ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B.md`

## Next task after successful write

`ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2B`
