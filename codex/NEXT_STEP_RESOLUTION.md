# CODEX NEXT-STEP RESOLUTION

When the user asks "lanjut", "langkah berikutnya", or otherwise expects Codex to decide what is next:

1. Read `NEXT_ACTION.md`.
2. Verify its dependency/status in `codex/TASK_QUEUE.md` (or the active module queue if one is introduced later).
3. Verify module status in `modules/MODULE_REGISTRY.json`.
4. If the task changes a module/shared contract, classify impact using `modules/CHANGE_IMPACT_RULES.md`.
5. Check policy-pending/superseded registers before implementing.
6. If no executable active-module task remains, read `codex/SYSTEM_TASK_QUEUE.md` and `project_management/MODULE_ROADMAP.md`.
7. Never jump into implementation for a module with status `NOT_PLANNED`; recommend/perform its planning gate instead.
8. End the user-facing turn with progress plus 3–4 numbered choices; option 1 is the primary recommendation.


## Shared Communication resolution
If the user asks for WhatsApp/broadcast/parent-delivery implementation, first verify `docs/09_communication/COMMUNICATION_ROADMAP.md` gates. If Guardian/recipient/consent or published parent-artifact prerequisites are not ready, recommend/execute the prerequisite planning task rather than coding a direct provider integration.
