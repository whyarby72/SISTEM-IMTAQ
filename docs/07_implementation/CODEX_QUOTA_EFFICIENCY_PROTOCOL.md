# CODEX QUOTA EFFICIENCY PROTOCOL v1.0

## Objective
Reduce Codex agentic usage without weakening architecture, testing, security, auditability, or safe-maintenance controls.

The full repository remains the project knowledge base. **It is not the default active context.**

## 1. Minimum Active Context
At task start, load only:
- `AGENTS.md`;
- `codex/CURRENT_TASK_CONTEXT.md`;
- `NEXT_ACTION.md`;
- the task context's **REQUIRED NOW** files.

Do not bulk-read roadmaps, future modules, the full Core Engine baseline, all architecture docs, or all tests.

## 2. Lazy context expansion
Expand context only when a concrete implementation question/trigger requires it. Use `codex/CONTEXT_ROUTER.md`.

Before opening a long file, search for the relevant heading/term and read the smallest useful section where the client supports targeted reads.

## 3. One task / short task family per thread
Prefer a fresh Codex thread when a task is completed or the work changes domain substantially. Durable state belongs in Git, task context, work log, tests and repository files — not an endlessly growing chat transcript.

For interruption within the same task, resume from repository state and `codex/CURRENT_TASK_CONTEXT.md`; do not replay the entire historical conversation.

## 4. Atomic engineering checkpoints, not micro-turns
Checkpoint after a coherent result (for example scaffold + verification, migration + model + targeted test), not after every file creation.

The owner still receives multiple time-horizon choices. Quick checkpoints are for genuine time constraints; the recommended option should normally complete a meaningful atomic result.

## 5. Test economy without test avoidance
During implementation:
- run the smallest targeted test/check that validates the current atomic change;
- run required broader regression at task completion, contract change, security/global gate, staging or release gate;
- do not repeatedly rerun the whole suite after edits unrelated to it.

P0/security/integrity tests remain mandatory.

## 6. Output economy
At normal checkpoints, report only:
- completed result;
- changed scope;
- verification result;
- blocker/risk if any;
- `SAFE_TO_CLOSE`;
- exact resume point;
- verified progress if changed;
- next timed choices.

Do not restate the full architecture on every turn.

## 7. Model/reasoning economy
Where the Codex client supports model selection, use `codex/MODEL_AND_REASONING_POLICY.md`. Prefer the least expensive model/reasoning level that reliably passes the task's tests and review criteria.

## 8. No quota-driven unsafe shortcuts
Never save quota by:
- skipping authorization/security checks;
- editing production directly;
- bypassing migrations/audit;
- collapsing domain boundaries;
- accepting failing required tests;
- guessing `POLICY_PENDING`;
- hiding a blocker.

## 9. Context refresh rules
Re-read a file only when:
- it changed;
- the current question depends on an exact clause not retained in working context;
- a task/domain boundary changes;
- a reviewer asks for source verification.

## 10. Task transition
When a task closes:
1. update durable repository state;
2. create/update the next `codex/CURRENT_TASK_CONTEXT.md`;
3. start the next task in a fresh short thread when practical.

This makes the next session start from a small deterministic context rather than the previous chat history.
