# CURRENT TASK CONTEXT

**Task:** `AI-PROVIDER-UX-S1.1` Modern Minimal AI Provider Settings Refinement  
**State:** `COMPLETED / PASS / READY_FOR_CONTROLLED_REAL_PROVIDER_VERIFICATION`  
**Current phase:** `AI PROVIDER UX`

## Current baseline

Academic implementation is existing and substantially complete through controlled pilot/UAT evidence. Preserve it; do not restart Sprint 0 or rebuild attendance, teacher attendance, substitution, cancellation, rescheduling, joint sessions, correction, dashboard metrics, or dashboard export.

## Current management gate

Session-occurrence mutation remains outside this task. AI-A0 does not modify or reopen the accepted SOC-I1E implementation. `MD_02_NON_ELIGIBLE_AUTHORITY`, legacy import provenance, source-authority precedence, and full attendance canonicalization remain open as documented; the read-only AI MVP must not infer or resolve them.

## REQUIRED NOW

1. `codex/CHANGE_MANIFESTS/AI-PROVIDER-UX-S1.1-2026-09-25.md`
2. `recovery/ai-provider-ux-s1/AI-PROVIDER-UX-S1.1_20260925-080000/README_RECOVERY.md`
3. `NEXT_ACTION.md`

Do not require old migration/admin task files by default. Expand context only when the selected gate requires it.

## Boundaries

AI-A5K-IR1 ratified the already-present pilot migration after independent source, backup, schema, disposable PostgreSQL, and regression verification. No credential/configuration was seeded, no live OpenAI request was made, public AI remains OFF, and no Academic business data, attendance semantics, RBAC, or historical data changed. Future migration writes require the `migrate:guarded` target guard.
