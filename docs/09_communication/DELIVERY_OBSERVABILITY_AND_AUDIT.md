# Delivery, Response Observability & Audit v1.1

**Status:** `DESIGN_LOCKED` future observability principles.

## Required operational visibility
For each batch/communication occurrence expose counts as applicable:
- resolved recipients;
- eligible;
- skipped + reason;
- queued;
- sent;
- delivered;
- read when supported/relevant;
- failed;
- retrying;
- permanently failed.

For `ACTION_REQUEST` additionally expose:
- response pending;
- confirmed/accepted response code(s);
- unavailable/declined response code(s) where applicable;
- needs follow-up;
- no response/expired;
- response received after escalation where relevant.

## Message-level audit
A historical message should be traceable to:
- batch/communication intent ID;
- message purpose;
- source module and source event/fact reference;
- recipient canonical identity;
- audience/group resolution reference;
- contact-channel snapshot/reference;
- student + artifact version for parent-specific messages where relevant;
- template/version;
- initiating/approving user(s);
- provider/channel;
- provider message reference;
- queue/send/delivery timestamps;
- delivery status history;
- failure/retry reason;
- correlation/idempotency key.

## Action-request response audit
Preserve:
- request ID;
- recipient/person;
- source business obligation/reference;
- response code;
- response timestamp;
- response channel/provider reference;
- optional note;
- follow-up/escalation state;
- resulting domain workflow reference if a human-approved change occurs.

## Delivery/response events
Prefer append-only event history so provider and response state transitions remain explainable. Provider state is transport evidence; domain state remains in the owning domain.

## Retry rules
- transient provider/network errors may retry according to configured policy;
- permanent eligibility/privacy/invalid-recipient failures are not blindly retried;
- retries must not create duplicate logical messages/requests;
- manual resend is a new auditable send action linked to the same source intent;
- scheduler reruns must not duplicate active teacher confirmation requests for the same configured cycle.

## Alerts
Communication failures/no-response states may create shared operational alerts or follow-up queues. They never alter the underlying report, schedule, attendance, teacher presence or student record.
