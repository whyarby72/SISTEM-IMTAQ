# CURRENT TASK CONTEXT

**Task:** `ACADEMIC-WALI-PILOT-K3A-PRIMARY-TEACHER-PARTICIPATION-READINESS-RECONCILIATION`
**State:** `COMPLETED / READ_ONLY / K3A_PRIMARY_PROVISIONING_READY_FOR_CONTROLLED_PREFLIGHT`
**Current phase:** `ACADEMIC / READ-ONLY K3A PROVISIONING READINESS RECONCILIATION`
**Branch:** `chore/academic-wali-pilot-primary-k2a`
**Entry basis:** `7cf929cbec32b48c17b1b0aac9ab8d9ea6e7d31a`
**Final evidence CI:** `36844093403` = SUCCESS on exact HEAD

K2B provisioning and independent read-only re-verification are closed. K3A
readiness is now reconciled as 14 standalone reportable sessions with 14
authoritative teacher mappings and 14 deterministic missing PRIMARY rows.

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

## Current executable task

This task completed the read-only K3A provisioning-readiness reconciliation over:

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

The K2B task must prove exact current HEAD, PILOT identity,
`BEGIN TRANSACTION READ ONLY`, `transaction_read_only=on`, exact 14/14 K3A
canonical/reportable standalone sessions, 14/14 authoritative teacher
matches, K3A missing physical rows 14, K1 10/10, K2A 12/12, K2B/K3B shared
12/12, and zero side effects.

No further K2B or K3B write is authorized by this context.

No database write is authorized by this routing checkpoint. No source, test,
migration, schema, config, schedule, roster, account, role, AI/provider,
staging, production, deployment, or main-merge change is authorized.

## Required contract

`codex/TASK_CONTEXTS/ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2B.md`

## Next task

Return to ChatGPT/project-owner audit; any K3A provisioning requires separate authorization.
