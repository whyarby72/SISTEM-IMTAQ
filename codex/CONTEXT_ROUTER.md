# CODEX CONTEXT ROUTER — MINIMUM ACTIVE CONTEXT

Purpose: preserve the complete project knowledge base while preventing Codex from loading unrelated material and exhausting agentic quota.

## Always start with only
1. `AGENTS.md` (normally auto-loaded by Codex)
2. `codex/CURRENT_TASK_CONTEXT.md`
3. `NEXT_ACTION.md`

Then read only **REQUIRED NOW** files named by the task context.

## Context tiers
### Tier 0 — always-active
Current task, immediate constraints, exact write scope, checkpoint contract.

### Tier 1 — task authority
Only the 1–4 authoritative docs directly needed to implement the current atomic step.

### Tier 2 — conditional dependency
Load only when a concrete trigger appears: Shared Core change, RBAC/security impact, DB schema change, cross-module contract, policy-pending, deployment, etc.

### Tier 3 — reference/archive/future
Do not load during normal implementation unless explicitly required: future modules, AI, Communication, Parent Portal, historical snapshots, full Core Engine baseline, unrelated roadmaps.

## Retrieval behavior
- Use `rg`, `grep`, file search, headings, or targeted ranges before opening long docs.
- Do not recursively summarize `docs/`, `modules/`, or `shared/`.
- Do not read the same unchanged reference repeatedly in one thread.
- Prefer source code + tests for an already-implemented behavior; use architecture docs for governing rules and ambiguity.
- When a new task becomes active, update `codex/CURRENT_TASK_CONTEXT.md` rather than making the next thread re-read the whole workspace.

## Trigger map
| Trigger | Load |
|---|---|
| Shared identity/Guardian/Staff/Org | `docs/10_shared_core/README.md` then its specific linked contract |
| RBAC/security | `docs/02_architecture/RBAC_MATRIX.md` and/or `docs/10_shared_core/AUTH_RBAC_AUDIT.md` |
| Academic class/enrollment | `docs/02_architecture/CLASS_MASTER_AND_ENROLLMENT.md` |
| Academic scheduling | `docs/02_architecture/SCHEDULE_CONFLICT_AND_CONSTRAINT_ENGINE.md` |
| Attendance | relevant sections of `BUSINESS_RULES.md`, `WORKFLOW_STATE_MACHINES.md`, schema + tests |
| DB change after baseline | safe-maintenance + specific schema contract; applied migration immutable |
| Cross-module change | `modules/CHANGE_IMPACT_RULES.md`, dependency map, only touched contracts |
| Policy-dependent path | locate exact item in `POLICY_PENDING_REGISTER.md`; do not read entire governance tree |
| Staging/production | `DEPLOYMENT_STAGING_AND_ROLLBACK.md` + release checklist |
| AI | `docs/08_ai/` specific contract only |
| Communication | `docs/09_communication/` specific contract only |
| Parent Portal | `docs/11_parent_portal/` specific contract only |

## Anti-pattern
`Read everything so you understand the project` is prohibited as a routine start instruction. The complete bundle is the knowledge base; the current task context is the working set.
