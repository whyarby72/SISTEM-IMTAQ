# Teacher Availability & Tomorrow Readiness Architecture v1.0

**Status:** `DESIGN_READY_FUTURE` / implementation deferred.

## Purpose
Support a future workflow that asks scheduled teachers whether they can fulfill their upcoming teaching obligation, without confusing a communication response with official teacher attendance.

Example future use:
> "Tomorrow you are scheduled to teach Fiqih X-A at 08:00–09:30. Can you teach?"

## Critical distinction

```text
Teaching Schedule / Session Obligation
          ≠
Teacher Availability Confirmation
          ≠
Teacher Attendance
```

A teacher may confirm availability and later be unable to attend; therefore `CONFIRMED` is not `PRESENT`.
A teacher may not respond and still teach; therefore `NO_RESPONSE` is not `ABSENT`.

## Canonical grain
Recommended logical grain:

`1 Teacher × 1 Future Class Session/Obligation × 1 Confirmation Cycle`

The physical schema is deferred.

## Candidate lifecycle
- `PENDING`
- `CONFIRMED`
- `UNAVAILABLE`
- `NEEDS_FOLLOW_UP`
- `EXPIRED` / `NO_RESPONSE`

Final vocabulary and timeout policy are `POLICY_PENDING`.

## Future flow

```text
Future Class Session
      ↓
Expected PRIMARY teacher obligation
      ↓
Configured confirmation window reached
      ↓
ACTION_REQUEST created
      ↓
Teacher receives WhatsApp/In-App request
      ↓
Structured response
  ┌─────────────┬───────────────┐
  ▼             ▼               ▼
CONFIRMED   UNAVAILABLE   NEEDS_FOLLOW_UP / no response
  │             │               │
  │             ▼               ▼
  │        Academic exception   Academic follow-up queue
  │             │
  │     Human operational decision
  │      ┌──────┼───────┬───────────┐
  │      ▼      ▼       ▼           ▼
  │  Substitute Swap Reschedule Cancellation
  │      │      │       │           │
  └──────┴──────┴───────┴───────────┘
             existing Academic schedule-change workflow
```

## Response rule
Provider chat text should be normalized into structured response codes. Example UI/provider actions may map to:
- `CONFIRMED`
- `UNAVAILABLE`
- `NEEDS_FOLLOW_UP`

Free-text notes may supplement the response but are not the sole state.

## No automatic schedule mutation
`UNAVAILABLE` must never automatically:
- mark the teacher ABSENT;
- substitute another teacher;
- cancel the session;
- reschedule the class.

It creates an operational exception for the authorized Academic process. Existing `SUBSTITUTION`, `SWAP`, `RESCHEDULE`, and `CANCELLATION` workflows remain authoritative.

## Tomorrow Readiness view
A future Academic operational dashboard may show counts such as:
- sessions/teacher obligations tomorrow;
- `CONFIRMED`;
- `UNAVAILABLE`;
- `NO_RESPONSE` / pending;
- exceptions requiring substitution/reschedule decision.

This is an operational readiness view, not a teacher-performance score.

## Owner and permissions
Likely future consumers:
- Academic Admin/PIC for monitoring;
- Waka Academic for domain oversight;
- teacher recipient for own request response.

Exact sender, schedule, reminder cadence, escalation and response deadline remain `POLICY_PENDING`.

## Idempotency
Scheduled confirmation generation must be idempotent. Re-running a scheduler for the same obligation/cycle must not create duplicate active confirmation requests.

## Audit
At minimum preserve:
- source session/teacher obligation;
- request created/sent times;
- recipient/channel;
- response code/time/channel;
- relevant note;
- follow-up/escalation actions;
- link to schedule-change transaction if an exception results in an approved change.

## AI
AI is not required. If used later, AI may draft text or summarize pending responses but may not decide availability, fabricate a response, or autonomously apply schedule changes.
