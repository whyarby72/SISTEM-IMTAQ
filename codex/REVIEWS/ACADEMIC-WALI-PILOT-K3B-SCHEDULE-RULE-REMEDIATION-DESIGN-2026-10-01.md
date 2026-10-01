# K3B Schedule Rule Remediation Design

**Task:** `ACADEMIC-WALI-PILOT-K3B-SCHEDULE-RULE-REMEDIATION-DESIGN`  
**Mode:** `READ_ONLY_REMEDIATION_DESIGN_AND_DRY_RUN`  
**Repository:** `whyarby72/SISTEM-IMTAQ`  
**Branch:** `chore/academic-wali-pilot-primary-k2a`  
**Entry/final repository HEAD:** `d933a24dd7faf3518538bbee721ea0311244cc54`  
**Entry CI:** `36806464203` — SUCCESS on the entry HEAD
**Final evidence commit:** `f29763e739cc360821749c7cbfc60266adcf459b`
**Final evidence CI:** `36809446297` — SUCCESS on the exact final evidence HEAD
**Frozen horizon:** `[2026-09-30 00:00, 2026-10-07 00:00)` Asia/Jakarta

## Decision

`K3B_REMEDIATION_DESIGN_INCOMPLETE_HOLD`

The rule-scope design is internally resolvable: the current K3B rows are
joint K2B+K3B rows and the two apparent overlap pairs have disjoint
week-of-month recurrence sets. No rule mutation is recommended by this dry
run. The task remains HOLD because the authoritative repository entry says
K3B has zero sessions, while the current read-only PILOT query proves twelve
reportable joint sessions. The session-generation provenance for that change
is not present in the inspected audit log. K2B remains HOLD and is not
reopened.

## Read-only guard and repository reconciliation

| Check | Result |
|---|---|
| Branch | `chore/academic-wali-pilot-primary-k2a` |
| Local HEAD | `d933a24dd7faf3518538bbee721ea0311244cc54` |
| Remote HEAD | `d933a24dd7faf3518538bbee721ea0311244cc54` |
| Worktree | clean before review; metadata artifacts only in this closeout |
| CI | run `36806464203`, SUCCESS, exact HEAD |
| PILOT database | `imtaq`, PostgreSQL 18.6 |
| Technical/session timezone | UTC in application contract; audit transaction observed Asia/Jakarta session display |
| Transaction | `BEGIN TRANSACTION READ ONLY` |
| `transaction_read_only` | `on` |
| Transaction end | `ROLLBACK` |
| Database write | NONE |

No names, emails, credentials, student identifiers, or teacher identifiers
are recorded here.

The entry review claimed PostgreSQL technical timezone UTC; the current
direct psql read-only session displayed Asia/Jakarta. This is a session-level
observation only and was not changed by this task. It is retained as evidence
for the next runtime-state reconciliation rather than silently normalized.

## Historical `0` versus current `14` rule count

The three interpretations were reproduced against the current PILOT state:

| Interpretation | Predicate | Count |
|---|---|---:|
| A — teaching-assignment anchor | `TeachingAssignment.class_id = IMTAQ-2026-3B` | 0 |
| B — effective schedule scope | a `ScheduleRuleGroup.class_id = IMTAQ-2026-3B` | 14 |
| C — canonical application scope | `AcademicClassScopeResolver::forScheduleRule()` | 14 |

Classification: `BASELINE_QUERY_SCOPE_MISMATCH_CONFIRMED` for the rule
count. The previous zero is the anchor-class query result; the canonical
resolver uses groups when present, and all 14 current rows include K3B in
their groups. The current rows also include K2B in the same groups, so their
canonical scope is joint, not K3B-only.

This corrects the preceding reconciliation artifact's statement that each
row had only one K3B group. Current read-only evidence shows two groups per
row: `IMTAQ-2026-2B:JOINT_SCOPE` and `IMTAQ-2026-3B:JOINT_SCOPE`.

## Authoritative intended scope

`JOINT_K2B_K3B` is supported by independent evidence:

