# NEXT ACTION

**Execution state:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-FOR-ONE-CLASS = READY_FOR_EXECUTION`
**Current canonical baseline:** `codex/GOVERNANCE/CANONICAL_PRE_SOC_IMPLEMENTATION_BASELINE_v1.0.md`
**Next task ID:** `SOC-MD-06`

Academic Web completion baseline: `4 / 10 = 40% COMPLETE_EVIDENCED`.
Wali application workflow: `APPLICATION_READY_PROVISIONING_NOT_VERIFIED`.
**Pilot verification:** `codex/REVIEWS/ACADEMIC-WALI-PILOT-PROVISIONING-VERIFICATION-2026-09-30.md`
**Decision:** `PROVISIONING_GAP_FOUND`
**NEXT_ATOMIC_TASK:** `EXECUTE_ACADEMIC_WALI_PILOT_PRIMARY_TEACHER_K1_FROM_REPOSITORY`

## Current controlled Kelas 1 teacher-participation provisioning

**Task:** `ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-FOR-ONE-CLASS`  
**Selected class:** `IMTAQ-2026-1`  
**Type:** `CONTROLLED_PILOT_DATA_PROVISIONING`  
**Branch:** `fix/academic-wali-pilot-primary-teacher-k1`  
**State-basis:** `2c2dccfb2961fd211b6c496514929919a3e9219a`  
**Task contract:** `codex/TASK_CONTEXTS/ACADEMIC-WALI-PILOT-PROVISION-PRIMARY-TEACHER-PARTICIPATION-FOR-ONE-CLASS.md`

The read-only provisioning verification found:
- Kelas 1: 11 sessions / 10 reportable;
- Kelas 1 expected PRIMARY teacher participation: 0/10;
- canonical teacher source: session teaching assignment;
- canonical write service: `TeacherParticipationRecorder::ensurePrimary()`.

Authorized pilot write budget: at most 10 new PRIMARY EXPECTED participation
rows for the exact Kelas 1 reportable target set only.

No source/schema/schedule/session/roster/attendance-status or other-class
mutation is authorized.

On success, route a read-only provisioning re-verification.
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
**Decision:** `APPLICATION_READY_PROVISIONING_NOT_VERIFIED`
**NEXT_ATOMIC_TASK:** `EXECUTE_ACADEMIC_WALI_PILOT_PRIMARY_TEACHER_K1_FROM_REPOSITORY`
