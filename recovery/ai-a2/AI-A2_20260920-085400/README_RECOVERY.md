# AI-A2 Recovery Checkpoint

Task: OpenAI Responses Runtime + Typed Tool Orchestration  
Created: 2026-09-20 08:54:00 Asia/Jakarta  
Repository: SISTEM-IMTAQ  
Git: NOT AVAILABLE — external manifest recovery applies

## Recovery boundary

This checkpoint contains the AI-A2 server-side provider/runtime layer only:

- provider-agnostic Academic AI provider contract;
- OpenAI Responses API adapter over Laravel HTTP Client;
- strict schemas for exactly five AI-A1 read tools;
- bounded sequential tool-call orchestration;
- ambiguity guard, provider failure handling, request tracing, and audit metadata;
- deterministic provider-fake/security tests.

It contains no HTTP route, controller, chat UI, migration, seed/import, attendance write, occurrence write, or historical rewrite.

## Restore procedure

1. Preserve the current application source before restoring.
2. Restore only files listed in `POST_AI_A2_INTENTIONAL_FILES.sha256`.
3. Verify hashes with `sha256sum -c POST_AI_A2_INTENTIONAL_FILES.sha256`.
4. Re-run the focused AI tests, Academic suite, full suite, lint/Pint, and view cache.
5. Keep provider credentials outside the repository; do not restore `.env` or secrets.

## Baseline evidence

The repository has no Git metadata. AI-A1's accepted post-checkpoint hashes are preserved in `PRE_AI_A1_BASELINE_HASHES.sha256` for the AI-A1 files that AI-A2 extended. Files newly created by AI-A2 are marked `ABSENT_BEFORE_AI_A2` in the pre-change inventory because no Git commit or durable pre-change snapshot existed.

## Runtime safety

- `store=false`, `tool_choice=auto`, and `parallel_tool_calls=false` are explicit.
- The API key is read server-side from configuration only and is absent from logs/results/artifacts.
- Provider-side durable conversation state, built-in tools, SQL, and write tools are not enabled.
- Live OpenAI smoke was not run; tests use a deterministic fake provider.

