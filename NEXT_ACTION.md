# NEXT ACTION

## Academic Wali UAT R3A1 closeout — 2026-10-05

**Current task:** `ACADEMIC-WALI-UAT-R3A1-FUTURE-OCCURRENCE-GUARD`
**State:** `COMPLETED / PASS / ACADEMIC_WALI_R3A1_IMPLEMENTED_PASS`
**Starting HEAD:** `6cfd0284b73153c27bb6c5667aeb49465759651b`
**Tested executable HEAD:** `7442dac3466d4abfca5bc0c17a80e8e6db713234`
**Exact CI:** `37245865829` = SUCCESS
**Manifest:** `codex/CHANGE_MANIFESTS/ACADEMIC-WALI-UAT-R3A1-FUTURE-OCCURRENCE-GUARD-2026-10-05.md`
**Database access/write:** `NONE / NONE`
**Next atomic task:** `R3A1_SOURCE_READY_FOR_CHATGPT_AUDIT`

Do not automatically resume human UAT, start R3B, or resume Grade G3.
Public Academic AI remains OFF; Academic Web remains `4/10 = 40%
COMPLETE_EVIDENCED`; `SOC-MD-06` is unchanged; `IMP-S12-007` remains
`NOT_STARTED`.

## Academic Wali dashboard maturity R1 closeout — 2026-10-04

**Current task:** `ACADEMIC-WALI-DASHBOARD-MATURITY-R1-OPERATIONAL-HOME`
**State:** `COMPLETED / PASS / WALI_DASHBOARD_R1_IMPLEMENTED_PASS`
**Tested executable HEAD:** `a857e341e88dc195add3f14a9a75d11c63a6734b`
**Exact CI:** `37204858961` = SUCCESS
**Manifest:** `codex/CHANGE_MANIFESTS/ACADEMIC-WALI-DASHBOARD-MATURITY-R1-2026-10-04.md`
**Database access/write:** `NONE / NONE`
**Next atomic task:** `RETURN_TO_CHATGPT_FOR_ACADEMIC_WALI_DASHBOARD_R1_AUDIT`

The dashboard is operational-first for Wali Kelas and remains a read model
over canonical attendance/session facts. Grade G1/G2 stay PASS; Grade G3 is
`DEFERRED_BY_OWNER_PRIORITY`. Academic Web remains `4/10 = 40%`. Do not start
attendance UAT, dashboard R2, or Grade G3 automatically. Public Academic AI
remains OFF; `IMP-S12-007` and canonical queue marker `SOC-MD-06` are
unchanged.

## Academic grade workflow G2 closeout — 2026-10-04

**Current task:** `ACADEMIC-WEB-GRADE-WORKFLOW-G2-DRAFT-ENTRY-STATE-HARDENING`
**State:** `COMPLETED / PASS / GRADE_G2_IMPLEMENTED_PASS`
**Tested executable HEAD:** `b3878d9f02fbbde06dc9e23b2562e8ad8eaacd88`
**Exact CI:** `37200924044` = SUCCESS
**Manifest:** `codex/CHANGE_MANIFESTS/ACADEMIC-WEB-GRADE-WORKFLOW-G2-2026-10-04.md`
**Database write:** `NONE`
**Next atomic task:** `RETURN_TO_CHATGPT_FOR_ACADEMIC_WEB_GRADE_G2_AUDIT`

G2 is complete with DRAFT-only domain hardening, exact teacher authority,
server-owned provenance, atomic batch persistence, optimistic versioning,
no-op/missing/zero semantics, and role/state-correct UI. Academic Web remains
`4/10 = 40% COMPLETE_EVIDENCED`. Do not start G3. Public Academic AI remains
OFF; `IMP-S12-007` and canonical queue marker `SOC-MD-06` are unchanged.

## Academic grade workflow G1 closeout — 2026-10-04

