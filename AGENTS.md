# AGENTS.md — SISTEM IMTAQ

Applies to the whole repository unless a deeper `AGENTS.md` is more specific.

## Mission
Build one modular Laravel/PostgreSQL SISTEM IMTAQ with canonical Shared Core identities.
North Star: `ONE STUDENT → ONE ID → ONE HISTORY → MANY ACTIVITIES → ONE SOURCE OF TRUTH → MANY REPORTS.`

## Quota-efficient task start — REQUIRED
Do **not** bulk-read the repository.
1. Read `codex/CURRENT_TASK_CONTEXT.md`.
2. Read `NEXT_ACTION.md`.
3. Read only the files marked **REQUIRED NOW** by the current task context.
4. Use `codex/CONTEXT_ROUTER.md` only if additional context is genuinely needed.
5. Do not read `PROJECT_STATUS.md`, `PROJECT_PROGRESS.md`, full task queues, full governance, future AI/Communication/Portal docs, or unrelated modules merely to start a task.
6. Do not re-read an unchanged file in the same thread unless necessary.
7. Search/find the relevant section before opening a large document in full.

If a task context conflicts with an authoritative contract, the authoritative contract wins. Task contexts are routing summaries, not new business authority.

## Core architecture invariants
- Modular monolith first: one Laravel application, one PostgreSQL database, one auth/RBAC foundation.
- Shared Core owns canonical Student/Staff/Guardian/Organization identity; domains own their transactions.
- Never create duplicate module-specific Student masters or use names as primary identity.
- Cross-module writes require explicit contracts/services; no module writes another module's owned transaction tables directly.
- `POLICY_PENDING` is never guessed. `SUPERSEDED` is never implemented.
- No destructive deletion of historical business facts.
- Backend RBAC + data scope + resource state are authoritative; UI hiding is not authorization.
- Transactional state changes use explicit services/commands, validation, audit/versioning, and database constraints where applicable.
- Reports/KPI consume canonical facts; AI is never Source of Truth.

## Safe change rules
- Git/release history is source-version authority.
- Minimum Necessary Change. No unrelated refactor/framework upgrade.
- Declare expected write scope before non-trivial edits.
- Protected/shared/security/global areas may not be silently touched; escalate impact first.
- Applied staging/production migrations are immutable; create a new migration.
- Persistent business data, uploads and secrets stay outside replaceable code releases.
- Non-trivial completed code work requires tests and a Change Manifest.
- Do not ask the business owner which individual files to edit/upload.

Authority when triggered: `docs/07_implementation/CODEX_CHANGE_MANAGEMENT_AND_SAFE_MAINTENANCE.md`.

## Quota-efficiency rules
Follow `docs/07_implementation/CODEX_QUOTA_EFFICIENCY_PROTOCOL.md`.
- Keep active context to the smallest sufficient set.
- Prefer targeted file/section reads over repository-wide reading.
- Run targeted tests during an atomic step; run broader required regression at the appropriate gate/closeout.
- Do not spend turns producing repeated architecture summaries unless requested.
- Do not sacrifice integrity/security tests to save quota.
- If the client permits model choice, follow `codex/MODEL_AND_REASONING_POLICY.md`.

## Checkpoint rules
Follow `docs/07_implementation/CODEX_CHECKPOINT_TIMEBOX_PROTOCOL.md`.
- Complete one coherent atomic engineering result, then stop safely.
- Report `SAFE_TO_CLOSE = YES/NO`, exact resume point, verified progress, and 2–4 next checkpoint choices.
- Option 1 is the primary recommendation.
- Give time ranges, not guarantees. Offer a quick option only when it reaches a real safe checkpoint.
- Execute only the checkpoint horizon selected by the owner.
- `SAFE CHECKPOINT NOW` means: start nothing new, persist state, stop when safe.

## Current implementation guardrails
- Application root: `application/web/`.
- Current executable task is resolved by `codex/CURRENT_TASK_CONTEXT.md`; do not jump sprints.
- Google/Gmail SSO is deferred.
- AI/OpenAI implementation is future and not a current foundation dependency; OpenAI API is the selected future provider.
- WhatsApp/Communication, Parent Portal and biometric/fingerprint implementation are future/deferred unless explicitly activated.
- Domains marked `NOT_PLANNED` are not implemented.

## Domain-triggered authority
Only load these when the active work actually touches them:
- Shared Core/identity/RBAC/audit → `docs/10_shared_core/README.md`
- Academic class/enrollment → `docs/02_architecture/CLASS_MASTER_AND_ENROLLMENT.md`
- Academic scheduling/conflicts → `docs/02_architecture/SCHEDULE_CONFLICT_AND_CONSTRAINT_ENGINE.md`
- AI → `docs/08_ai/`
- Communication/WhatsApp → `docs/09_communication/`
- Parent Portal → `docs/11_parent_portal/`
- Migration → `docs/05_migration/`
- UAT/test strategy → `docs/06_testing/`

## Work closeout
When the atomic step/task materially changes repository state:
1. run required targeted checks/tests;
2. update `codex/WORK_LOG.md` and task status only when appropriate;
3. update `NEXT_ACTION.md` only if the executable next action changes;
4. run `python scripts/update_project_progress.py` when progress evidence changed;
5. at task completion, run required broader validation and produce the Change Manifest.

Do not update several status documents on every tiny sub-step if no status/progress fact changed; record only what is required by the checkpoint protocol.
