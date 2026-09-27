# Operating Codex Without Being a Programmer

The project owner controls **business intent and approval**. Codex controls **code impact analysis, file selection, implementation and tests**.

## What you normally say

### To start
Use `../00_START/COPY_PASTE_FIRST_PROMPT.txt`.

### To continue
You may simply answer `1` when Codex's option 1 is the correct recommended next task. For a fresh session, use `../01_PROMPTS/02_CONTINUE_NEXT_TASK.txt`.

### To report a bug
Describe what you saw, what you expected and who was affected. Use `../01_PROMPTS/05_BUG_FIX.txt`.

### To ask for a change
Describe the desired behavior and problem. Use `../01_PROMPTS/06_FEATURE_OR_CHANGE_REQUEST.txt`.

You should **not** need to say:
- which controller/model/file to edit;
- which migration to rewrite;
- which PHP files to upload;
- which database row to edit manually.

## What to inspect after Codex works

Look for:
1. task status;
2. tests/checks;
3. Change Manifest;
4. files/migrations/config affected;
5. shared/protected-zone impact;
6. deployment/rollback note;
7. updated verified progress;
8. next recommended task.

## When to be cautious
Stop and ask Codex to explain if it:
- wants to rewrite many unrelated files for a small request;
- changes Shared Core for a module-local request without impact analysis;
- edits an old applied migration;
- asks you to manually overwrite production source files one by one;
- activates a POLICY_PENDING/FUTURE feature;
- bypasses RBAC/audit/correction workflow;
- hard-codes a class, role or OpenAI provider inside a domain module;
- reports DONE with failing tests.


## Checkpoint pilihan waktu
Setelah satu langkah aman selesai, Codex harus berhenti dan menawarkan beberapa horizon checkpoint. Anda cukup memilih nomor berdasarkan waktu yang tersedia. Lihat `CHECKPOINT_CHOICE_GUIDE.md`.

Contoh: option 1 recommended ±15–25 menit, option 2 quick ±5–10 menit, option 3 extended ±30–60 menit, dan STOP NOW bila `SAFE_TO_CLOSE = YES`. Estimasi adalah kisaran, bukan janji.

Jika ingin berhenti mendadak, gunakan `../01_PROMPTS/20_SAFE_CHECKPOINT_NOW.txt`.