**Current task:** `ACADEMIC-WEB-GRADE-WORKFLOW-G1-AUTHORIZATION-READ-SURFACE`
**State:** `COMPLETED / PASS / GRADE_G1_IMPLEMENTED_PASS`
**Tested source basis:** `8800c5c6a234e3f11c5eddcfdd9be34ce99cf1cc`
**Exact CI:** `37194977772` = SUCCESS
**Manifest:** `codex/CHANGE_MANIFESTS/ACADEMIC-WEB-GRADE-WORKFLOW-G1-2026-10-04.md`
**Database write:** `NONE`
**Next atomic task:** `ACADEMIC-WEB-GRADE-WORKFLOW-G2-DRAFT-ENTRY-STATE-HARDENING`

G1 implemented the read-only grade surface and strict authorization boundary.
G2 must enforce DRAFT-only normal save; G5 must add atomic correction
approval/application. Do not write grade data. Public Academic AI remains OFF;
`IMP-S12-007` and `SOC-MD-06` remain unchanged.

## Current controlled PILOT migration E1 handoff — 2026-10-04

**Current task:** `SUPER-ADMIN-USER-ACCESS-CONTROLLED-PILOT-MIGRATION-E1`
**State:** `CONTROLLED_PILOT_MIGRATION_COMPLETED / POSTFLIGHT_PASS`
**Tested executable HEAD:** `a7fbcead149086e331cb5e7e22ed161a892f8a4b`
**Evidence:** `codex/CHANGE_MANIFESTS/SUPER-ADMIN-USER-ACCESS-PILOT-MIGRATION-E1-2026-10-04.md`
**Next atomic task:** `RETURN_TO_CHATGPT_FOR_USER_ACCESS_CONTROLLED_PILOT_MIGRATION_E1_AUDIT`

Exactly three User & Access migrations were applied to PILOT `imtaq` through
the target guard (`43→46`), followed only by `UserAccessFeatureSeeder`.
Independent read-only postflight verified account defaults, permission/grant
matrix, 13 feature rows, effective access, and Public Academic AI OFF.

Do not start another feature or database action. Academic UAT remains HOLD /
HUMAN_OBSERVATION_REQUIRED; SOC-MD-06 and IMP-S12-007 NOT_STARTED are preserved.

## Historical handoff — not current execution authority

**Current executable task:** `SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1R`
**Task state:** `COMPLETED / PASS / RUN_36976414076`
**Feature branch:** `feat/super-admin-user-access-preferences`
**Task context:** `codex/TASK_CONTEXTS/SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1R.md`
**Starting HEAD:** `79fb525c4369f7d31530a2e5f261499713e37ddd`
**Static checks:** `PASS`
**Focused/foundation tests:** `PASS / exact GitHub Actions run 36976414076`
**Database write:** `NONE`
**Next atomic task:** `RETURN_TO_CHATGPT_FOR_SUPER_ADMIN_USER_ACCESS_AUDIT`

The historical V1 closeout below is retained and is not superseded silently.

**Current task:** `SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1`
**Task state:** `IMPLEMENTED / PASS / RUN_36944117304`
**Feature branch:** `feat/super-admin-user-access-preferences`
**Task context:** `codex/TASK_CONTEXTS/SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1.md`
**Exact implementation HEAD:** `c9c7ee3e57d1f4481d71d97c87ebec5082453150`
**Exact implementation CI:** `36944491055` = SUCCESS
**Next atomic task:** `RETURN_TO_CHATGPT_FOR_SUPER_ADMIN_USER_ACCESS_AUDIT`

The historical Academic first-day UAT remains `HOLD / HUMAN_OBSERVATION_REQUIRED`;
this feature does not authorize pilot data access or attendance writes.

**Execution state:** `ACADEMIC_WALI_FIRST_DAY_CONTROLLED_UAT = HOLD / HUMAN_OBSERVATION_REQUIRED`
**Current canonical baseline:** `codex/GOVERNANCE/CANONICAL_PRE_SOC_IMPLEMENTATION_BASELINE_v1.0.md`
**Next task ID:** `SOC-MD-06`

