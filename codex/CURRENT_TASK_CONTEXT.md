# CURRENT TASK CONTEXT

**Task:** `ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2B`
**State:** `COMPLETED / READ_ONLY / K2B_REMEDIATION_VERIFIED_SHARED_JOINT_COVERAGE`
**Current phase:** `ACADEMIC / READ-ONLY POST-WRITE K2B/K3B SHARED COVERAGE REVERIFICATION`
**Branch:** `chore/academic-wali-pilot-primary-k2a`
**Entry basis:** `2d870cd0ad88b0349f2b6f32a2980b4e1401a4ec`
**Final evidence CI:** `36835854259` = SUCCESS on exact HEAD

K2B provisioning passed: 12 expected PRIMARY rows were created through the
canonical service for the exact 12 joint K2B+K3B sessions. Both class views
resolve the same 12 physical rows. Independent read-only postflight passed.

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

This task completed the independent read-only re-verification after K2B teacher-participation provisioning over:

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

The K2B task must prove exact current HEAD, PILOT identity,
`BEGIN TRANSACTION READ ONLY`, `transaction_read_only=on`, exact 12/12 K2B/K3B
session-set equality, 12/12 canonical teacher matches, shared physical-row
equality, K1 10/10, K2A 12/12, K3A 0/14, and zero side effects.

No further K2B or K3B write is authorized by this context.

No database write is authorized by this routing checkpoint. No source, test,
migration, schema, config, schedule, roster, account, role, AI/provider,
staging, production, deployment, or main-merge change is authorized.

## Required contract

`codex/TASK_CONTEXTS/ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2B.md`

## Next task

Return to ChatGPT/project-owner audit; any next class provisioning requires separate authorization.
