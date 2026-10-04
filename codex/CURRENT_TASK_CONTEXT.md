# CURRENT TASK CONTEXT

## Current controlled PILOT migration E1 closeout — 2026-10-04

**Current task:** `SUPER-ADMIN-USER-ACCESS-CONTROLLED-PILOT-MIGRATION-E1`
**State:** `CONTROLLED_PILOT_MIGRATION_COMPLETED / POSTFLIGHT_PASS`
**Branch / tested executable HEAD:** `feat/super-admin-user-access-preferences` / `a7fbcead149086e331cb5e7e22ed161a892f8a4b`
**Evidence:** `codex/CHANGE_MANIFESTS/SUPER-ADMIN-USER-ACCESS-PILOT-MIGRATION-E1-2026-10-04.md`
**CI authority:** run `37012461730` SUCCESS on exact executable HEAD; 15 passed, 536 warnings, 2267 assertions, 0 failed.
**Next atomic task:** `RETURN_TO_CHATGPT_FOR_USER_ACCESS_CONTROLLED_PILOT_MIGRATION_E1_AUDIT`

PILOT `imtaq` moved from 43 to 46 migrations through the guarded command.
Only `UserAccessFeatureSeeder` ran. Independent read-only postflight confirmed
9/9 ACTIVE accounts, 9/9 must-change-password false, three permission codes,
SUPER_ADMIN/WAKA/WALI grants 3/1/0, 13 features, zero overrides/preferences,
the accepted access matrix, and Public Academic AI OFF.

**REQUIRED NOW:** return to ChatGPT for E1 audit. Do not start another feature
or database action. Academic first-day UAT remains HOLD /
HUMAN_OBSERVATION_REQUIRED; SOC-MD-06 and IMP-S12-007 remain unchanged.

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
