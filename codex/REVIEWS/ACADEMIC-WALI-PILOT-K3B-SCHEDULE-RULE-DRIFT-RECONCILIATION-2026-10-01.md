# K3B Schedule Rule Drift Reconciliation

**Task:** `ACADEMIC-WALI-PILOT-K3B-SCHEDULE-RULE-DRIFT-RECONCILIATION`  
**Mode:** `READ_ONLY_PILOT_STATE_AND_PROVENANCE_RECONCILIATION`  
**Repository:** `whyrby72/SISTEM-IMTAQ`  
**Branch:** `chore/academic-wali-pilot-primary-k2a`  
**Entry HEAD:** `8edf768a420681b2755eb5402c110b5f269467ce`  
**Entry CI:** `36788526133` — SUCCESS  
**Frozen horizon:** `[2026-09-30 00:00, 2026-10-07 00:00)` Asia/Jakarta

## Decision

`K3B_SCHEDULE_RULE_DRIFT_INVALID_OR_CONFLICTING`

K2B controlled provisioning remains `HOLD`. No K2B write, schedule repair,
session generation, or other pilot mutation was performed.

## Read-only identity proof

| Marker | Result |
|---|---|
| Laravel environment | `local` |
| Laravel connection | `pgsql` |
| PostgreSQL database | `imtaq` |
| PostgreSQL version | `18.6 (Homebrew)` |
| PostgreSQL technical session timezone | `UTC` |
| Transaction | `BEGIN TRANSACTION READ ONLY` |
| `transaction_read_only` | `on` |
| Transaction end | `ROLLBACK` |

All queries were aggregate/non-PII. No names, emails, credentials, teacher
identifiers, or student identifiers were recorded.

## Current K3B rule set

| Metric | Result |
|---|---:|
| Total rules scoped to K3B | 14 |
| Usable rules (`workflow_status != ARCHIVED`) | 14 |
| `PUBLISHED` | 14 |
| Effective in frozen horizon | 14 |
| `EVERY_WEEK` | 10 |
| `WEEK_OF_MONTH` | 4 |
| Orphaned teaching assignments | 0 |
| Invalid subject linkage | 0 |
| Invalid teacher linkage | 0 |
| Invalid K3B group linkage | 0 |
| Invalid date ranges | 0 |
| Invalid/incomplete recurrence | 0 |
| Duplicate semantic rule groups | 0 |
| Overlap pairs on same class scope | 2 |
| Overlap pairs with same teacher | 0 |
| K3B sessions in horizon | 0 |
| K3B reportable sessions | 0 |

All 14 rules have a teaching assignment anchored to K2B and one K3B schedule
group. The expected joint 2B+3B scope is therefore not represented as a
two-class group on these current rows. This is a structural scope mismatch,
not merely a historical count difference.

The two overlap pairs share the same class scope and overlapping weekday/time
intervals. They have different teachers and subjects, so the teacher-conflict
count is zero, but the class-scope conflict count is two. This is sufficient
to reject the current rule set as a safe baseline for K2B progression.

## Provenance

Current rule timestamps, in Jakarta time:

- created: `2026-09-07 10:07:28 +07:00` for all 14 rules;
- updated: `2026-09-07 10:52:49 +07:00` for all 14 rules.

Aggregate audit evidence shows one coherent operational chain:

| Actor type | Source channel | Action | Count | Jakarta time range |
|---|---|---|---:|---|
| `IMPORTER` | `IMPORT` | `SCHEDULE_RECONCILED` | 14 | 10:30:55–10:30:56 |
| `SYSTEM` | `WEB` | `SCHEDULE_VALIDATED` | 14 | 10:45:45–10:45:46 |
| `SYSTEM` | `WEB` | `SCHEDULE_PUBLISHED` | 14 | 10:52:49 |

This proves a supported importer and publication path, but does not prove an
owner-approved correction of the earlier `0` baseline. The current rule
content remains invalid/conflicting despite traceable provenance.

## Why 14 rules produced 0 sessions

Repository inspection shows `ClassSessionGenerator::generate()` is an
explicit service call. It is invoked by schedule create/update flows and
schedule-rule revision, but importer and publication transition do not invoke
session generation. Publication changes workflow state and writes audit
records; it does not backfill `ClassSession` rows.

Therefore the current state is consistent with:

`imported rules -> validated -> published -> no generator invocation -> 0 sessions`

The zero-session state is a separate provisioning gap. It must not be repaired
in this reconciliation, and it cannot be advanced while the rule scope and
overlap defects remain unresolved.

## Cross-domain invariants

| Class | Reportable sessions | Expected PRIMARY |
|---|---:|---:|
| K1 | 10 | 10/10 |
| K2A | 12 | 12/12 |
| K2B | 12 | 0/12 |
| K3A | 14 | 0/14 |
| K3B | 0 | 0 |

K2B duplicate/conflict count was 0 and current-period lock blockers were 0.
No attendance, correction, lock, participation, schedule, session, roster,
account, or role write occurred.

## Safety closeout

| Field | Result |
|---|---|
| Database write | `NONE` |
| Application/source/test/migration/schema/config change | `NONE` |
| K2B provisioning | `NOT EXECUTED` |
| Public Academic AI | `OFF` |
| `IMP-S12-007` | `NOT_STARTED` |
| `SOC-MD-06` | PRESERVED |
| K2B write gate | `HOLD / K3B_SCHEDULE_RULE_DRIFT_INVALID_OR_CONFLICTING` |

Next step requires owner/ChatGPT audit of the K3B rule-scope and overlap
defects. K2B must not reopen until an authorized remediation decision exists
and a fresh read-only preflight follows it.
