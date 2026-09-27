# AI Provider Configuration Governance v1.0

Status: AI-A5K implementation checkpoint — `BLOCKED / PILOT MIGRATION NOT APPLIED`
Date: 2026-09-20

## Authority

Database-managed active configuration is the only runtime authority for provider credential, model, max output tokens, and provider runtime enablement after migration. The browser, request payload, LLM, and legacy environment values are not authority.

The public Waka AI feature remains separate and OFF: `academic.ai.assistant_enabled=false`.

## Access and lifecycle

- Provider administration is `SUPER_ADMIN_ONLY` through the canonical institution-wide authorization service.
- Waka Akademik, Wali Kelas, and unauthenticated users have no access.
- Credential plaintext is accepted transiently, encrypted at rest, hidden from serialization, masked as last4, never flashed, logged, audited, or placed in URLs/recovery artifacts.
- Credential lifecycle: `PENDING → VERIFIED/STANDBY → ACTIVE through configuration pointer → SUPERSEDED/REVOKED`.
- Active credential direct revoke is rejected.
- Configuration lifecycle: `DRAFT → VERIFIED → explicit transactional ACTIVATE`; no draft-to-active path.
- Model changes create new configuration versions.
- Failed verification/rotation leaves the current active pointer intact.
- Automatic credential fallback is prohibited.

## Provider and model controls

- Discovery is server-side through stored credential; browser never calls OpenAI.
- Available, verified, and active are separate states.
- Unverified models cannot activate.
- Runtime kill switch is separate from public AI activation and fail-closed when disabled.
- `store=false`, sequential tool calls, exact five production schemas, system instructions, authorization, grounding, and rate limits remain server-controlled.

## Migration gate

The minimum migration creates credential, versioned configuration, and active-pointer tables. PostgreSQL `migrate --pretend` passed and all application tests exercise the migration through SQLite. A fresh PostgreSQL custom backup and `pg_restore --list` passed, but the requested disposable PostgreSQL create/apply/drop operation was rejected by the execution approval runner. Therefore the pilot migration remains pending and must not be applied until disposable verification is separately approved and passes.

## Current safe state

- Credential: NONE in pilot database.
- Active configuration: NONE in pilot database.
- Provider runtime: OFF / NOT_CONFIGURED.
- Public AI feature: OFF.
- Legacy env provider configuration: retained for compatibility inspection only; `OpenAiResponsesProvider` does not use it as a runtime fallback.

