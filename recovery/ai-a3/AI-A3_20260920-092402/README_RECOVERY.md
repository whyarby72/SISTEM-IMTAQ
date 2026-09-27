# AI-A3 Recovery Checkpoint

Task: Authenticated Waka AI Query Endpoint  
Created: 2026-09-20 09:24:02 Asia/Jakarta  
Repository: SISTEM-IMTAQ  
Git: NOT AVAILABLE — external manifest recovery applies

## Recovery boundary

This checkpoint contains the AI-A3 HTTP boundary only:

- one authenticated POST route;
- server-side Waka authorization delegated to AI-A1 authorization;
- default-OFF feature gate;
- strict question validator;
- authenticated-identity rate limiter;
- sanitized JSON response and `Cache-Control: no-store`;
- provider/runtime failure mapping and correlation continuity.

No AI pilot activation, chat UI, streaming, durable history, Academic write capability, migration, seed/import, or database schema change is included.

## Restore procedure

1. Preserve current application source before restoring.
2. Restore only files listed in `POST_AI_A3_INTENTIONAL_FILES.sha256`.
3. Verify hashes with `sha256sum -c POST_AI_A3_INTENTIONAL_FILES.sha256`.
4. Keep `ACADEMIC_AI_ASSISTANT_ENABLED=false` and provider credentials outside the repository.
5. Re-run the AI-A3 focused suite, Academic suite, full suite, lint/Pint, and view cache.

## Safety evidence

- Route is POST-only and carries `web`, `auth`, and `throttle:academic-ai` middleware.
- Client controls only `question`; model, tools, system instructions, role, user ID, storage, and loop limits are server-owned.
- Feature gate was verified `assistant_enabled=false` after implementation.
- Tests use SQLite in-memory explicitly; PostgreSQL pilot was inspected read-only only.
- No live student data was sent to OpenAI and no live OpenAI smoke was run.

