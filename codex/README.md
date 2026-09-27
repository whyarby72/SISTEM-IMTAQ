# Codex Control Center

This folder makes the project resumable across Codex sessions.

- `TASK_QUEUE.md` — all implementation tasks, dependencies and current status.
- `WORK_PROTOCOL.md` — how Codex selects, executes and closes tasks.
- `CONTEXT_MAP.md` — which document answers which question.
- `WORK_LOG.md` — append-only implementation session log.
- `DECISION_ESCALATION.md` — how to handle policy blockers.
- `MASTER_PROMPT_FOR_CODEX.txt` — optional bootstrap prompt for a new Codex conversation.

The current task is always declared in `/NEXT_ACTION.md`.

## Complete handoff prompt library
For a fresh handoff, use `/CODEX_FIRST_PROMPT.txt`. Reusable owner prompts are in `/handoff/01_PROMPTS/`.
