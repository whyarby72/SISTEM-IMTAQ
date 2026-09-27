# AI Provider & API Key Architecture v1.1

**Status:** `DESIGN_LOCKED`.

## 1. Selected provider decision
**OpenAI API is the selected AI provider for SISTEM IMTAQ.**

Self-hosted/local AI is `OUT_OF_CURRENT_TARGET`: no GPU server, model runtime, model download/serving stack, self-hosted inference API, or local-model operational dependency is authorized by this design. A future management decision may revisit this, but Codex must not prepare or implement it now.

The server-side `OpenAIProvider` adapter should use the current supported OpenAI API interface selected at implementation time. OpenAI-specific calls must remain inside the shared AI integration boundary rather than being embedded throughout domain modules.

## 2. Required secret
The OpenAI credential is an **API key**, not a user-facing token. The real key must never be committed to this repository.

Expected server configuration:

```text
OPENAI_API_KEY=<server secret>
IMTAQ_AI_ENABLED=false
IMTAQ_AI_READ_ENABLED=false
IMTAQ_AI_DRAFT_ENABLED=false
IMTAQ_AI_WRITE_ENABLED=false
IMTAQ_AI_PROVIDER=openai  # selected provider; not a runtime provider selector for current scope
IMTAQ_AI_MODEL_CHAT=<configured model id>
```

The exact model ID must remain environment/configuration-driven.

## 3. Secret placement
Allowed:
- server environment variables;
- deployment secret manager;
- CI/CD secret store.

Forbidden:
- source code;
- Git history;
- `AGENTS.md`;
- documentation examples containing a real key;
- browser JavaScript/local storage;
- mobile binary/client bundle;
- database fields visible to normal application users;
- AI conversation history.

## 4. Request route

```text
Browser/User
   ↓ authenticated request
Laravel backend
   ↓ provider adapter
OpenAI API
```

Never:

```text
Browser/User → OpenAI API using exposed project key
```

## 5. Feature flags
AI must be progressively enabled:
- `IMTAQ_AI_ENABLED`: master switch.
- `IMTAQ_AI_READ_ENABLED`: read-only tools.
- `IMTAQ_AI_DRAFT_ENABLED`: structured draft proposals.
- `IMTAQ_AI_WRITE_ENABLED`: controlled write tools.

A child capability cannot be considered enabled if the master AI switch is disabled.

## 6. OpenAI failure handling
Normalize OpenAI/provider-boundary errors into application-safe categories, for example:
- `AI_PROVIDER_UNAVAILABLE`
- `AI_RATE_LIMITED`
- `AI_AUTH_CONFIGURATION_ERROR`
- `AI_TIMEOUT`
- `AI_INVALID_TOOL_OUTPUT`

Do not return provider stack traces or credentials to users.

Retry behavior must avoid duplicate domain actions. Provider retries and domain-command idempotency are separate concerns.

## 7. Secret rotation
The application must permit API-key rotation without code changes. No persistent business record may depend on a specific API key value.

## 8. Development/test behavior
Automated domain tests must not require real OpenAI calls. Use a fake/mock `AIProvider` for deterministic tests. OpenAI integration tests requiring a real key must be opt-in and must not run in normal CI without an explicitly configured secret.

## 9. No core dependency on API availability
The OpenAI API key is required only when AI features are enabled. Missing/invalid credentials, rate limits, timeout or OpenAI outage must disable/degrade AI capabilities only. Academic, Tahfizh, Kesantrian, Shared Core, reporting, communication and other deterministic workflows must remain operational.

## 10. No self-hosted fallback requirement
There is no requirement to run a local/self-hosted model as an automatic fallback. If OpenAI is unavailable, the approved behavior is graceful AI unavailability while core workflows continue. This deliberately avoids adding heavy server dependencies solely to preserve AI availability.
