# Prompt Library Index

These prompts are reusable. Replace text inside `[ ... ]` only when needed.

| Prompt | Use when |
|---|---|
| `01_START_CURRENT_TASK.txt` | Start the current task from repository state. |
| `02_CONTINUE_NEXT_TASK.txt` | Continue to the next executable task after a completed task. |
| `03_RESUME_AFTER_INTERRUPTION.txt` | New Codex chat/session after interruption. |
| `04_STATUS_ONLY_NO_CHANGES.txt` | Audit status without modifying files. |
| `05_BUG_FIX.txt` | Report a defect/regression. |
| `06_FEATURE_OR_CHANGE_REQUEST.txt` | Request a new or changed behavior. |
| `07_POLICY_DECISION_INPUT.txt` | Give a management/business decision for a pending policy. |
| `08_SHARED_CORE_OR_CROSS_MODULE_CHANGE.txt` | Deliberately change shared contracts/Core. |
| `09_DATABASE_SCHEMA_CHANGE.txt` | Deliberately request schema evolution. |
| `10_TEST_AND_REGRESSION_AUDIT.txt` | Run verification without adding features. |
| `11_PREPARE_STAGING_RELEASE.txt` | Prepare a tested staging/release candidate. |
| `12_ROLLBACK_OR_HOTFIX.txt` | Recover from a bad release/regression. |
| `13_CLOSE_TASK_AND_UPDATE_PROGRESS.txt` | Force proper task closeout/documentation. |
| `14_NEW_MODULE_PLANNING.txt` | Plan a future module before implementation. |
| `15_AI_FUTURE_WORK.txt` | Reopen future OpenAI work only when authorized. |
| `16_COMMUNICATION_FUTURE_WORK.txt` | Reopen future Communication/WhatsApp work only when authorized. |
| `17_ARCHITECTURE_ONLY_NO_CODE.txt` | Explore/design without implementation. |
| `18_UI_UX_CHANGE.txt` | Request an interface change while preserving backend contracts. |

| `19_CHECKPOINT_CHOICE.txt` | Ask Codex to offer several next checkpoint horizons before continuing. |
| `20_SAFE_CHECKPOINT_NOW.txt` | Emergency safe stop before closing/sleeping the MacBook. |

For the very first Codex session, prefer `../00_START/COPY_PASTE_FIRST_PROMPT.txt`.

## v1.16 quota-efficient prompts
- `21_QUOTA_EFFICIENT_START.txt` — preferred fresh-session start; minimum active context.
- `22_QUOTA_EFFICIENT_CONTINUE.txt` — continue current task without re-reading the full bundle.