- `TeacherScheduleImporter::GROUP_CLASS_CODES` maps `TG-B` to both official
  class codes;
- `TeacherScheduleImportPlanner` emits both class rows for `TG-B`;
- the current database contains both group rows with `JOINT_SCOPE` on all 14
  K3B-scoped rules;
- `QA_SCHEDULE_V11_2026-09-07.md` records that TG-B overlaps are represented
  as joint scope;
- `AcademicClassScopeResolver::forScheduleRule()` treats groups as the
  effective scope.

The assignment anchor remains K2B by design; it is not evidence that K3B is
excluded. No row is classified `K3B_ONLY`, `K2B_ONLY`, `OTHER`, or
`UNRESOLVED`.

## Fourteen-rule remediation matrix

Fingerprints are deterministic ordinals ordered by weekday, start time, and
rule id. Subject references are non-PII subject codes.

| # | Assignment-safe reference | Effective scope | Recurrence / time | Subject | Occurrences in horizon | Prior overlap | Intended scope | Evidence | Proposed action | Confidence |
|---:|---|---|---|---|---|---|---|---|---|---|
| 01 | `...023-2B` | 2B+3B joint | Mon 08:00–09:30 / every week | `SUB-SIRAH` | Oct 5 | none | JOINT_K2B_K3B | TG-B mapping + two JOINT_SCOPE groups | KEEP | high |
| 02 | `...027-2B` | 2B+3B joint | Mon 10:00–11:00 / every week | `SUB-AQIDAH` | Oct 5 | none | JOINT_K2B_K3B | same | KEEP | high |
| 03 | `...034-2B` | 2B+3B joint | Tue 10:15–11:30 / every week | `SUB-HADITH` | Oct 6 | none | JOINT_K2B_K3B | same | KEEP | high |
| 04 | `...039-2B` | 2B+3B joint | Wed 08:00–09:30 / weeks 1,3 | `SUB-ENTREPRENEUR` | none | pair P1 | JOINT_K2B_K3B | TG-B mapping + groups | KEEP | high |
| 05 | `...040-2B` | 2B+3B joint | Wed 08:00–09:30 / weeks 2,4,5 | `SUB-ARABIC` | Sep 30 | pair P1 | JOINT_K2B_K3B | TG-B mapping + groups | KEEP | high |
| 06 | `...044-2B` | 2B+3B joint | Wed 10:00–11:00 / weeks 1,3 | `SUB-ENTREPRENEUR` | none | pair P2 | JOINT_K2B_K3B | TG-B mapping + groups | KEEP | high |
| 07 | `...045-2B` | 2B+3B joint | Wed 10:00–11:00 / weeks 2,4,5 | `SUB-ARABIC` | Sep 30 | pair P2 | JOINT_K2B_K3B | TG-B mapping + groups | KEEP | high |
| 08 | `...049-2B` | 2B+3B joint | Thu 08:00–09:30 / every week | `SUB-MUSTALAH` | Oct 1 | none | JOINT_K2B_K3B | same | KEEP | high |
| 09 | `...053-2B` | 2B+3B joint | Thu 10:00–11:00 / every week | `SUB-FIQH-DAWAH` | Oct 1 | none | JOINT_K2B_K3B | same | KEEP | high |
| 10 | `...004-2B` | 2B+3B joint | Sat 08:00–09:30 / every week | `SUB-MEDIA` | Oct 3 | none | JOINT_K2B_K3B | same | KEEP | high |
| 11 | `...008-2B` | 2B+3B joint | Sat 10:00–11:00 / every week | `SUB-MEDIA` | Oct 3 | none | JOINT_K2B_K3B | same | KEEP | high |
| 12 | `...010-2B` | 2B+3B joint | Sat 16:00–17:00 / every week | `SUB-JAZARIYYAH` | Oct 3 | none | JOINT_K2B_K3B | same | KEEP | high |
| 13 | `...014-2B` | 2B+3B joint | Sun 08:00–09:30 / every week | `SUB-SIRAH` | Oct 4 | none | JOINT_K2B_K3B | same | KEEP | high |
| 14 | `...018-2B` | 2B+3B joint | Sun 10:00–11:00 / every week | `SUB-ULUM-QURAN` | Oct 4 | none | JOINT_K2B_K3B | same | KEEP | high |

