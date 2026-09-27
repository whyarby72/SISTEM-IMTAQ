# IMTAQ-REPO-ADOPTION-A3R — State Commit Semantics Correction

**Mode:** `EXISTING_REPO_ADOPTION`  
**Task type:** narrow repository-state remediation  
**Task owner:** `CODEX`  
**Task state:** `READY_FOR_EXECUTION`  
**Branch:** `chore/IMTAQ-REPO-ADOPTION-A3-state-reconciliation`  
**A3 result commit / state-basis commit:** `da5b15dfee1c257be3d262270dc2ba27001977bf`

## Problem

A3 is substantively complete, but its state artifacts contain ambiguous or incorrect commit semantics:

- `PROJECT_STATE.json.repo_commit` still points to preparation commit `0a7b3cce...`;
- `codex/CURRENT_TASK_CONTEXT.md` labels `0a7b3cce...` as the A3 closeout commit;
- `TEST_MATRIX.csv` uses `0a7b3cce...` for current-branch/state evidence.

Actual A3 result commit is:

`da5b15dfee1c257be3d262270dc2ba27001977bf`

A repository state file cannot safely try to contain the SHA of the commit that contains itself, because every edit would create a new commit SHA. Therefore the contract must distinguish **state-basis commit** from **exact current branch HEAD**.

## Canonical semantics

Use:

- `repo_commit` = the exact commit whose repository state this packet describes;
- `repo_commit_semantics` = `STATE_BASIS_COMMIT`;
- exact current HEAD = resolve from Git branch ref at task start/audit time;
- do not chase the remediation commit SHA back into the same state file;
- do not label a prior commit as “current HEAD” or “closeout commit” after a newer commit exists.

For A3R, the canonical state-basis commit is:

`da5b15dfee1c257be3d262270dc2ba27001977bf`

## Allowed writes

Only if needed:

- `PROJECT_STATE.json`
- `TEST_MATRIX.csv`
- `codex/CURRENT_TASK_CONTEXT.md`
- `NEXT_ACTION.md`
- `EVIDENCE_INDEX.json` only if a reference or wording is factually wrong because of the same commit-semantics issue.

No application/business source changes.

## Required corrections

### PROJECT_STATE.json

Set:

- `repo_commit` = `da5b15dfee1c257be3d262270dc2ba27001977bf`
- add `repo_commit_semantics` = `STATE_BASIS_COMMIT`
- add a clear field such as `head_resolution` = `RESOLVE_FROM_GIT_BRANCH_REF`

Do not add a self-referential “current HEAD SHA” field that would become stale immediately after commit.

Preserve:

- branch identity;
- product stage;
- candidate adapter/factory status;
- blockers;
- evidence debt;
- Public Academic AI OFF;
- next task `CI-PHP-CONTRACT-D1`.

### codex/CURRENT_TASK_CONTEXT.md

Replace any wording that calls `0a7b3cce...` the A3 closeout commit.

Use a factual label such as:

- `A3 state-basis commit: da5b15dfee1c257be3d262270dc2ba27001977bf`

Set A3R as current task while remediation is executing.

At closeout, state:

- `IMTAQ-REPO-ADOPTION-A3R = COMPLETED / PASS`
- next task = `CI-PHP-CONTRACT-D1`

### TEST_MATRIX.csv

For rows describing repository-state evidence that currently reference `0a7b3cce...`, update the commit/reference so they no longer claim that preparation commit is the A3 current-state basis.

Use `da5b15dfee1c257be3d262270dc2ba27001977bf` where the evidence is meant to describe the completed A3 state.

Do not change historical CI evidence for baseline commit `957f062...`.

### NEXT_ACTION.md

Route to A3R while executing, then after PASS restore next task to:

`CI-PHP-CONTRACT-D1`

## Acceptance criteria

- `A3R-AC-01` No application/business source changes.
- `A3R-AC-02` `PROJECT_STATE.json.repo_commit` equals `da5b15dfee1c257be3d262270dc2ba27001977bf`.
- `A3R-AC-03` `repo_commit_semantics = STATE_BASIS_COMMIT`.
- `A3R-AC-04` exact current HEAD is defined as Git-branch-ref authority, not embedded self-reference.
- `A3R-AC-05` no file claims `0a7b3cce...` is the A3 closeout/current-state commit.
- `A3R-AC-06` baseline CI evidence remains bound to `957f062...`.
- `A3R-AC-07` blocker `CI_PHP_LOCKFILE_COMPATIBILITY` remains unresolved and unchanged.
- `A3R-AC-08` Factory and adapter remain candidate/reference-only.
- `A3R-AC-09` no DB write, migration, OpenAI request, provider mutation, feature activation, dependency change, or CI fix.
- `A3R-AC-10` JSON/CSV parse and referenced-path validation PASS.
- `A3R-AC-11` branch is committed/pushed cleanly.
- `A3R-AC-12` next task is `CI-PHP-CONTRACT-D1`.

## Validation

At minimum:

- parse `PROJECT_STATE.json`;
- parse `EVIDENCE_INDEX.json` if touched;
- parse `TEST_MATRIX.csv`;
- grep/search for stale `0a7b3cceaae4c25181e874e548add308d1715c82` claims and verify any remaining occurrence is historically accurate, not mislabeled current/closeout state;
- verify no diff under `application/web/app`, `application/web/database`, or `application/web/routes`;
- verify no secret indicators introduced;
- verify branch worktree clean after commit;
- push to the same A3 branch.

## Stop conditions

Stop with `HOLD` if remediation requires application source, dependency, CI workflow, database, provider, or deployment changes.

Do not start `CI-PHP-CONTRACT-D1` inside A3R.
