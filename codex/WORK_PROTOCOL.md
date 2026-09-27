# Codex Work Protocol — Quota-Efficient

## 1. Select/confirm work
- Start from `codex/CURRENT_TASK_CONTEXT.md` + `NEXT_ACTION.md`.
- Only inspect the relevant task-queue row/dependencies when task executability must be verified or changed.
- If moving to another task, update the current task context before implementation.

## 2. Prepare minimum context
- Read only task-context `REQUIRED NOW` files.
- Expand through `codex/CONTEXT_ROUTER.md` only on concrete triggers.
- For non-trivial changes, declare expected write scope and impact class before editing; load the full safe-maintenance contract only when the change actually requires it.
- Identify policy blockers without reading the entire policy register unless necessary; search the exact concept first.

## 3. Implement one selected checkpoint
- Work toward one coherent engineering result, not micro-turns.
- Use Minimum Necessary Change.
- Do not silently expand protected/shared scope.
- Preserve module ownership/Source-of-Truth rules.
- Add/modify targeted automated tests with the implementation.
- Applied migrations are immutable.

## 4. Verify economically and correctly
- During an atomic step, run the smallest targeted check/test that proves the change.
- Run broader required regression at task closeout or when the impact class/security/global contract demands it.
- Never hide failures or skip mandatory integrity/security tests to save quota.

## 5. Checkpoint
After the coherent result:
- stop starting new work;
- report running process, relevant Git/test state, `SAFE_TO_CLOSE = YES/NO`, exact resume point;
- give 2–4 timed checkpoint choices, option 1 primary;
- wait for the owner's selection.

Do not update many status files just because a checkpoint occurred. Update durable records only when their underlying fact changed or the protocol requires a resume record.

## 6. Full task closeout
When the task is DONE/BLOCKED:
1. update relevant task row and `WORK_LOG.md`;
2. update module/project status only if lifecycle/gate changed;
3. update `NEXT_ACTION.md`;
4. create/update `codex/CURRENT_TASK_CONTEXT.md` for the next task when known;
5. produce Change Manifest for non-trivial code work;
6. run required broader tests/structure checks;
7. run `python scripts/update_project_progress.py` when progress evidence changed.

## 7. Thread hygiene
Prefer a new Codex thread after a completed task or major domain change. Repository/Git/task context are durable memory; do not carry an indefinitely growing chat when a fresh task-scoped thread is sufficient.
