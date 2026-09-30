# ACADEMIC WALI PILOT — PROVISIONING REVERIFY AFTER K2A

**Task ID:** `ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2A`  
**Task type:** `READ_ONLY_POST_WRITE_PROVISIONING_REVERIFICATION`  
**State:** `COMPLETED / READ_ONLY / K2A_REMEDIATION_VERIFIED_REMAINING_GAPS`  
**Audited branch:** `chore/academic-wali-pilot-primary-k2a`  
**Audited HEAD:** `b200948c51823d4698126aeb25250bfa5be7f02f`  
**Final CI:** `36660752193` = SUCCESS

## Purpose

Independently verify K1 and K2A provisioning integrity after the K2A
controlled write, recalculate K2B/K3A gaps, and reverify the K3B
schedule/session gap. This task is read-only and does not authorize K2B
provisioning.

## Required horizon

`[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

## Required guards

- prove authorized PILOT identity before business queries;
- use `BEGIN TRANSACTION READ ONLY`;
- prove `transaction_read_only=on`;
- use aggregate queries only;
- rollback the read-only transaction;
- do not access staging or production;
- do not expose secrets or PII.

## Verified result

- K1: 11 total, 10 reportable, 10/10 expected PRIMARY;
- K2A: 12 total, 12 reportable, 12/12 expected PRIMARY;
- K2A canonical teacher/role/obligation/participation semantics pass;
- K2A duplicate/conflict count = 0;
- K2A `attendance_status`, check-in/check-out, student attendance, and
  direct correction facts = 0;
- K2B: 12 reportable, 0/12 expected PRIMARY;
- K3A: 14 reportable, 0/14 expected PRIMARY;
- K3B: 0 usable schedule rules and 0 sessions;
- current locked period rows for official classes = 0;
- effective Waka authority = 1 role assignment / 1 linked staff identity;
- Public Academic AI = OFF;
- `IMP-S12-007` = `NOT_STARTED`;
- canonical queue marker = `SOC-MD-06`.

## Decision

`K2A_REMEDIATION_VERIFIED_REMAINING_GAPS`

Review artifact:

`codex/REVIEWS/ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2A-2026-09-30.md`

## Next atomic task

`ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B`

The next task is separate and must perform its own read-only preflight before
any authorized K2B write. No provisioning is performed by this task.

## Allowed repository writes

- this task context;
- the re-verification review;
- `PROJECT_STATE.json`;
- `EVIDENCE_INDEX.json`;
- `TEST_MATRIX.csv`;
- `NEXT_ACTION.md`;
- `codex/CURRENT_TASK_CONTEXT.md`.

## Forbidden

No database write, source/test/migration/schema/config change, schedule/session
generation, roster/account/role mutation, AI/provider mutation, deployment, or
main merge.
