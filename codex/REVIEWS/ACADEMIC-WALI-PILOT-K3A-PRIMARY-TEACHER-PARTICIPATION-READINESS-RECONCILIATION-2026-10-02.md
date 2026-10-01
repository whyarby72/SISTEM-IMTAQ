# K3A primary teacher participation readiness reconciliation

**Task:** `ACADEMIC-WALI-PILOT-K3A-PRIMARY-TEACHER-PARTICIPATION-READINESS-RECONCILIATION`
**Mode:** `READ_ONLY_PROVISIONING_READINESS_RECONCILIATION`
**Repository HEAD:** `7cf929cbec32b48c17b1b0aac9ab8d9ea6e7d31a`
**Exact CI:** run `36844093403`, SUCCESS on the exact HEAD.
**Horizon:** `[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

## Repository and PILOT guards

The branch is clean and local/remote HEADs match. The read-only transaction proved database `imtaq`, PostgreSQL 18.6, `transaction_read_only=on`, and Asia/Jakarta session semantics. No names, raw UUIDs, secrets, or unnecessary PII were recorded.

## K3A canonical session set

| Metric | Count |
|---|---:|
| Anchor ClassSession rows (`class_id=IMTAQ-2026-3A`) | 14 |
| Canonical class-scoped sessions | 14 |
| Reportable sessions | 14 |
| Cancelled/non-reportable canonical sessions | 0 |
| Standalone sessions | 14 |
| Joint sessions | 0 |
| Scope-not-provable sessions | 0 |

All 14 canonical sessions have only `{IMTAQ-2026-3A}` in their effective scope, with zero `JOINT_SCOPE` rows. Classification: `STANDALONE_K3A`. There is no K2B/K3B shared physical session in the K3A target set.

## Session and teacher integrity

All 14 sessions are scheduled, have valid schedule-rule linkage, valid teaching assignment and subject linkage, valid time bounds, no reschedule lineage, and no duplicate `(schedule_rule_id, planned_start_at)`. Authoritative mapping `ClassSession -> TeachingAssignment -> teacher_staff_id` is **14/14**; missing and ambiguous mappings are zero.

The wider joint-rule baseline remains 14 valid K2B/K3B joint rules, producing 12 K2B/K3B occurrences in this horizon. Those 12 are separate from the 14 standalone K3A sessions.

## Current participation and future physical write set

K3A expected PRIMARY coverage is **0/14**. Physical participation rows on the target are 0, missing physical PRIMARY rows are deterministically **14**, already-shared rows are 0, incompatible teacher rows are 0, and duplicate/conflicting PRIMARY counts are 0. A future write, if separately authorized, would therefore target exactly 14 K3A sessions; it must not touch K2B, K3B, or any other class.

Current non-target baseline is stable:

- K1: 10/10;
- K2A: 12/12;
- K2B: 12/12;
- K3B: 12/12 from the same 12 shared K2B/K3B rows;
- K3A: 0/14.

Target student attendance facts, teacher attendance facts, correction facts, and relevant locks are all zero. No schedule, session, scope-group, roster, account, role, or AI/provider mutation was performed.

## Readiness decision

`K3A_PRIMARY_PROVISIONING_READY_FOR_CONTROLLED_PREFLIGHT`

The exact standalone target, authoritative teacher mapping, current participation state, conflict/lock guards, and future physical write count are all provable. This decision authorizes only creation of a separate controlled provisioning contract with its own fresh mandatory preflight; it does **not** authorize a database write and does not execute K3A provisioning.

`SOC-MD-06`, `IMP-S12-007=NOT_STARTED`, and Public Academic AI `OFF` remain unchanged. Next action is return to ChatGPT/project-owner audit. No automatic K3A provisioning is performed.

