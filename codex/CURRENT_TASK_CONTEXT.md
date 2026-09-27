# CURRENT TASK CONTEXT

**Task:** `IMTAQ-REPO-ADOPTION-A3R` State Commit Semantics Correction  
**State:** `READY_FOR_EXECUTION`  
**Current phase:** `EXISTING_REPO_ADOPTION / REPOSITORY STATE REMEDIATION`  
**Task branch:** `chore/IMTAQ-REPO-ADOPTION-A3-state-reconciliation`  
**Baseline main:** `957f062815147a0cc2ea2200fd82692c3447f3e1`  
**A3 state-basis commit:** `da5b15dfee1c257be3d262270dc2ba27001977bf`

## Why A3R exists

A3 completed its substantive state reconciliation, but some state artifacts still label the earlier preparation commit `0a7b3cce...` as if it were the A3 closeout/current-state commit.

A3R corrects only commit semantics. It must distinguish:

- repository state-basis commit = `da5b15dfee1c257be3d262270dc2ba27001977bf`;
- exact current branch HEAD = resolve from Git branch ref at audit/task start time.

Do not create a self-referential state file that tries to contain the SHA of the commit that contains itself.

## REQUIRED NOW

1. `codex/TASK_CONTEXTS/IMTAQ-REPO-ADOPTION-A3R.md`
2. `PROJECT_STATE.json`
3. `TEST_MATRIX.csv`
4. `NEXT_ACTION.md`
5. `EVIDENCE_INDEX.json` only if the same commit-semantics issue affects it.

Do not bulk-read the repository.

## Expected writes

Only repository-state metadata necessary for A3R:

- `PROJECT_STATE.json`
- `TEST_MATRIX.csv`
- `codex/CURRENT_TASK_CONTEXT.md`
- `NEXT_ACTION.md`
- `EVIDENCE_INDEX.json` only if required.

## Boundaries

- No application/business source mutation.
- No framework/dependency/Composer/PHP change.
- No CI compatibility fix.
- No migration or database write.
- No live OpenAI request.
- No provider/DRAFT/ACTIVE/active-pointer mutation.
- No Public Academic AI activation.
- No deployment.
- Universal Repo Factory remains `CANDIDATE / REFERENCE ONLY`.
- `WEB_FULLSTACK_SERVICE_API` remains candidate only.

## Known unresolved blocker

`CI_PHP_LOCKFILE_COMPATIBILITY`

A3R must preserve this blocker unchanged. Do not solve it here.

## Exit

When A3R acceptance criteria pass:

- mark `IMTAQ-REPO-ADOPTION-A3R = COMPLETED / PASS`;
- commit and push to this same branch;
- keep `CI-PHP-CONTRACT-D1` as the next task;
- stop for ChatGPT repository audit.

ChatGPT will read the resulting evidence directly from GitHub.