**Current semantics review:** `codex/REVIEWS/ACADEMIC-WALI-PILOT-JOINT-SESSION-TEACHER-PARTICIPATION-SEMANTICS-RECONCILIATION-2026-10-01.md`
**Decision:** `JOINT_SESSION_TEACHER_PARTICIPATION_SEMANTICS_RATIFIED`

**K2B change manifest:** `codex/CHANGE_MANIFESTS/ACADEMIC-WALI-PILOT-PRIMARY-TEACHER-K2B-2026-10-01.md`
**K2B result:** `COMPLETED / PASS / 12_OF_12_EXPECTED_PRIMARY`
**K3B shared result:** `12_OF_12_FROM_SAME_12_PHYSICAL_ROWS`
**K3A change manifest:** `codex/CHANGE_MANIFESTS/ACADEMIC-WALI-PILOT-PRIMARY-TEACHER-K3A-2026-10-02.md`
**K3A result:** `COMPLETED / PASS / 14_OF_14_EXPECTED_PRIMARY`
**K3A re-verification review:** `codex/REVIEWS/ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K3A-2026-10-02.md`
**K3A re-verification decision:** `K3A_REMEDIATION_VERIFIED_PRIMARY_PROVISIONING_COMPLETE`
**Operational readiness reassessment:** `codex/REVIEWS/ACADEMIC-WALI-PILOT-OPERATIONAL-READINESS-REASSESSMENT-AFTER-PROVISIONING-2026-10-02.md`
**Operational readiness decision:** `READY_FOR_CONTROLLED_PILOT`
**First-day UAT candidate:** `IMTAQ-2026-1 · 2026-10-03 08:00 Asia/Jakarta · 20 required participants`
**First-day UAT gate:** `HOLD / HUMAN_OBSERVATION_REQUIRED`
**NEXT_ATOMIC_TASK:** `RETURN_TO_CHATGPT_FOR_CONTROLLED_WALI_UAT_AUTHORIZATION`

**Current review:** `codex/REVIEWS/ACADEMIC-WALI-PILOT-K3B-JOINT-SESSION-BASELINE-RECONCILIATION-2026-10-01.md`
**Decision:** `K3B_JOINT_SESSION_BASELINE_RATIFIED`
K3A provisioning and independent read-only re-verification are complete.

Academic Web product-completion percentage is outside this provisioning task; use its dedicated review/reconciliation rather than this operational gate.
Wali application workflow is evidenced; pilot provisioning remains the active operational gate.
**Pilot verification:** `codex/REVIEWS/ACADEMIC-WALI-PILOT-PROVISIONING-VERIFICATION-2026-09-30.md`
**Decision:** `PROVISIONING_GAP_FOUND`
**Change manifest:** `codex/CHANGE_MANIFESTS/ACADEMIC-WALI-PILOT-PRIMARY-TEACHER-K1-2026-09-30.md`

**Re-verification review:** `codex/REVIEWS/ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K1-2026-09-30.md`
**Decision:** `K1_REMEDIATION_VERIFIED_REMAINING_GAPS`

**K2A change manifest:** `codex/CHANGE_MANIFESTS/ACADEMIC-WALI-PILOT-PRIMARY-TEACHER-K2A-2026-09-30.md`
**K2A result:** `COMPLETED / PASS / 12_OF_12_EXPECTED_PRIMARY`
**K2A re-verification review:** `codex/REVIEWS/ACADEMIC-WALI-PILOT-PROVISIONING-REVERIFY-AFTER-K2A-2026-09-30.md`
**K2A re-verification decision:** `K2A_REMEDIATION_VERIFIED_REMAINING_GAPS`
**K2B task contract:** `codex/TASK_CONTEXTS/ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B.md`

