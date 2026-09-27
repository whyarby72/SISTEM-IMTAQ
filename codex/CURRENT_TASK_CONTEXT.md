# CURRENT TASK CONTEXT

**Task:** `IMTAQ-REPO-ADOPTION-A3` Repository State Reconciliation  
**State:** `COMPLETED / PASS`
**Current phase:** `EXISTING_REPO_ADOPTION / REPOSITORY STATE`  
**Task branch:** `chore/IMTAQ-REPO-ADOPTION-A3-state-reconciliation`
**Baseline main:** `957f062815147a0cc2ea2200fd82692c3447f3e1`
**A3 closeout commit:** `0a7b3cceaae4c25181e874e548add308d1715c82`

## Current baseline

SISTEM IMTAQ is an existing private GitHub repository at `whyarby72/SISTEM-IMTAQ`. The existing Laravel/PostgreSQL application under `application/web/` is authoritative and must be preserved.

`AI-PROVIDER-CONTEXT-P3` is already present in repository evidence as `COMPLETED / PASS`. Do not rerun or rebuild P3 in A3.

The exact baseline GitHub Actions run for the baseline commit currently fails during Composer dependency installation because the workflow uses PHP 8.3 while the lockfile includes packages requiring PHP >=8.4.1. A3 records this blocker only; it does not decide or implement the fix.

## REQUIRED NOW

1. `codex/TASK_CONTEXTS/IMTAQ-REPO-ADOPTION-A3.md`
2. `NEXT_ACTION.md`
3. `AGENTS.md`
4. Existing equivalents referenced by the A3 task only as needed.

A3 state artifacts are complete: `PROJECT_STATE.json`, `TEST_MATRIX.csv`, and
`EVIDENCE_INDEX.json`. The next diagnostic task is `CI-PHP-CONTRACT-D1`.

Do not bulk-read the repository. Expand context only when a concrete A3 acceptance criterion requires it.

## Expected writes

A3 may create only the minimum machine-readable state layer:

- `PROJECT_STATE.json`
- `TEST_MATRIX.csv`
- `EVIDENCE_INDEX.json`

A3 may update existing routing/state documents only where necessary to remove factual contradictions.

## Boundaries

- No application/business source mutation.
- No framework/dependency change.
- No PHP/Composer compatibility fix in A3.
- No migration or database write.
- No live OpenAI request.
- No provider/DRAFT/ACTIVE/active-pointer mutation.
- No Public Academic AI activation.
- No deployment.
- Universal Repo Factory remains `CANDIDATE / REFERENCE ONLY`.
- `WEB_FULLSTACK_SERVICE_API` remains a candidate adapter, not activated.

## Exit

When all A3 acceptance criteria pass, commit/push the repository-state changes and set the next diagnostic task to `CI-PHP-CONTRACT-D1`.

ChatGPT will reconcile the resulting commit/evidence directly from GitHub; routine file/log copy-paste by the owner is not required.
