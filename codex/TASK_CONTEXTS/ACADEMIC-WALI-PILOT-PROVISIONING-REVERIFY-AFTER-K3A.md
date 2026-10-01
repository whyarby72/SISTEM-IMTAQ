# Task Context — K3A provisioning re-verification after K3A write

**Task ID:** `ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K3A`
**Type:** `READ_ONLY_POST_WRITE_PROVISIONING_REVERIFICATION`
**Branch:** `chore/academic-wali-pilot-primary-k2a`
**Horizon:** `[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

## Scope

Independently verify the K3A controlled provisioning result. This task must
not perform any database write, remediation, session generation, schedule or
roster change, lock change, attendance entry, correction, AI/provider change,
or deployment action.

## Mandatory read-only guards

- resolve and record exact current Git HEAD and final CI evidence;
- prove the target is the authorized PILOT database `imtaq` on PostgreSQL;
- begin `TRANSACTION READ ONLY` and prove `transaction_read_only=on`;
- aggregate only; do not record student/teacher names, secrets, or raw UUIDs.

## Required verification

- K3A remains exactly 14 reportable standalone sessions in the frozen horizon;
- K3A expected PRIMARY is 14/14, physical rows are exactly 14, and there
  are no duplicate/conflicting rows;
- every K3A row matches `ClassSession -> TeachingAssignment -> teacher_staff_id`;
- role is `PRIMARY`, obligation is `TEACHING_ASSIGNMENT`, participation is
  `EXPECTED`, and attendance/check-in/check-out fields remain NULL;
- K1 remains 10/10, K2A 12/12, K2B 12/12, and K3B 12/12 through the same
  shared K2B/K3B rows;
- K2B/K3B participation sets remain equal with no K3B-only rows;
- no student attendance facts, teacher attendance facts, correction facts,
  lock mutation, schedule/session/scope/roster mutation, or non-target
  provisioning side effect is present;
- current lock and Waka escalation authority remain read-only observations.

## Decision and routing

Expected decision if all checks pass:
`K3A_REMEDIATION_VERIFIED_REMAINING_GAPS`.

If any guard fails, stop and report `HOLD` with the exact failed invariant.
After this task, return to the ChatGPT/project-owner audit. Do not execute
K3B provisioning automatically; the existing `SOC-MD-06` gate, Public
Academic AI `OFF`, and `IMP-S12-007=NOT_STARTED` remain unchanged.
