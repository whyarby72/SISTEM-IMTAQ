# K2B provisioning re-verification

**Task ID:** `ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2B`
**Type:** `READ_ONLY_POST_WRITE_PROVISIONING_REVERIFICATION`
**Status:** `READY`
**Branch:** `chore/academic-wali-pilot-primary-k2a`
**Frozen horizon:** `[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

No database write is authorized. Before every query prove PILOT identity,
begin `TRANSACTION READ ONLY`, and prove `transaction_read_only=on`.

Recompute, using aggregates and no names:

- K2B exact 12 reportable sessions and K3B exact same joint session set;
- physical participation rows = 12;
- K2B = 12/12 and K3B = 12/12 from the same participation row set;
- K2B-only/K3B-only = 0 and exact set equality = true;
- canonical teacher match and `PRIMARY / TEACHING_ASSIGNMENT / EXPECTED /
  attendance_status NULL` = 12/12;
- duplicate/conflict = 0;
- K1 = 10/10, K2A = 12/12, K3A = 0/14;
- no student attendance, teacher attendance, correction, schedule, session,
  scope-group, roster, account, role, or lock side effects;
- preserve `SOC-MD-06`, `IMP-S12-007=NOT_STARTED`, and Public Academic AI `OFF`.

Create the read-only review and reconcile repository metadata only. Do not
provision K3B separately and do not execute another K2B write.

