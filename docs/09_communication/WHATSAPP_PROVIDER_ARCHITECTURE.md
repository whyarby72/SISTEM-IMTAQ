# WhatsApp Provider Architecture v1.1

**Status:** provider integration `DEFERRED_FUTURE`.

## Provider abstraction
Shared Communication depends on a provider-neutral interface, not directly on provider SDKs throughout the codebase.

Conceptual interface capabilities may include:

```text
MessagingProvider
  - sendTemplateMessage(...)
  - get/normalize provider message reference
  - verifyWebhook(...)
  - normalizeDeliveryEvent(...)
  - normalizeApprovedInboundResponse(...)   # future action-request support
```

First adapter target:
`WhatsAppProvider`

Future adapters may include email/in-app/push or other institutional channels without changing Academic/Tahfizh/Kesantrian business logic.

## Secret handling
Provider credentials/tokens are backend secrets only.
Never place real credentials in:
- repository files;
- frontend JavaScript;
- `AGENTS.md`;
- task prompts/logs;
- database fields visible to ordinary application users.

Use server environment/secret management and least-privilege provider credentials.

## Outbound transport
- Use queues/jobs for sends and retries.
- Use an idempotency/dedup key for each logical send operation.
- Store provider message IDs/references only as transport metadata.
- A provider API response does not replace institutional audit/state.

## Webhooks and inbound responses
Incoming callbacks/replies must:
1. be authenticated/verified according to provider capabilities;
2. map provider event/reply to an existing outbound message/request;
3. be idempotent/replay-safe;
4. append delivery/response events rather than silently overwrite history;
5. never allow a webhook to directly mutate source Academic/Tahfizh/Kesantrian facts;
6. for action requests, normalize only approved response options and route the structured response to Shared Communication/domain contracts.

## External policy dependency
WhatsApp Business/API policies, template requirements, conversation windows, pricing, supported interactive actions and technical endpoints can change. Before implementation/go-live, Codex must require current provider-policy verification and record the resulting integration decision. Do not hard-code volatile external policy in architecture.
