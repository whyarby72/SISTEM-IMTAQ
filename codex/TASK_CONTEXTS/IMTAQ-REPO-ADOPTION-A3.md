# IMTAQ-REPO-ADOPTION-A3 — Repository State Reconciliation

**Mode:** `EXISTING_REPO_ADOPTION`  
**Product form candidate:** `WEB_FULLSTACK_SERVICE_API`  
**Task owner:** `CODEX`  
**Task state:** `READY_FOR_EXECUTION`  
**Baseline main commit:** `957f062815147a0cc2ea2200fd82692c3447f3e1`  
**Task branch:** `chore/IMTAQ-REPO-ADOPTION-A3-state-reconciliation`

## Goal

Make the existing repository itself carry the minimum durable continuation state required for the repository-centered workflow:

`PRODUCT_SPEC/equivalent → ACCEPTANCE_CRITERIA → CURRENT_TASK → PROJECT_STATE → TEST_MATRIX → EVIDENCE_INDEX → Codex handoff`

Do this without rebuilding, relocating, modernizing, or broadly refactoring the application.

## Existing equivalents — reuse, do not duplicate

- Product scope/spec authority: `docs/01_scope/PRODUCT_SCOPE.md` + existing architecture/governance docs.
- Current-task routing: `codex/CURRENT_TASK_CONTEXT.md` + `NEXT_ACTION.md`.
- Decisions: `project_management/DECISION_LOG.md` + `project_management/TECHNICAL_DECISIONS.md`.
- Test policy: `docs/06_testing/TEST_STRATEGY.md` + `docs/06_testing/UAT_MATRIX.md`.
- Evidence history: `codex/CHANGE_MANIFESTS/` + `recovery/`.
- Deployment authority: `docs/07_implementation/DEPLOYMENT_STAGING_AND_ROLLBACK.md`.
- Existing machine-readable project metadata: `PROJECT_MANIFEST.json`.

Do **not** create duplicate `PRODUCT_SPEC.md`, `ACCEPTANCE_CRITERIA.md`, `CURRENT_TASK.md`, or `DECISIONS.md` unless a concrete unmet requirement cannot be satisfied by the mapped existing artifacts.

## Required writes

Create only these minimum new repository-state artifacts if still absent:

1. `PROJECT_STATE.json`
2. `TEST_MATRIX.csv`
3. `EVIDENCE_INDEX.json`

Update existing routing/state artifacts only where needed to remove contradictions:

4. `codex/CURRENT_TASK_CONTEXT.md`
5. `NEXT_ACTION.md`
6. existing project metadata/status files only if their factual state is materially stale and the update is necessary for a coherent repository state.

No application source changes are in scope.

## Canonical facts to reconcile

- Repository: `whyarby72/SISTEM-IMTAQ`
- Visibility: private.
- Default branch: `main`.
- Baseline main commit: `957f062815147a0cc2ea2200fd82692c3447f3e1`.
- Existing application root: `application/web/`.
- Academic implementation: existing/pilot/hardening; do not rebuild.
- `AI-PROVIDER-CONTEXT-P3`: completed/pass in repository evidence.
- Controlled full provider verification retry readiness after P3: `READY`, but no new live OpenAI verification is authorized by A3.
- Public Academic AI remains OFF.
- Universal Repo Factory remains `CANDIDATE / REFERENCE ONLY`; no Factory or domain-adapter activation is authorized.

## Current CI evidence

Exact baseline commit GitHub Actions run:

- workflow: `Application foundation`
- run id: `36301085559`
- commit: `957f062815147a0cc2ea2200fd82692c3447f3e1`
- result: `FAIL`
- failing step: `composer install --no-interaction --prefer-dist --no-progress`
- runner PHP: `8.3.35`
- `composer.json`: PHP `^8.3`
- lockfile contains Symfony 8.1 packages requiring PHP `>=8.4.1`

A3 must record this as an unresolved blocker/evidence debt. A3 must **not** choose or implement the PHP/dependency fix.

Recommended blocker id:

`CI_PHP_LOCKFILE_COMPATIBILITY`

