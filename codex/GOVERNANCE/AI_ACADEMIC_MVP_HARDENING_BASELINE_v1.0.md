# AI Academic MVP Hardening Baseline v1.0

Status: AI-A5 `CLOSED / PASS` — 2026-09-20

This baseline governs the disabled, read-only Waka Akademik AI MVP. It preserves the AI-A0 through AI-A4 contracts and does not activate the pilot.

## Security and grounding boundary

- The only allowed domain is Waka Akademik student-attendance read assistance.
- Exactly five server-registered typed read tools remain available; no new tool, write action, migration, or browser-direct provider call is introduced.
- Factual academic answers require a successful tool result. Tool-free output is limited to clarification, limitation, or safe refusal; otherwise the runtime returns `FACTUAL_TOOL_EVIDENCE_REQUIRED` without exposing the model text.
- Runtime reports `TOOL_GROUNDED`, `NO_TOOL`, `TOOL_INCOMPLETE`, or `TOOL_ERROR` and preserves server warnings independently of model text.
- User questions, tool payloads, and model output are untrusted data. Prompt injection cannot alter role, authority, tool registry, privacy boundary, or read-only behavior.

## Privacy, audit, and evidence

- OpenAI Responses requests use `store=false` and do not claim zero data retention.
- Audit metadata includes deterministic instruction version, request correlation, actor, provider/model identifiers, provider request ID, tools, status, rounds, usage, grounding state, and warning counts.
- The question audit digest is HMAC-SHA256 using the server application key; raw questions, API keys, authorization headers, `.env`, passwords, and credentials are not emitted to UI, JSON, logs, or recovery artifacts.
- Tool evidence remains bounded and explicit; truncation or incomplete canonical evidence remains a warning.

## Cost and provider boundary

- Server-owned limits: question length 4,000 characters, 10 requests/minute, bounded tool rounds, bounded tool result pages, timeout, and `max_output_tokens` default 800.
- The client cannot override model, tool list, rounds, timeout, or output-token limits.
- Provider transport has no browser retry loop or unbounded retry. Provider failures map to deterministic safe runtime failure without raw provider detail.
- Usage accounting is carried through provider response metadata; no hardcoded price is claimed.

## Activation and data protection

- `academic.ai.assistant_enabled` remains OFF by default.
- No pilot activation, real-student live smoke, academic business write, migration, schema change, seed/import, or historical rewrite is authorized by this baseline.
- Synthetic live OpenAI smoke is `NOT_RUN_CONFIGURATION_MISSING`: safe inspection found no configured API key and provider disabled. The configured model presence alone is insufficient.

## Verification

- AI-A5 focused: 29 tests / 102 assertions.
- Academic regression: 360 tests / 1,445 assertions.
- Full regression: 473 tests / 1,913 assertions.
- Blade view cache, PHP lint, and Pint: PASS.

