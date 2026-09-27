# CODEX HANDOFF — START HERE

**Bundle:** SISTEM IMTAQ Codex Project v1.16 — Quota-Efficient Edition  
**State:** PILOT IMPLEMENTED / HARDENING  
**Current canonical gate:** `SOC-MD-06` (management decision; source mutation not authorized)  
**Application root:** `application/web/`

## Business owner
You do not need to know source-file locations. Start Codex with `CODEX_FIRST_PROMPT.txt` or `handoff/01_PROMPTS/21_QUOTA_EFFICIENT_START.txt`.

## Codex
The full bundle is the project knowledge base, **not** the default active context.
Start with:
`AGENTS.md → codex/CURRENT_TASK_CONTEXT.md → NEXT_ACTION.md → REQUIRED NOW`.

Canonical implementation baseline:
`codex/GOVERNANCE/CANONICAL_IMPLEMENTATION_BASELINE_v1.0.md`.

Do not bulk-read the repository. Expand context only through `codex/CONTEXT_ROUTER.md` when a concrete trigger requires it.

## Quota strategy
- Normal coding: prefer GPT-5.6 Terra when selectable.
- Routine/light work: Luna when sufficient.
- Hard escalation only: Sol.
- Keep reasoning low/medium unless higher effort demonstrates value.
- Use meaningful atomic checkpoints rather than many micro-turns.

See `codex/MODEL_AND_REASONING_POLICY.md` and `docs/07_implementation/CODEX_QUOTA_EFFICIENCY_PROTOCOL.md`.

## Current non-goals
Do not rebuild completed Academic implementation. Do not implement session occurrence canonicalization, SOC-MD-06, AI/OpenAI, Google/Gmail SSO, WhatsApp provider, Parent Portal, biometric/fingerprint, or future-domain work without an explicit gate.

## MacBook checkpoint
After each atomic result Codex stops, reports `SAFE_TO_CLOSE`, and offers timed checkpoint choices. Use `SAFE CHECKPOINT NOW` when you need to stop urgently.
