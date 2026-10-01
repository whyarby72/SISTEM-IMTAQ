# CURRENT TASK CONTEXT

**Task:** `ACADEMIC-WALI-PILOT-K3B-SCHEDULE-RULE-REMEDIATION-DESIGN`
**State:** `COMPLETED / HOLD / SESSION_PROVENANCE_DIVERGENCE`
**Current phase:** `ACADEMIC / K3B READ-ONLY REMEDIATION DESIGN AND DRY RUN`
**Branch:** `chore/academic-wali-pilot-primary-k2a`
**Entry basis:** `d933a24dd7faf3518538bbee721ea0311244cc54`
**Final evidence CI:** `36809446297` = SUCCESS on exact final evidence HEAD `f29763e739cc360821749c7cbfc60266adcf459b`

K2B remains blocked because the current K3B evidence is internally divergent:
14 published joint rules are present and the current read-only PILOT contains
12 reportable joint sessions, while the prior entry evidence recorded zero
sessions. The two apparent recurrence pairs have disjoint week sets; no rule
mutation is authorized until session-generation provenance is reconciled.

The preceding CI blocker was a wall-clock month-boundary fixture defect in
`AttendanceSemanticMetricsServiceTest`. It was stabilized with a fixed
Asia/Jakarta test clock; production attendance semantics were unchanged.

## Entry evidence

K2A post-write re-verification is CLOSED / ACCEPTED:

- K1 = 10/10 expected PRIMARY;
- K2A = 12/12 expected PRIMARY with canonical semantics;
- K2B = 12 reportable sessions, 0/12 expected PRIMARY;
- K3A = 14 reportable sessions, 0/14 expected PRIMARY;
- K3B = 14 canonical joint rules; current read-only PILOT shows 12 reportable joint sessions; prior zero-session entry evidence is stale/unreconciled;
- current locks = 0;
- Waka authority = 1 effective role / 1 linked staff;
- Public Academic AI = OFF;
- `IMP-S12-007` = `NOT_STARTED`;
- canonical queue marker = `SOC-MD-06`.

## Current executable task

Complete the read-only K3B remediation design and session-provenance
reconciliation over:

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

The review must prove exact current HEAD, PILOT identity,
`BEGIN TRANSACTION READ ONLY`, `transaction_read_only=on`, anchor/group/
canonical scope counts, recurrence intersections, session implication,
canonical write-path availability, and cross-class aggregate invariants.

No rule/session/attendance/participation write is authorized. K2B remains
blocked until the session-provenance divergence is separately resolved.

No database write is authorized by this routing checkpoint. No source, test,
migration, schema, config, schedule, roster, account, role, AI/provider,
staging, production, deployment, or main-merge change is authorized.

## Required contract

`codex/TASK_CONTEXTS/ACADEMIC-WALI-PILOT-K3B-SCHEDULE-RULE-REMEDIATION-DESIGN.md`

## Next task

`RETURN_TO_CHATGPT_FOR_K3B_REMEDIATION_DESIGN_AUDIT`
