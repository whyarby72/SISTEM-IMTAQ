# CURRENT TASK CONTEXT

**Task:** `SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1`
**State:** `IMPLEMENTED / PENDING_DISPOSABLE_POSTGRES_AND_EXACT_CI`
**Branch:** `feat/super-admin-user-access-preferences`
**Task contract:** `codex/TASK_CONTEXTS/SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1.md`

This is a controlled full-stack User & Akses implementation. No PILOT
database write, Academic UAT action, AI/provider mutation, or production
cutover is authorized. Required closeout is disposable PostgreSQL replay,
focused tests, foundation regression, and exact GitHub Actions evidence.

---

**Task:** `ACADEMIC-WALI-PILOT-FIRST-DAY-CONTROLLED-ATTENDANCE-UAT`
**State:** `HOLD / HUMAN_OBSERVATION_REQUIRED`
**Current phase:** `ACADEMIC / FIRST-DAY CONTROLLED PILOT PRE-WRITE GATE`
**Branch:** `chore/academic-wali-pilot-primary-k2a`
**Entry basis:** `5f71d32c57a5d9061fb9daf08e89d7e3658841ef`
**Exact CI:** `36933559280` = SUCCESS on exact HEAD

K2B provisioning and independent read-only re-verification are closed. K3A
controlled provisioning and this independent read-only postflight are closed:
K3A remains 14/14, global unique physical obligations are 48/48, and no
expected PRIMARY provisioning gap remains for the frozen horizon.

The preceding CI blocker was a wall-clock month-boundary fixture defect in
`AttendanceSemanticMetricsServiceTest`. It was stabilized with a fixed
Asia/Jakarta test clock; production attendance semantics were unchanged.

## Entry evidence

K2A post-write re-verification is CLOSED / ACCEPTED:

- K1 = 10/10 expected PRIMARY;
- K2A = 12/12 expected PRIMARY with canonical semantics;
- K2B = 12/12 expected PRIMARY;
- K3A = 14 reportable sessions, 0/14 expected PRIMARY;
- K3B = 14 canonical joint rules and 12 reportable joint sessions; 12/12 shared PRIMARY;
- current locks = 0;
- Waka authority = 1 effective role / 1 linked staff;
- Public Academic AI = OFF;
- `IMP-S12-007` = `NOT_STARTED`;
- canonical queue marker = `SOC-MD-06`.

## Completed controlled provisioning

The K3A write covered `[2026-09-30 00:00, 2026-10-07 00:00) Asia/Jakarta`.
Fresh preflight proved PILOT `imtaq`, PostgreSQL 18.6,
`transaction_read_only=on`, exact 14 standalone reportable sessions,
14/14 authoritative mappings, zero pre-existing participation, conflicts,
locks, attendance facts, and correction facts. One outer transaction called
`TeacherParticipationRecorder::ensurePrimary()` exactly once per target and
persisted 14 K3A-only rows. Independent read-only postflight passed all
semantic, non-target, and side-effect guards.

## Required contract

`codex/REVIEWS/ACADEMIC-WALI-PILOT-OPERATIONAL-READINESS-REASSESSMENT-AFTER-PROVISIONING-2026-10-02.md`

The reassessment is read-only. It must prove PILOT identity, frozen-horizon
class/session/roster/snapshot/teacher participation readiness, lock and Waka
authority, and preserve the separation between controlled pilot readiness and
production readiness.

## Reassessment result

`READY_FOR_CONTROLLED_PILOT`

The five official classes are structurally ready for a separately authorized
first-day Wali UAT/pilot transaction. No database write or application-source
mutation is authorized by this task.

## Prior contract

`codex/REVIEWS/ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K3A-2026-10-02.md`

## Next task

Return to ChatGPT/project-owner for the next Academic operational-readiness
decision. Do not start K3B provisioning, another class provisioning, AI work,
or deployment automatically.
