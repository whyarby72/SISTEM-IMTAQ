# AI Academic Assistant — Phase 2C July 2026 Readiness Dossier

Status: read-only dossier; no certification decision has been written.
Generated: 2026-09-17

## Historical legacy candidate

Source: `LEGACY_MONTHLY_SNAPSHOT`, period `2026-07`.

| Evidence | Observed |
|---|---:|
| Monthly summary rows | 5 |
| Roster | 84 |
| Present | 1,126 |
| Permission | 39 |
| Sick | 29 |
| Absent | 4 |
| Eligible | 1,198 |
| Non-eligible | 142 |
| Reconciliation | PASS: eligible = present + permission + sick + absent |
| Import batches | `IMTAQ-JUL-2026-MONTHLY`, `IMTAQ-202607-STUDENT-SNAPSHOT` |
| Summary status | PUBLISHED |
| Explicit source certification | NONE; certification migration not applied to pilot DB |
| Publication audit evidence | 2 `MONTHLY_ATTENDANCE_REPORT_PUBLISHED` records for `2026-07` |

Reference historical rate: `1,126 / 1,198 = 93.99%`. This remains a candidate value only. PUBLISHED is not CERTIFIED.

## Official class-lineage candidate matrix

The existing deterministic July mapping service and current official active class rows provide one candidate for each source reference. These are not approved certification mappings until human approval is recorded in the new lineage table.

| Legacy reference | Candidate official class | Evidence | Ambiguity | Recommendation |
|---|---|---|---|---|
| 1 | `IMTAQ-2026-1` | active class, academic year `2026/2027`, unique | none observed | prepare for approval |
| 2A | `IMTAQ-2026-2A` | active class, academic year `2026/2027`, unique | none observed | prepare for approval |
| 2B | `IMTAQ-2026-2B` | active class, academic year `2026/2027`, unique | none observed | prepare for approval |
| 3A | `IMTAQ-2026-3A` | active class, academic year `2026/2027`, unique | none observed | prepare for approval |
| 3B | `IMTAQ-2026-3B` | active class, academic year `2026/2027`, unique | none observed | prepare for approval |

## Live July readiness

Canonical live denominator uses completed ClassSessions only. Historical PLANNED sessions are an operational work queue and are excluded from the official denominator; CANCELLED and RESCHEDULED source sessions are excluded.

| Official class | Completed sessions | Historical planned queue | Eligible | Resolved | Missing | Completeness |
|---|---:|---:|---:|---:|---:|---:|
| 1 | 6 | 0 | 120 | 120 | 0 | 100% |
| 2A | 41 | 0 | 779 | 779 | 0 | 100% |
| 2B | 0 | 52 joint events | 0 | 0 | 0 | n/a |
| 3A | 28 | 20 | 420 | 420 | 0 | 100% |
| 3B | 0 | 52 joint events | 0 | 0 | 0 | n/a |
| Institution | 75 | 72 | 1,319 | 1,319 | 0 | 100% |

Completed-session attendance counts: PRESENT 1,275; PERMISSION 33; SICK 11; ABSENT 0; LATE 0; EXCUSED 0. The 52 joint 2B+3B events are 52 institutional events and 104 class participations, not 104 institutional events.

`LIVE_JULY_CERTIFIABLE = NO`: no explicit certification has been recorded, joint/planned queue work remains, and source authority still requires human certification evidence. The complete completed-session denominator does not by itself publish an official rate.

## Faizal regression

Faizal’s legacy candidate remains present 9, permission 11, eligible 20, rate 45%. The live candidate is recalculated from canonical session facts. Before explicit certification, the resolver must return `DATA_NOT_CERTIFIED` (or another blocking state), with no silent source selection.

## Decisions enforced in Phase 2C

- LATE is resolved physical attendance and is included in the official numerator.
- EXCUSED is resolved, included in Tidak Hadir, and remains distinct from PERMISSION.
- NON_ELIGIBLE and MISSING are excluded from Tidak Hadir.
- Historical PLANNED is visible as work queue, not denominator and not student absence.
- Official rate is nullable unless completeness, source authority, certification, and data quality gates pass.
- No persisted historical rewrite or automatic certification is performed.
