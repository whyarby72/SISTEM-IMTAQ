# Shared Communication Architecture v1.1

**Status:** `DESIGN_LOCKED` architecture / `DEFERRED_FUTURE` implementation.

## Purpose
Provide one shared, auditable communication layer for SISTEM IMTAQ. The platform supports two major future communication families:

1. **Parent/Guardian delivery** — personalized published reports and approved parent-facing information.
2. **Institutional/internal communication** — announcements, reminders and structured action requests for staff/operational audiences such as teachers, Wali Kelas, masyayikh, asatidzah, drivers and domain teams.

WhatsApp is the first planned external channel, but the platform remains provider-neutral.

## Architectural position

```text
Domain Sources of Truth / Approved Institutional Facts
Academic / Tahfizh / Kesantrian / Shared Core / Other Domains
                         │
                         ▼
               Communication Trigger
                         │
               ┌─────────┴──────────┐
               ▼                    ▼
      Parent/Guardian Flow     Internal Staff Flow
 Published parent artifact    Announcement / Reminder /
                              Action Request
               │                    │
               └─────────┬──────────┘
                         ▼
              Recipient/Audience Resolver
                         │
       Identity / Relationship / Role / Assignment /
            Consent / Preference / Scope checks
                         │
                         ▼
               Communication Orchestrator
                         │
              ┌──────────┴───────────┐
              ▼                      ▼
       Message Template       Secure Link / Context
              │
              ▼
       Outbound Message Queue
              │
              ▼
        MessagingProvider
              │
        ┌─────┴────────────┐
        ▼                  ▼
 WhatsAppProvider      Future Providers
        │
        ▼
 Delivery / Response Webhooks
        │
        ▼
Delivery Status + Structured Response + Audit + Exception Queue
```

## Ownership boundaries
- Source domains own report/fact content, calendar/schedule/session states and other business truth.
- Shared Core owns canonical Student/Guardian/Staff/Organization identity context.
- Shared Communication owns recipient/audience resolution, communication groups, templates, outbound queue, provider adapters, delivery events, action-response normalization and communication audit.
- Source domains may consume structured communication responses through explicit contracts, but communication does not write arbitrary domain tables.
- Messaging providers do not own institutional facts.
- A domain must never call WhatsApp/provider APIs directly.

## Communication-purpose model

### `ANNOUNCEMENT`
One-way information; no structured response required.

### `REMINDER`
Time/event-based reminder derived from an existing official fact.

### `ACTION_REQUEST`
Tracked communication expecting a structured response. The response becomes a dedicated response fact and may trigger a human-managed operational exception.

Parent published-report delivery remains a specialized personalized outbound flow and may be represented as a report-delivery purpose/template class.

## Core invariants
1. Communication is never a Source of Truth for Academic/Tahfizh/Kesantrian business facts.
2. Business event/decision first, communication second.
3. Only approved/published parent-facing content is eligible for parent delivery.
4. One personalized parent message resolves to the exact Student, authorized recipient and exact published artifact/version.
5. Internal recipients are resolved from canonical Staff/Organization/Role/Assignment data or audited managed groups, not uncontrolled phone lists.
6. Provider outage never blocks normal domain transactions, schedule decisions or report publication.
7. Communication sends through background jobs/queues, not long synchronous web requests.
8. Every send attempt is auditable and idempotent.
9. Re-sending creates a new auditable send action; it does not rewrite historical delivery evidence.
10. Contact/consent/channel eligibility is evaluated at send time where relevant and recorded sufficiently for audit.
11. Action-request responses are structured and auditable; `NO_RESPONSE` is never automatically converted to refusal/absence.
12. Teacher availability confirmation is not teacher attendance and must not auto-create ABSENT/PRESENT.
13. Manual broadcast authority is permission/scope-based; organizational seniority alone does not grant send rights.
14. Executive read-only roles remain read-only unless a separate communication permission is explicitly approved.
15. AI may draft message wording only under AI/RBAC policy; AI never decides that an activity is cancelled/rescheduled or autonomously sends sensitive broadcasts.
16. External provider policy requirements must be re-verified at implementation/go-live.

## Canonical logical grains

### Parent personalized delivery
`Recipient × Student × Published Artifact Version × Channel`

### Internal announcement/reminder delivery
`Recipient × Communication Intent/Occurrence × Channel`

### Action request
`Recipient × Business Request × Response Cycle`

## Suggested shared tables/objects (future)
Existing/planned:
- `message_templates`
- `message_template_versions`
- `outbound_message_batches`
- `outbound_messages`
- `message_delivery_events`
- optional `secure_delivery_links`

Additional future concepts:
- communication audience/group registry;
- communication intents/announcements/reminders;
- action requests;
- action responses;
- response/delivery event lineage.

Exact physical schema remains a future implementation decision after the applicable Decision Gate.

## Authoritative supporting documents
- `INSTITUTIONAL_BROADCAST_REMINDER_ACTION_REQUEST.md`
- `AUDIENCE_AND_GROUP_REGISTRY.md`
- `TEACHER_AVAILABILITY_CONFIRMATION.md`
- `GUARDIAN_CONTACT_AND_CONSENT.md`
- `PARENT_REPORT_DELIVERY_WORKFLOW.md`
- `MESSAGE_TEMPLATE_POLICY.md`
- `WHATSAPP_PROVIDER_ARCHITECTURE.md`
- `DELIVERY_OBSERVABILITY_AND_AUDIT.md`
- `COMMUNICATION_PRIVACY_SECURITY.md`
- `COMMUNICATION_UAT_MATRIX.md`
- `COMMUNICATION_ROADMAP.md`
