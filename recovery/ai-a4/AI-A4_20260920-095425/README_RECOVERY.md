# AI-A4 Recovery Checkpoint

Task: Waka Academic Assistant Chat UI  
Created: 2026-09-20 09:54:25 Asia/Jakarta  
Repository: SISTEM-IMTAQ  
Git: NOT AVAILABLE — external manifest recovery applies

## Recovery boundary

This checkpoint contains only the dashboard UI and test-environment hardening for AI-A4. The UI remains behind `academic.ai.assistant_enabled`, whose accepted default is `false`.

## Restore procedure

1. Preserve the current application source before restoring.
2. Restore only files listed in `POST_AI_A4_INTENTIONAL_FILES.sha256`.
3. Verify hashes with `sha256sum -c POST_AI_A4_INTENTIONAL_FILES.sha256`.
4. Keep `ACADEMIC_AI_ASSISTANT_ENABLED=false` and all provider credentials outside the repository.
5. Run the AI-A3/A2/A1, Academic, and full suites with the test harness; run `php artisan view:cache`, PHP lint, and Pint.

## Safety evidence

- No new endpoint: browser uses the existing same-origin AI-A3 POST route.
- Only `question` is sent by the browser; server-owned identity, tools, provider, model, and instructions remain unavailable to the client.
- CSRF token is sent through the existing web session boundary.
- Answer and warning strings are rendered as text, never as raw HTML or Markdown.
- No local/session/IndexedDB persistence, analytics, console logging, write controls, or retry loop.
- UI is hidden when the feature gate is OFF and is limited to full academic authority dashboard roles.
- PHPUnit base harness enforces SQLite in-memory and array cache/session so stale cached pilot configuration cannot route tests to PostgreSQL.
- No pilot PostgreSQL write, migration, import, seed, or live OpenAI request occurred.
