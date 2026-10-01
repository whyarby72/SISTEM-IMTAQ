# K3B Joint Session Baseline Reconciliation

**Task:** `ACADEMIC-WALI-PILOT-K3B-JOINT-SESSION-BASELINE-RECONCILIATION`  
**Mode:** `READ_ONLY_SESSION_SCOPE_AND_PROVENANCE_RECONCILIATION`  
**Repository:** `whyarby72/SISTEM-IMTAQ`  
**Branch:** `chore/academic-wali-pilot-primary-k2a`  
**Exact current HEAD:** `e0ba57a99c1ed6588caf5563fa2bd5ae3ea0af5b`  
**Last executable/evidence CI:** `36809446297` — SUCCESS on `f29763e739cc360821749c7cbfc60266adcf459b`

## Decision

`K3B_JOINT_SESSION_BASELINE_RATIFIED`

The historical K3B zero-session result was an anchor-query scope blind spot,
not session creation drift. The canonical baseline is 14 valid joint rules
and 12 reportable joint sessions. No session repair, generation, schedule
rule change, or K2B provisioning was performed.

`K2B_CONTROLLED_WRITE_GATE = OPEN_FOR_FRESH_MANDATORY_PREFLIGHT`

This opens only the gate for a new K2B preflight. It does not authorize or
execute the K2B write in this task.

## Repository gate

| Check | Result |
|---|---|
| Local HEAD | `e0ba57a99c1ed6588caf5563fa2bd5ae3ea0af5b` |
| Remote HEAD | `e0ba57a99c1ed6588caf5563fa2bd5ae3ea0af5b` |
| Parity | YES |
| Worktree at start | CLEAN |
| Metadata-only delta | `e0ba57a` relative to executable evidence commit `f29763e` |
| CI | run `36809446297`, SUCCESS on `f29763e` |
| Application changes | NONE |

The exact-current HEAD is documentation/state-only after the last executable
evidence commit; no application workflow was changed.

## PILOT read-only proof

All business queries used explicit boundaries:

`[2026-09-30 00:00:00+07, 2026-10-07 00:00:00+07)`

| Marker | Result |
|---|---|
| Database | `imtaq` |
| PostgreSQL | 18.6 (Homebrew) |
| Direct psql session timezone | Asia/Jakarta |
| Laravel/application DB session contract | UTC (`config/database.php`, `DB_TIMEZONE` default) |
| Transaction | `BEGIN TRANSACTION READ ONLY` |
| `transaction_read_only` | `on` |
| End | `ROLLBACK` |
| Database write | NONE |

The direct psql timezone is recorded separately and is not treated as
application drift; repository configuration explicitly resolves the Laravel
PostgreSQL connection timezone to UTC while business boundaries remain
Asia/Jakarta.

## Three session counts

| Interpretation | Predicate | Count |
|---|---|---:|
| A — K3B anchor | `ClassSession.class_id = K3B` | 0 |
| B — K3B group scope | `ClassSessionGroup.class_id = K3B`, reportable | 12 |
| C — canonical application scope | `AcademicClassScopeResolver::forSession()` | 12 |

## Exact set identity

Non-PII set comparison produced:

| Set result | Count |
|---|---:|
| K2B anchor sessions | 12 |
| K3B canonical sessions | 12 |
| Intersection | 12 |
| K2B-only | 0 |
| K3B-only | 0 |
| `exact_set_equal` | `true` |

The historical K2B 12 and current canonical K3B 12 are the same database
rows. Raw UUIDs are intentionally omitted.

## Structural integrity of the 12 joint sessions

| Check | Result |
|---|---:|
| `schedule_rule_id` non-null | 12/12 |
| Accepted 14-rule membership | 12/12 |
| TeachingAssignment anchor K2B | 12/12 |
| Exactly K2B + K3B JOINT_SCOPE groups | 12/12 |
| Missing/extra scope groups | 0 |
| `session_source=SCHEDULED` | 12/12 |
| Reportable status | 12/12 |
| No reschedule lineage | 12/12 |
| Duplicate rule + planned start | 0 |
| Session subject matches assignment | 12/12 |
| Session assignment matches rule | 12/12 |
| Generator session-code convention | 12/12 |

## Expected versus actual occurrence set

The accepted 14 rules produce exactly 12 occurrences in the frozen horizon:
the week-of-month pairs `{1,3}` have no occurrence in this horizon, the
paired `{2,4,5}` rules occur on September 30, and the weekly rules occur on
October 1, 3, 4, 5, and 6.

| Set | Count |
|---|---:|
| `EXPECTED_RULE_OCCURRENCES` | 12 |
| `ACTUAL_JOINT_SESSIONS` | 12 |
| Matched rule/start/end tuples | 12 |
| Expected missing | 0 |
| Unexpected actual | 0 |

`EXPECTED_RULE_OCCURRENCES = ACTUAL_JOINT_SESSIONS` exactly; no calendar
block exception is needed.

## Temporal provenance

The 12 candidate session rows and their scope groups were created between
`2026-09-07 10:18:48+07` and `2026-09-07 10:18:50+07`. The prior zero-session
review commit `8edf768a420681b2755eb5402c110b5f269467ce` was committed at
`2026-10-01 05:57:48+07`. Therefore:

`SESSION_ROWS_PREEXISTED_PRIOR_ZERO_REVIEW`

The prior zero was not evidence that the rows were later created.

## Generation-path provenance

Classification:

`GENERATION_AUDIT_NOT_RECORDED`

No dedicated `ClassSession` generation audit event was found. This is explicit
data/audit debt, not proof of an unauthorized mutation. Schedule-rule
publication audit exists and predates the session rows.

The rows are:

`STRUCTURALLY_CONSISTENT_WITH_CLASS_SESSION_GENERATOR`

All 12 match the generator's schedule rule, anchor class, assignment,
planned timestamps, session-code convention, and two JOINT_SCOPE rows. This
does not prove which actor invoked generation.

## Historical zero root cause

`K3B_SESSION_BASELINE_QUERY_SCOPE_MISMATCH_CONFIRMED`

The anchor query returns zero because the generator stores the K2B teaching
assignment anchor in `ClassSession.class_id`. The canonical resolver uses
session scope groups, where the same rows include K3B. The 12 rows predate the
prior zero review, are exactly the same rows as the K2B set, and match the
accepted joint-rule occurrences.

## Operational baseline

| Class | Expected PRIMARY |
|---|---:|
| K1 | 10/10 |
| K2A | 12/12 |
| K2B | 0/12 |
| K3A | 0/14 |
| K3B canonical joint sessions | 0/12 |

Recurrence-aware conflict count is 0. Relevant period lock rows are 0.
Candidate attendance facts are 0, correction rows are 0, and no mutation was
performed.

## Closeout

| Field | Result |
|---|---|
| K3B rules | 14 valid canonical joint rules |
| K3B canonical sessions | 12 reportable joint sessions |
| K2B/K3B set equality | TRUE |
| Session repair/generation | NOT RUN |
| K2B provisioning | NOT RUN |
| K2B gate | OPEN_FOR_FRESH_MANDATORY_PREFLIGHT |
| `SOC-MD-06` | PRESERVED |
| `IMP-S12-007` | NOT_STARTED |
| Public Academic AI | OFF |
| Database write | NONE |
| SAFE_TO_CLOSE | YES |

Next atomic task: `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B`.
