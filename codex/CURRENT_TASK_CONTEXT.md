# CURRENT TASK CONTEXT

**Task:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B`
**State:** `COMPLETED / PASS / 12_OF_12_EXPECTED_PRIMARY`
**Current phase:** `ACADEMIC / CONTROLLED PILOT K2B TEACHER PARTICIPATION PROVISIONING`
**Branch:** `chore/academic-wali-pilot-primary-k2a`
**Entry basis:** `38e14520c08daace2fc16b3608c73aa168fed471`
**Final evidence CI:** `36826834884` = SUCCESS on exact pushed evidence HEAD `43938305d5c556627aa2e13d2b5887fe1502bac0`

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
- K2B = 12 reportable sessions, 0/12 expected PRIMARY;
- K3A = 14 reportable sessions, 0/14 expected PRIMARY;
- K3B = 14 canonical joint rules and 12 reportable joint sessions; expected PRIMARY remains 0/12;
- current locks = 0;
- Waka authority = 1 effective role / 1 linked staff;
- Public Academic AI = OFF;
- `IMP-S12-007` = `NOT_STARTED`;
- canonical queue marker = `SOC-MD-06`.

## Current executable task

The next executable task is the independent read-only re-verification after K2B
teacher-participation provisioning over:

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

The K2B task must prove exact current HEAD, PILOT identity,
`BEGIN TRANSACTION READ ONLY`, `transaction_read_only=on`, exactly 12
reportable K2B sessions, 12/12 authoritative teaching assignments, existing
PRIMARY 0/12, conflicts 0, lock blockers 0, K1 10/10, K2A 12/12, and the
ratified K3B non-target baseline of 14 joint rules, 12 joint sessions, and
K2B and K3B must remain 12/12 from the same shared rows.

No K2B write is authorized by this context; the next task must execute its
own preflight before opening the canonical write path.

No database write is authorized by this routing checkpoint. No source, test,
migration, schema, config, schedule, roster, account, role, AI/provider,
staging, production, deployment, or main-merge change is authorized.

## Required contract

`codex/TASK_CONTEXTS/ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2B.md`

## Next task

`ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2B`
