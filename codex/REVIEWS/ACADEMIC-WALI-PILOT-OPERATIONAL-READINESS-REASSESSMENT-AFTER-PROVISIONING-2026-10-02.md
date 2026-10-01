# Academic Wali Pilot Operational Readiness Reassessment

**Task:** `ACADEMIC-WALI-PILOT-OPERATIONAL-READINESS-REASSESSMENT-AFTER-PROVISIONING`
**Mode:** `READ_ONLY_OPERATIONAL_READINESS_REASSESSMENT`
**Frozen horizon:** `[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`

## Executive decision

`READY_FOR_CONTROLLED_PILOT`

The current application workflow remains evidenced, all five official class
scopes have effective Wali authority and active homeroom assignments, the
frozen-horizon reportable session and participant prerequisites are present,
and canonical teacher PRIMARY coverage is complete. No blocking lock,
duplicate/conflict, attendance fact, correction fact, or cancelled-session
leakage was found in the aggregate read-only audit.

This decision authorizes only a separately approved, controlled first-day
Wali UAT/pilot attendance transaction. It is not production approval,
deployment approval, historical completeness, or AI activation.

## Repository and CI authority

| Check | Result |
|---|---|
| Repository | `whyarby72/SISTEM-IMTAQ` |
| Starting branch | `chore/academic-wali-pilot-primary-k2a` |
| Starting HEAD | `5f71d32c57a5d9061fb9daf08e89d7e3658841ef` |
| Starting CI | `36933559280 = SUCCESS` on the starting HEAD |
| Worktree/parity preflight | clean; local HEAD matched origin branch |
| Executable source drift in this task | none |

The final evidence CI for the metadata/review commit is recorded after push
in the repository evidence index. Historical evidence is not rewritten.

## PILOT and read-only guard

The live audit used the configured PILOT database `imtaq`. The aggregate query
ran inside `BEGIN TRANSACTION READ ONLY`; PostgreSQL reported:

| Guard | Result |
|---|---|
| database | `imtaq` |
| PostgreSQL | `18.6` |
| `transaction_read_only` | `on` |
| session timezone | `Asia/Jakarta` |
| transaction end | `ROLLBACK` |
| database writes | `NONE` |

No names, email addresses, raw UUIDs, secrets, or unnecessary PII were
recorded.

## Official class readiness matrix

The official set is exactly five active classes in one active academic year:
`IMTAQ-2026-1`, `IMTAQ-2026-2A`, `IMTAQ-2026-2B`, `IMTAQ-2026-3A`, and
`IMTAQ-2026-3B`. Each has one effective active homeroom row, one effective
Wali authority, and no aggregate orphan/duplicate authority signal.

| Class | Wali | Active roster | Reportable sessions | Participant snapshot | Teacher PRIMARY | Locks | Waka escalation | Attendance structural readiness | Final readiness |
|---|---:|---:|---:|---:|---:|---:|---:|---|---|
| IMTAQ-2026-1 | 1 | 20 | 10 | 10/10 | 10/10 | 0 | 1 | READY_FOR_ENTRY | READY_FOR_CONTROLLED_PILOT |
| IMTAQ-2026-2A | 1 | 19 | 12 | 12/12 | 12/12 | 0 | 1 | READY_FOR_ENTRY | READY_FOR_CONTROLLED_PILOT |
| IMTAQ-2026-2B | 1 | 10 | 12 | 12/12 | 12/12 | 0 | 1 | READY_FOR_ENTRY | READY_FOR_CONTROLLED_PILOT |
| IMTAQ-2026-3A | 1 | 15 | 14 | 14/14 | 14/14 | 0 | 1 | READY_FOR_ENTRY | READY_FOR_CONTROLLED_PILOT |
| IMTAQ-2026-3B | 1 | 20 | 12 | 12/12 | 12/12 | 0 | 1 | READY_FOR_ENTRY | READY_FOR_CONTROLLED_PILOT |

K2B and K3B are one joint physical session set. The audit used
`class_session_groups` for class attribution: K2B and K3B each show the same
12 reportable class participations, while global physical counting remains
48, not 60.

## Session, participant, and attendance evidence

The frozen-horizon physical status breakdown is:

| Status | Distinct physical sessions |
|---|---:|
| `PLANNED` | 48 |
| `CANCELLED` | 1 |

The cancelled session is excluded from reportable work. All reportable
sessions have required participant snapshots and all 48 have exactly one
expected PRIMARY obligation with canonical teacher mapping. Student
attendance facts and teacher attendance status/check-in/checkout facts are
zero; no session is already validated/finalized. Thus attendance entry is
permitted for all 48 reportable physical sessions, subject to normal
authorization and completeness rules.

Global invariants:

- `TOTAL_REPORTABLE_PHYSICAL_SESSIONS = 48`;
- `EXPECTED_PRIMARY_PHYSICAL_ROWS = 48`;
- `MISSING_PRIMARY_PHYSICAL_ROWS = 0`;
- duplicate/conflicting PRIMARY rows = `0`;
- cancelled-session PRIMARY leakage = `0`;
- K2B/K3B shared physical set = `12` sessions, exact equality;
- blocking attendance-period locks = `0`;
- effective Waka escalation authority with linked active staff = `1`.

## Wali workflow reconfirmation

The existing application review at
`codex/REVIEWS/ACADEMIC-WALI-DAILY-WORKFLOW-READINESS-2026-09-29.md` remains
the source/test evidence for sign-in, Wali scope resolution, dashboard and
session navigation, draft save, teacher attendance, completeness validation,
finalize, monitoring, cross-class denial, correction, post-lock correction,
audit, and DQ controls. No application source changed between that accepted
evidence and this reassessment. A live first-day transaction is intentionally
not simulated in this read-only task.

## Operational and production separation

| Dimension | Decision |
|---|---|
| Application workflow readiness | PASS evidenced |
| Controlled pilot provisioning | PASS: current aggregate prerequisites verified |
| Production readiness | NOT ASSESSED; separate gate required |
| Public Academic AI | OFF |
| `IMP-S12-007` | `NOT_STARTED` |
| `SOC-MD-06` | preserved unchanged |
| Historical attendance completeness | not required by this gate |

## Next atomic task

Return to ChatGPT/project owner for authorization of a separate controlled
first-day Wali UAT/pilot transaction plan. Do not start unrestricted rollout,
production deployment, monthly publication, or AI activation automatically.

## Closeout

| Item | Result |
|---|---|
| Source/application mutation | `NONE` |
| Database write | `NONE` |
| Migration/schema/runtime mutation | `NONE` |
| AI/provider/deployment mutation | `NONE` |
| Decision | `READY_FOR_CONTROLLED_PILOT` |
| Safe to close | `YES` |