Recommended next diagnostic task after A3:

`CI-PHP-CONTRACT-D1`

## PROJECT_STATE.json minimum contract

At minimum contain or reference:

- `repo_commit`
- `active_branch`
- `product_stage`
- `current_task_id`
- `adapter_id`
- `acceptance_ids`
- `latest_evidence_refs`
- `latest_test_state`
- `blockers`
- `evidence_debt`
- `next_owner`
- `updated_at`

Use factual values only. Do not claim Universal Factory or adapter activation.

For the task branch, bind state to the actual current branch/commit at closeout, not to a guessed future SHA.

## TEST_MATRIX.csv minimum purpose

Create a compact machine-readable ledger of current proving checks/evidence. At minimum distinguish:

- exact-commit GitHub Actions evidence;
- historical local regression evidence from change manifests/recovery;
- static/source inspection evidence;
- not-yet-replayed evidence.

Do not promote historical local PASS to exact-current-CI PASS.

At minimum include the baseline CI failure above.

## EVIDENCE_INDEX.json minimum purpose

Index durable evidence already present in the repository. Prefer references to existing:

- GitHub Actions run/commit identity;
- `codex/CHANGE_MANIFESTS/AI-PROVIDER-CONTEXT-P3-2026-09-26.md`;
- `recovery/ai-provider-context-p3/AI-PROVIDER-CONTEXT-P3_20260926-150000/README_RECOVERY.md`;
- relevant test/evidence docs.

Do not copy raw logs, secrets, provider bodies, or credentials into the index.

## Acceptance criteria

- `A3-AC-01` Existing repo remains authoritative; no new repo/app/source relocation.
- `A3-AC-02` No files under `application/web/app/`, migrations, routes, provider implementation, or business logic are changed.
- `A3-AC-03` Existing equivalent contracts are mapped rather than duplicated.
- `A3-AC-04` `PROJECT_STATE.json` exists, parses, and reflects exact branch/commit at closeout.
- `A3-AC-05` `TEST_MATRIX.csv` exists, is parseable, and separates exact-current evidence from historical evidence.
- `A3-AC-06` `EVIDENCE_INDEX.json` exists, parses, and references durable repo evidence without secrets.
- `A3-AC-07` Current-task routing no longer contradicts the completed P3 state.
- `A3-AC-08` CI failure `CI_PHP_LOCKFILE_COMPATIBILITY` is explicitly recorded as unresolved.
- `A3-AC-09` Universal Factory remains reference-only and `WEB_FULLSTACK_SERVICE_API` remains candidate only.
- `A3-AC-10` No database write, migration, provider state mutation, live OpenAI request, feature activation, or deployment occurs.
- `A3-AC-11` Repository validation for new JSON/CSV/state references passes.
- `A3-AC-12` Branch is committed/pushed with evidence and a clear next owner/task.

## Expected change class

`MODULE_INTERNAL / REPOSITORY_GOVERNANCE`

No protected application zone should be touched. If application/security/database source becomes necessary, stop and escalate.

## Validation

At minimum:

- parse both new JSON files;
- parse/validate CSV shape;
- verify all referenced repository paths exist where applicable;
- verify `git diff -- application/web/app application/web/database application/web/routes` is empty relative to baseline;
- verify no secrets are introduced;
- verify branch worktree clean after commit;
- record exact branch/head SHA;
- do not rerun live provider verification;
- do not repair the CI PHP mismatch in A3.

## Required closeout state

Update repo-owned state/evidence so ChatGPT can reconcile directly from GitHub. Human must not be required to copy source, diffs, compiler output, or routine closeout data into chat.

Set next task to `CI-PHP-CONTRACT-D1` only after A3 acceptance criteria pass.

## Stop conditions

Stop and report `HOLD` if:

- exact repo/branch/commit cannot be resolved;
- task requires application source mutation;
- existing state authority cannot be reconciled without destructive deletion;
- secret-bearing material would enter committed artifacts;
- a decision between PHP runtime upgrade vs dependency-lock change is required.

Do not resolve the PHP/dependency decision inside A3.