**K3B joint-session baseline:** `codex/REVIEWS/ACADEMIC-WALI-PILOT-K3B-JOINT-SESSION-BASELINE-RECONCILIATION-2026-10-01.md`
**K3B baseline decision:** `K3B_JOINT_SESSION_BASELINE_RATIFIED` (anchor count 0; canonical joint count 12; exact K2B/K3B set equality; no mutation performed)

Joint participation contract: K2B and K3B share one teacher-participation row
per joint ClassSession. Current post-write coverage is `12/12` in both views
from the same 12 rows. No second K3B write.

## Historical K2B teacher-participation provisioning

**Historical task:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B`
**Selected class:** `IMTAQ-2026-2B`
**Type:** `CONTROLLED_PILOT_DATA_PROVISIONING`  
**Branch:** `chore/academic-wali-pilot-primary-k2a`  
**State-basis:** `38e14520c08daace2fc16b3608c73aa168fed471`
**Task contract:** `codex/TASK_CONTEXTS/ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-K2B.md`
**Final evidence CI:** `36829585309` = SUCCESS on `38e14520c08daace2fc16b3608c73aa168fed471`

Verified frozen-horizon baseline:
- 12 reportable sessions;
- 12/12 expected PRIMARY;
- 12/12 authoritative teaching assignments;
- 0 conflicts;
- 0 current lock blockers.

Authorized K2B write completed: 12 expected PRIMARY rows through
`TeacherParticipationRecorder::ensurePrimary()`; re-verification passed.

K1, K2A, K2B, and K3B remain historical/current baselines; K3A is now 14/14.

K2A re-verification completed with 12/12 expected PRIMARY and independent
read-only postflight PASS. K2B/K3B shared coverage is 12/12. K3A readiness
reconciliation found 14 standalone sessions with 14 missing PRIMARY rows;
do not provision K3A without separate authorization.

Production readiness is not assessed.
`IMP-S12-007` remains NOT_STARTED.
Canonical queue gate remains `SOC-MD-06`.


## Current AI track — AI Academic Assistant

**AI-A0:** `CLOSED / PASS`  
**AI MVP gate:** `READY_FOR_IMPLEMENTATION`  
**MVP role:** `WAKA_AKADEMIK_ONLY`  
**MVP domain:** `STUDENT_ATTENDANCE`  
**Typed tools:** `IMPLEMENTED / 5`  
**Application business writes:** `NONE`

The four AI-A0 governance artifacts, AI-A1 typed read-tool layer, AI-A2 server-side provider runtime, AI-A3 HTTP endpoint, AI-A4 Waka UI, and AI-A5 hardening are complete. The AI MVP pilot remains disabled.

**AI-A3:** `CLOSED / PASS`  
**AI HTTP endpoint:** `IMPLEMENTED / DISABLED BY FEATURE GATE`  
**AI HTTP authorization:** `IMPLEMENTED`  
**Pilot AI feature:** `OFF`  
**AI-A4:** `CLOSED / PASS`
**AI Waka chat UI:** `IMPLEMENTED / DISABLED BY FEATURE GATE`
**AI-A4 recovery:** `recovery/ai-a4/AI-A4_20260920-095425/`
**AI-A5:** `CLOSED / PASS`
**AI security/grounding hardening:** `IMPLEMENTED`
**AI live OpenAI smoke:** `NOT RUN — CONFIGURATION MISSING`
**AI-A5 recovery:** `recovery/ai-a5/AI-A5_20260920-110000/`
**AI-A5K:** `CLOSED / ACCEPTED / PILOT MIGRATION RATIFIED AFTER IR1`
**AI provider configuration source:** `DATABASE-MANAGED ACTIVE POINTER`
**AI-A5K recovery:** `recovery/ai-a5k/AI-A5K_20260920-120000/`
**AI-A5K-IR1:** `CLOSED / ACCEPTED`
**AI-A5K-IR1 incident:** `codex/INCIDENTS/AI-A5K-R1-TARGET-MISMATCH-2026-09-20.md`
**AI-A5K-IR1 recovery:** `recovery/ai-a5k-ir1/AI-A5K-IR1_20260920-213000/`
**AI migration target guard:** `IMPLEMENTED / migrate:guarded`
**AI-A5K-UI1:** `CLOSED / ACCEPTED`
**AI Provider navigation:** `SUPER_ADMIN_ONLY / PRESENT`
**AI-A5K-UI1 recovery:** `recovery/ai-a5k-ui1/AI-A5K-UI1_20260920-220000/`
**AI-A5K-UI1R:** `COMPLETED / READY_FOR_AUDIT`
**AI Provider Model ID:** `SERVER-SIDE DISCOVERY DROPDOWN / PRESENT`
**AI-A5K-UI1R recovery:** `recovery/ai-a5k-ui1r/AI-A5K-UI1R_20260924-133500/`
**AI-PROVIDER-R1-C:** `COMPLETED / PASS`
**AI-PROVIDER-R1-C grounding:** `STRUCTURAL / SERVER-OWNED TERMINAL STATES`
**AI-PROVIDER-R1-C attendance free text:** `FREE_TEXT_EXCLUDED`
**AI-PROVIDER-R1-C recovery:** `recovery/ai-provider-r1c/AI-PROVIDER-R1C_20260924-210000/`
**AI-PROVIDER-R1-D:** `COMPLETED / PASS`
**AI-PROVIDER-R1-D public gate display:** `REAL AUTHORITY / READ-ONLY`
**AI-PROVIDER-R1-D secret safety:** `VALIDATION + EXCEPTION + AUDIT SAFE`
**AI-PROVIDER-R1-D admin throttle:** `12/MINUTE PER USER/IP`
**AI-PROVIDER-R1-D recovery:** `recovery/ai-provider-r1d/AI-PROVIDER-R1D_20260924-220000/`
**AI-PROVIDER-READINESS-E1:** `CLOSED / PASS / TRACEABILITY COMPLETE`
**AI-PROVIDER-READINESS-E1 recovery:** `recovery/ai-provider-readiness-e1/AI-PROVIDER-READINESS-E1_20260925-060000/`
**Gate B readiness:** `READY_FOR_CONTROLLED_REAL_PROVIDER_VERIFICATION`
**Provider verification:** `NOT RUN / NOT AUTHORIZED BY E1`
**AI-PROVIDER-UX-S1:** `COMPLETED / PASS`
**AI Provider settings UI:** `SIMPLIFIED PRIMARY WORKFLOW + COLLAPSED ADVANCED SETTINGS`
**AI-PROVIDER-UX-S1 recovery:** `recovery/ai-provider-ux-s1/AI-PROVIDER-UX-S1_20260925-070000/`
**Gate B after UXS1:** `READY_FOR_CONTROLLED_REAL_PROVIDER_VERIFICATION`
**AI-PROVIDER-UX-S1.1:** `COMPLETED / PASS`
**AI Provider settings refinement:** `READ-MODE PRIMARY CARD + ON-DEMAND EDIT + COLLAPSED ADVANCED`
**AI-PROVIDER-UX-S1.1 recovery:** `recovery/ai-provider-ux-s1/AI-PROVIDER-UX-S1.1_20260925-080000/`
**Gate B after UXS1.1:** `READY_FOR_CONTROLLED_REAL_PROVIDER_VERIFICATION`

**AI-A2:** `CLOSED / PASS`  
**AI provider runtime:** `IMPLEMENTED / NOT HTTP EXPOSED`  
**AI tool orchestrator:** `IMPLEMENTED`  
**OpenAI Responses provider:** `IMPLEMENTED`  
**AI HTTP endpoint at A2 close:** `NOT_STARTED`  
**AI Waka chat UI at A2 close:** `NOT_STARTED`  
**AI MVP pilot:** `NOT_STARTED`  
**AI-A3 readiness at A2 close:** `READY_FOR_IMPLEMENTATION`

## Track A — Session Occurrence governance and implementation

**Post-migration baseline:** `ESTABLISHED`  
**Database migrations:** `42/42 APPLIED`  
**Session Occurrence implementation:** `CANONICAL_OCCURRENCE_PERSISTENCE IMPLEMENTED`  
**Canonical occurrence write service:** `IMPLEMENTED`  
**Canonical occurrence workflow integration:** `IMPLEMENTED_AND_ACTIVATED`  
**Canonical occurrence UI:** `IMPLEMENTED_AND_ACTIVATED`  
**Canonical denominator:** `IMPLEMENTED_AND_ACTIVATED`  
**Cutover authority:** `IMPLEMENTED_AND_ACTIVATED`  
**Pilot occurrence cutover:** `2026-09-20T00:00:00+07:00`  
**Denominator cutover:** `IMPLEMENTED_AND_ACTIVATED`

The canonical persistence, workflow, and denominator layers are activated for the controlled pilot from `2026-09-20T00:00:00+07:00`. MD-02 non-eligible authority remains OPEN, source authority precedence is NOT_ACTIVATED, class lineage population is NOT_ACTIVATED, and historical occurrence backfill is not authorized.

Do not activate NON_ELIGIBLE, source-authority precedence, full attendance canonicalization, certification/lineage population, historical backfill, or historical rewrite without separate governance authorization.

## Track B — Deployment / cutover track

Production cutover is not complete. The deployment track remains blocked/pending until canonical Git/release authority, staging, backup/restore verification, secrets handling, monitoring, rollback, and production migration review are evidenced.

## Current boundaries

Preserve the existing Academic implementation and transitional COMPLETED semantics. AI/OpenAI, Communication/WhatsApp, Parent Portal, biometric, and unrelated future-domain implementation remain deferred.

## Safe checkpoint state

SOC-I1E recovery artifacts are under `recovery/soc-i1e/SOC-I1E_20260919-160000/`. Session occurrence canonicalization is activated for the controlled pilot; first real post-cutover occurrence remains pending operational use. AI-A1 through AI-A5 are complete; AI-A3/A4 remain disabled by the feature gate and no AI pilot activation occurred.

**AI_PROVIDER_RUNTIME:** `IMPLEMENTED`  
**AI_CHAT_ENDPOINT:** `IMPLEMENTED_DISABLED_BY_FEATURE_GATE`  
**AI_WAKA_CHAT_UI:** `IMPLEMENTED_DISABLED_BY_FEATURE_GATE`  
**AI_AUDIT_HARDENING:** `IMPLEMENTED / SECURITY + GROUNDING + COST + EVIDENCE`
**LIVE_OPENAI_SMOKE:** `NOT_RUN_CONFIGURATION_MISSING`

**MANDATORY_SECURITY_REMEDIATION:** `COMPLETE`
**CONTROLLED_DIAGNOSTIC_RETRY_READINESS:** `READY`
**KNOWN_CONTINUATION_DEFECT:** `FIXED`
**INVALID_INPUT_ERROR_TAXONOMY:** `FIXED`
**INITIAL_LIVE_INPUT_REJECTION_ROOT_CAUSE:** `UNRESOLVED`
**VERIFICATION_INPUT_COMPATIBILITY:** `ONE-ITEM-MESSAGE-LIST + FORCED_RESOLVE_STUDENT`
**VERIFICATION_CONTINUATION:** `STATELESS_REPLAY + NO_PREVIOUS_RESPONSE_ID`
**CONTROLLED_FULL_VERIFICATION_RETRY_READINESS:** `READY`
**Readiness review:** `codex/REVIEWS/ACADEMIC-WALI-DAILY-WORKFLOW-READINESS-2026-09-29.md`
**Decision:** `APPLICATION_READY_PROVISIONING_NOT_VERIFIED` (historical; superseded by current read-only reassessment)
