# CURRENT TASK CONTEXT

## Current R1R closeout checkpoint — 2026-10-02

**Current task:** `SUPER-ADMIN-USER-ACCESS-PERMISSION-BOOTSTRAP-R1R-FIXTURE-ALIGNMENT`
**State:** `COMPLETED / PASS / RUN_37011165609`
**Branch / tested HEAD:** `feat/super-admin-user-access-preferences` / `979f6b26d39b4a308a818358afe9545b0050bc0e`
**Evidence:** `codex/CHANGE_MANIFESTS/SUPER-ADMIN-USER-ACCESS-PERMISSION-BOOTSTRAP-R1-2026-10-02.md`
**CI authority:** run `37011165609` SUCCESS on exact tested HEAD; 15 passed, 536 warnings, 2267 assertions, 0 failed.
**Next atomic task:** `RETURN_TO_CHATGPT_FOR_PERMISSION_BOOTSTRAP_R1_AUDIT`

R1/R1R source and fixture changes are validated only against disposable
PostgreSQL CI. Do not migrate, seed, broaden PILOT permissions, or perform
PILOT business-data writes in this checkpoint. The PILOT compatibility gate
remains owner-authorized work.

**REQUIRED NOW:** return to ChatGPT for the R1 audit. Academic first-day UAT
stays HOLD / HUMAN_OBSERVATION_REQUIRED; Public Academic AI stays OFF;
SOC-MD-06 and IMP-S12-007 remain unchanged. Database writes: NONE.

## Historical implementation context — not current execution authority

**Current executable task:** `SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1R`
**Current state:** `COMPLETED / PASS / RUN_36976414076`
**Branch:** `feat/super-admin-user-access-preferences`
**Task contract:** `codex/TASK_CONTEXTS/SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1R.md`
**Starting HEAD:** `79fb525c4369f7d31530a2e5f261499713e37ddd`
**Static validation:** `PASS`
**Focused/foundation database validation:** `PASS / exact GitHub Actions run 36976414076`
**Database write:** `NONE`
**Next atomic task:** `RETURN_TO_CHATGPT_FOR_SUPER_ADMIN_USER_ACCESS_AUDIT`

The historical V1 implementation state below is preserved as evidence; it is
not the current executable task.

**Task:** `SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1`
**State:** `IMPLEMENTED / PASS / RUN_36944117304`
**Branch:** `feat/super-admin-user-access-preferences`
**Task contract:** `codex/TASK_CONTEXTS/SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1.md`

This is a controlled full-stack User & Akses implementation. No PILOT
database write, Academic UAT action, AI/provider mutation, or production
cutover is authorized. Exact disposable PostgreSQL replay, focused tests,
foundation regression, and GitHub Actions evidence all passed.

**Exact implementation HEAD:** `c9c7ee3e57d1f4481d71d97c87ebec5082453150`
**Exact CI:** `36944491055` = SUCCESS

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
