# Upload & Start Instructions

## A. Prepare the Codex workspace

1. Extract the **entire** `SISTEM_IMTAQ_Codex_Project_v1.16` folder.
2. Open that folder as the repository/workspace root in Codex.
3. Do not upload only `docs/`, only `application/`, or only a prompt file. Codex needs the whole repository because governance, task queues, module boundaries and tests are part of the implementation contract.
4. Do not create a second unrelated Laravel project outside `application/web/`.
5. Keep the repository root intact.

## B. Start the first Codex session

Paste the exact content of:

`handoff/00_START/COPY_PASTE_FIRST_PROMPT.txt`

Codex should then:
- use the minimum active context (`AGENTS.md`, `codex/CURRENT_TASK_CONTEXT.md`, `NEXT_ACTION.md`, REQUIRED NOW);
- verify `IMP-S0-001`;
- begin only the first safe atomic foundation checkpoint;
- lazy-load other documents only when triggered;
- not implement later features;
- stop with `SAFE_TO_CLOSE` and timed checkpoint choices.

## C. After the first task

When Codex completes a task:
- review the Change Manifest and test results;
- do not ask which files you should upload;
- keep the repository as the Source of Truth;
- continue using `handoff/01_PROMPTS/02_CONTINUE_NEXT_TASK.txt`, or simply choose option 1 if Codex's option 1 is the correct executable next step.

## D. If the Codex session is interrupted

Use:

`handoff/01_PROMPTS/03_RESUME_AFTER_INTERRUPTION.txt`

Codex must re-read repository state rather than relying on memory from a previous session.

## E. If you only want a review

Use:

`handoff/01_PROMPTS/04_STATUS_ONLY_NO_CHANGES.txt`

That prompt explicitly forbids edits.


## Session behavior
After the first atomic step, Codex should stop at a safe checkpoint and offer several next checkpoint horizons with estimated ranges. Choose a number based on the time available. Do not close the local MacBook during an active local command unless Codex reports `SAFE_TO_CLOSE = YES`.