No `REVISE_SCOPE`, `REVISE_TIME`, `REVISE_RECURRENCE`, `ARCHIVE`, or
`REPLACE` action is supported by the evidence in this dry run.

## The two apparent overlap pairs

| Pair | Shared scope/time | Recurrence sets | Same dates? | Proposed resolution |
|---|---|---|---|---|
| P1: `...039-2B` / `...040-2B` | joint 2B+3B, Wednesday 08:00–09:30 | `{1,3}` vs `{2,4,5}` | No; zero common occurrence dates | KEEP both; not a real recurrence conflict |
| P2: `...044-2B` / `...045-2B` | joint 2B+3B, Wednesday 10:00–11:00 | `{1,3}` vs `{2,4,5}` | No; zero common occurrence dates | KEEP both; not a real recurrence conflict |

Different teachers are not used as the safety rationale. The decisive fact
is the recurrence intersection, consistent with `ScheduleRuleConflictChecker`
which searches for a first common occurrence before reporting a conflict.

## Session-generation implication and current divergence

The repository keeps generation separate: importer and publication establish
rule state; `ClassSessionGenerator::generate()` materializes sessions. No
generation was run by this task.

The expected rule-driven result in the frozen horizon is 12 occurrences:
the two week-of-month rules with weeks 1,3 have no occurrence in the horizon,
the paired rules with weeks 2,4,5 each occur on September 30, and the ten
weekly rules occur on October 1, 3, 4, 5, or 6. Current read-only PILOT data
contains exactly 12 reportable `PLANNED` joint sessions for these occurrences,
each scoped to both 2B and 3B, with zero attendance facts observed. This is
inconsistent with the entry claim of K3B sessions `0`.

The inspected audit log has schedule-rule importer/validate/publish events,
but no ClassSession audit event proving when or by which supported path these
12 sessions were materialized. Therefore this task cannot certify the session
provenance or silently treat the prior zero as authoritative.

## Canonical write-path assessment

The repository has canonical services for future schedule mutations:

- `ScheduleRuleRevisionService::revise()` preserves history, audits changes,
  copies groups, and invokes the generator for revised rules;
- `ScheduleRuleArchiveService::archive()` archives and audits without
  destructive historical deletion;
- `ScheduleRuleController` routes schedule administration through Waka/Super
  Admin authorization;
- `TeacherScheduleImporter` and `TeacherSchedulePublicationService` are the
  existing audited import/publication paths.

No application service gap was found for a future supported rule revision or
archive. No such service was invoked here. A separate session-provenance
reconciliation is required before any operational gate is advanced.

## Cross-class safety baseline

The current read-only aggregate showed:

| Class | Reportable sessions | Expected PRIMARY |
|---|---:|---:|
| K1 | 10 | 10/10 |
| K2A | 12 | 12/12 |
| K2B | 12 | 0/12 |
| K3A | 14 | 0/14 |
| K3B | 12 joint sessions | 0/12 |

All 49 sessions in the horizon were reportable except one cancelled session;
current period lock rows were zero. The required K3B `0`-session invariant
could not be reproduced and is therefore explicitly marked as a stale-entry
contradiction, not overwritten.

## Safety closeout

| Field | Result |
|---|---|
| Rule mutation | NONE |
| Session generation | NONE |
| Attendance/participation mutation | NONE |
| Database write | NONE; every query rolled back |
| K2B provisioning | NOT EXECUTED |
| K2B gate | HOLD |
| Public Academic AI | OFF |
| `IMP-S12-007` | NOT_STARTED |
| `SOC-MD-06` | PRESERVED |
| Primary decision | `K3B_REMEDIATION_DESIGN_INCOMPLETE_HOLD` |
| SAFE_TO_CLOSE | YES for this read-only design checkpoint |

Next atomic task: `RETURN_TO_CHATGPT_FOR_K3B_REMEDIATION_DESIGN_AUDIT`.
