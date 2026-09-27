# CODEX MODEL & REASONING POLICY — QUOTA EFFICIENCY

**Status:** operational guidance; model availability depends on the current Codex client/version and account.

As of 2026-09-02, OpenAI's current GPT-5.6 guidance positions:
- **GPT-5.6 Terra**: balance of intelligence and cost — preferred default for normal SISTEM IMTAQ coding when selectable.
- **GPT-5.6 Luna**: cost-sensitive/high-volume — preferred for routine status/docs, simple searches, mechanical edits, and low-risk housekeeping when selectable.
- **GPT-5.6 Sol**: flagship capability — reserve for genuinely hard architecture/debugging/security/cross-module problems when Terra is insufficient.

## Default
For normal implementation: **Terra + medium reasoning** when the Codex UI/config permits selection.

## Use Luna when
- status-only/repository navigation;
- work-log/document housekeeping;
- simple deterministic edits;
- straightforward test additions/triage where quality is demonstrably sufficient.

## Escalate to Sol when
- difficult multi-layer regression cannot be resolved reliably with Terra;
- Shared Core/security/cross-module architecture change is genuinely complex;
- hard concurrency/data-integrity debugging requires deeper reasoning.

Do not use Sol merely because a task is important. Importance is handled by tests/review; Sol is for complexity.

## Reasoning
- start `low` or `medium` for routine tasks;
- use `high` only when there is a measured need;
- avoid `max` as a default;
- if a lower reasoning setting preserves quality on representative tests, prefer it.

## Client reality
Codex may not let an agent switch its own model mid-turn. Treat this file primarily as an **owner/session selection policy**. If the requested model is unavailable, continue with the closest available cost-efficient coding model; do not block the project or invent model capabilities.

## Non-negotiable
Never reduce integrity/security/business-rule validation merely to reduce usage. Save quota by minimizing context and unnecessary turns, not by skipping required correctness checks.
