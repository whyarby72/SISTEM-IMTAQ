# AI-A1 Recovery Checkpoint

Task: Typed Academic Read Tools  
Created: 2026-09-19 20:53:29 Asia/Jakarta  
Repository: SISTEM-IMTAQ  
Git: NOT AVAILABLE — external manifest recovery applies

## Scope

This checkpoint preserves the AI-A1 deterministic, provider-agnostic, read-only tool layer. It contains no OpenAI provider, LLM runtime, HTTP chat endpoint, UI, migration, or pilot database dump.

## Recovery method

1. Preserve the application source at the checkpoint timestamp.
2. Restore only the intentional AI-A1 source/test paths listed in `POST_AI_A1_INTENTIONAL_FILES.sha256`.
3. Use the unchanged canonical dependency hashes in `PRE_AI_A1_BASELINE_DEPENDENCY_HASHES.sha256` to verify the AI-A1 read boundary was built against the expected source.
4. Re-run the focused, Academic, and full test commands recorded in `AI_A1_VALIDATION_EVIDENCE.md`.
5. Do not run migrations, seed/import, or point tests at pilot PostgreSQL.

The repository has no Git metadata, so this is an external durable recovery manifest rather than a commit checkpoint.

