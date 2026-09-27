# Handoff Control Center — v1.16

Designed for a non-programmer project owner and quota-efficient Codex operation.

## Folder map
- `00_START/` — start instructions and copy-paste prompt.
- `01_PROMPTS/` — implementation, continuation, bugs, changes, tests, staging, rollback, checkpoint and quota-efficient prompts.
- `02_USER_GUIDE/` — operating guides including quota efficiency.
- `03_CHECKLISTS/` — acceptance/safety/release checklists.
- `04_EXAMPLES/` — examples.
- `05_REFERENCE/` — authority/scope/manifest snapshots.

## Normal pattern
1. Start with `21_QUOTA_EFFICIENT_START.txt` (or root `CODEX_FIRST_PROMPT.txt`).
2. Codex reads only current task context + REQUIRED NOW.
3. Codex completes one meaningful atomic checkpoint, tests it, and stops.
4. Choose a timed next option.
5. For task transition, durable state + a new current task context replace long conversational history.
6. Do not manually pick source files for deployment.
