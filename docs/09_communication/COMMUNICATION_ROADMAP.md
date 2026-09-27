# Shared Communication Roadmap v1.1

Implementation is intentionally deferred and excluded from the current Academic MVP/system-domain implementation denominator.

## Recommended future order

### COM-0 — Recipient and identity foundation
- canonical Guardian recipient context for parent delivery;
- canonical Staff/Organization/Assignment context for internal audiences;
- verified contact channels;
- consent/preferences policy where required;
- communication audience/group ownership policy.

### COM-1 — Provider-neutral communication core
- message purpose: `ANNOUNCEMENT`, `REMINDER`, `ACTION_REQUEST`, parent report delivery;
- versioned templates;
- outbound batch/message model;
- queue/jobs;
- messaging provider contract;
- audit/idempotency;
- recipient snapshot.

### COM-2 — Audience & institutional broadcast
- dynamic audience resolver;
- managed communication groups;
- sender permission/scope checks;
- preview recipient count/exclusions;
- controlled internal broadcast pilot.

### COM-3 — Scheduled reminders
- event/schedule-triggered reminder generation;
- idempotent scheduling;
- provider-neutral delivery;
- reminder observability.

### COM-4 — Action-request framework
- structured action request/response lifecycle;
- inbound response normalization;
- response timeout/follow-up state;
- exception/owner routing.

### COM-5 — Teacher availability / tomorrow readiness pilot
- read future Academic class-session obligations;
- generate teacher confirmation requests;
- `CONFIRMED / UNAVAILABLE / NO_RESPONSE` operational view;
- route `UNAVAILABLE/NO_RESPONSE` to authorized Academic follow-up;
- preserve existing substitution/swap/reschedule/cancellation authority.

### COM-6 — WhatsApp adapter
- backend secret configuration;
- template mapping;
- send adapter;
- verified webhook handling for delivery and approved structured replies/actions;
- canonical provider-state normalization;
- retry/error handling.

The exact ordering of COM-5 and COM-6 may be adjusted in implementation, but action-request contracts must exist before teacher confirmation is activated.

### COM-7 — Academic published-report delivery pilot
- published report eligibility contract;
- Guardian recipient preview;
- personalized batch generation;
- secure link/attachment policy;
- delivery dashboard;
- controlled parent pilot.

### COM-8 — Multi-domain expansion
- Tahfizh/Kesantrian/Administratif approved communications;
- additional institutional audiences;
- cross-domain parent/report communication where approved.

### COM-9 — AI-assisted communication drafting (optional)
AI may draft wording/summaries for an authorized human, but no autonomous schedule decision, sensitive publication or broadcast.

## Gate dependencies
Do not activate provider sending before:
- Shared Core recipient identity contracts are stable;
- RBAC/audit/queue foundation is stable;
- sender/audience permissions are approved;
- current external provider requirements have been verified;
- privacy/UAT test plan exists.

Do not activate teacher availability confirmation before:
- Academic schedule/session/teacher obligation structure is production-stable;
- response timing/reminder/escalation policy is approved;
- `UNAVAILABLE` handling ownership is approved;
- action-response normalization and idempotency tests pass.
