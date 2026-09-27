# AI Usage, Cost & Observability v1.0

**Status:** `DESIGN_LOCKED` architecture; exact budgets/pricing are operational configuration.

## 1. Usage record
Recommended shared table when AI is implemented:

```text
ai_usage_logs
-------------
id
ai_interaction_id
user_id
module
provider
model
capability_level      -- READ / DRAFT / ACTION
input_tokens nullable
output_tokens nullable
total_tokens nullable
cached_input_tokens nullable
latency_ms
success
error_class nullable
request_at
```

Do not store provider secrets in usage logs.

## 2. Interaction/action lineage
Recommended structures:
- `ai_interactions`: one user conversational request/response unit or trace.
- `ai_tool_executions`: each selected tool, authorization result, duration and success/failure.
- business audit record may reference `ai_interaction_id` when the human confirms an AI-assisted action.

Conversation text retention is a separate privacy policy and should not be required for token accounting.

## 3. Cost
Token counts should be recorded from provider usage metadata when available. Pricing changes over time, therefore:
- do not hard-code a permanent currency cost into business facts;
- if application cost estimation is added, use a versioned/effective-dated pricing configuration;
- provider billing dashboard remains an external reconciliation source.

## 4. Controls
Support future configuration for:
- global AI on/off;
- per-module capability enablement;
- per-role daily/monthly quota if needed;
- maximum output size;
- timeout/retry limits;
- project/month budget alerts outside or alongside application monitoring.

Do not invent quota values before management/operations decides them.

## 5. Operational metrics
Useful AI platform metrics:
- request count;
- success/error rate;
- input/output/total token usage;
- latency;
- tool-call count;
- validation rejection count;
- confirmation-to-execution rate for write tools;
- estimated cost when configured;
- provider unavailable/rate-limit events.

These are AI/platform metrics, not student KPI.
