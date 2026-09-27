# Institutional Broadcast, Reminder & Action Request Architecture v1.0

**Status:** `DESIGN_READY_FUTURE` / implementation deferred.

## Purpose
Extend Shared Communication beyond parent-report delivery so the same platform can support internal institutional communications such as:
- Academic holiday/cancellation announcements;
- postponement or schedule-change notices;
- messages to all teachers, all asatidzah, masyayikh, drivers, Waka or other approved staff groups;
- scheduled reminders derived from official schedules/events;
- structured action requests that require a recipient response, such as asking a teacher whether they can teach the next day.

This is a **shared platform capability**, not an Academic-owned WhatsApp feature.

## Canonical message purposes
Shared Communication distinguishes at least three business-purpose types:

### `ANNOUNCEMENT`
One-way institutional information. No recipient response is required for completion.

Examples:
- "Academic activities tomorrow are suspended."
- "Departure time has moved to 06:30."
- "All asatidzah are requested to note the approved schedule change."

### `REMINDER`
A scheduled or event-triggered reminder derived from an existing official fact.

Examples:
- "You are scheduled to teach Fiqih X-A tomorrow at 08:00."
- "The Academic coordination meeting begins in one hour."

A reminder does not create the underlying schedule/event. The schedule/event remains Source of Truth.

### `ACTION_REQUEST`
A communication that expects a structured response and creates a tracked request lifecycle.

Examples:
- "Can you teach tomorrow's Fiqih X-A session?"
- future acknowledgements or operational confirmations where approved.

A reply must be normalized into structured data; free-text chat alone is not the official response state.

## Transaction-before-communication rule
Communication must not become a parallel Source of Truth.

Examples:

```text
Approved Academic Calendar change
        ↓
Academic Calendar event = NON_TEACHING_DAY / BLOCK
        ↓
Communication Announcement
        ↓
Teachers receive notification
```

Do **not** send a WhatsApp message saying "tomorrow is a holiday" while the Academic Calendar still says regular teaching occurs.

For schedule change:

```text
Approved Schedule Change
        ↓
Canonical session/schedule state updated
        ↓
Communication event emitted
        ↓
Affected teacher/Wali Kelas recipients resolved
        ↓
Message queued/sent
```

## Shared architecture

```text
Official Domain Fact / Approved Decision
              │
              ▼
       Communication Trigger
              │
              ▼
       Purpose Resolver
 Announcement / Reminder / Action Request
              │
              ▼
       Audience Resolver
              │
              ▼
Recipient Eligibility + Channel Resolution
              │
              ▼
Template + Context Snapshot
              │
              ▼
Outbound Message Queue
              │
              ▼
MessagingProvider
              │
              ▼
Delivery Events / Response Events
              │
              ▼
Audit + Exception / Follow-up Queue
```

## Ownership boundaries
- Source domains own the fact/event/decision that justifies a message.
- Shared Core owns canonical Staff/Guardian identities and organization context.
- Shared Communication owns audience resolution, groups, templates, outbound queue, delivery state, response normalization and communication audit.
- Source domains consume structured responses when they are relevant to their workflow.
- WhatsApp/provider APIs must never be called directly from Academic/Tahfizh/Kesantrian domain code.

## Core invariants
1. `ANNOUNCEMENT`, `REMINDER`, and `ACTION_REQUEST` are distinct purposes.
2. Communication never creates or silently mutates the underlying schedule, holiday, session, attendance, grade or other domain fact.
3. Recipient selection is resolved from canonical Staff/Organization/Role/Assignment data or approved manual groups; not from ad-hoc phone-number lists as the primary authority.
4. A recipient group may be dynamic/effective-dated and must be auditable.
5. A failed provider delivery does not invalidate the business event that generated the message.
6. Repeated scheduler/provider retries must be idempotent.
7. Action-request responses are structured facts with actor, request, response, channel and timestamp.
8. No response is an operational exception/state, not proof of refusal or absence.
9. Automatic reminders may be allowed only when their trigger is an approved Source-of-Truth fact and the message class is configured for auto-send.
10. Manual institutional broadcast authority is permission/scope-based; executive read-only roles do not automatically gain send rights.
11. AI may draft wording only when authorized; AI does not decide that an activity is cancelled, rescheduled or approved.
12. Current external provider requirements must be re-verified at implementation/go-live.

## Suggested future message/request objects
Physical schema is deferred, but future design should support these logical grains:

### `communication_announcements`
One approved communication intent/broadcast definition.

### `communication_reminders`
One reminder definition or generated reminder instance tied to an official source event.

### `communication_action_requests`
One business request requiring structured response.

### `communication_action_responses`
One recipient response to one action request.

### existing shared delivery objects
- `message_templates`
- `message_template_versions`
- `outbound_message_batches`
- `outbound_messages`
- `message_delivery_events`

These objects may be implemented with a generalized communication-intent model rather than separate tables if the resulting contract remains explicit and auditable. Codex must not choose a physical schema before the future implementation gate.

## Examples of supported future audience targets
- all active staff;
- all asatidzah;
- all masyayikh;
- all active Academic teachers;
- all Wali Kelas;
- all staff assigned to Tahfizh;
- all staff assigned to Kesantrian;
- all drivers;
- a specific Waka and members under that domain;
- an approved manual event committee group;
- exact affected recipients from a schedule/session change.

## Status
This document makes the future capability **architecturally available** but does not authorize implementation now. Current Academic MVP and Shared Core work remain independent of this capability.
