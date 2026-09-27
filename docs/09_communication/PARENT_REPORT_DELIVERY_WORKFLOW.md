# Parent Report Delivery Workflow v1.0

**Status:** `DESIGN_LOCKED` future workflow skeleton.

## Canonical flow

```text
SOURCE DOMAIN FACTS
      ↓
REPORT / PARENT ARTIFACT DRAFT
      ↓
REVIEW / APPROVAL
      ↓
PUBLISHED
      ↓
approved_for_parent_report = TRUE (or equivalent policy)
      ↓
BUILD DELIVERY ELIGIBILITY SET
      ↓
PREVIEW / BATCH REVIEW
      ↓
QUEUE PERSONALIZED MESSAGES
      ↓
PROVIDER SEND
      ↓
SENT / DELIVERED / READ / FAILED / SKIPPED
      ↓
EXCEPTION HANDLING + AUDIT
```

## Important separations
- Report publication and WhatsApp delivery are separate processes.
- Failed WhatsApp delivery does not unpublish the report.
- A report may be re-sent without creating a new report version.
- A corrected report creates a new report version according to the source report workflow; any new send must reference the new exact version.

## Suggested batch lifecycle
`DRAFT → PREVIEWED → APPROVED_FOR_SEND → QUEUED → PROCESSING → COMPLETED_WITH_RESULT`

A batch may complete with mixed row outcomes; it should not claim global success while some recipients are failed/skipped.

## Suggested message lifecycle
Provider-neutral canonical statuses may include:
- `PREPARED`
- `QUEUED`
- `SENT`
- `DELIVERED`
- `READ` when supported
- `FAILED`
- `SKIPPED`
- `CANCELLED`

Provider-specific statuses are mapped into canonical states while retaining raw provider event metadata when safe/necessary for audit.

## Eligibility failure examples
- report not published;
- report/version not approved for parent delivery;
- no authorized guardian;
- no eligible WhatsApp channel;
- consent/preference requirement not satisfied;
- invalid relationship at send time;
- duplicate active delivery prevented by idempotency rule.

## Secure-link delivery option
For sensitive/full reports, prefer a minimal message plus a secure report link or authenticated Parent Portal path rather than copying all sensitive content into the messaging channel. The exact security model is a future Parent Portal/privacy decision.

## AI rule
AI may draft message wording only after the underlying structured facts exist. AI must not publish a report or trigger a sensitive broadcast autonomously. Human/authorized workflow remains the authority.

## Parent Portal integration
When Parent Portal is available, sensitive/full report delivery should preferentially send a minimal notification plus a secure Portal deep link. The link is navigation only: Parent Portal must authenticate and re-authorize `User → Guardian → Student → exact published artifact`. Authoritative Portal design: `docs/11_parent_portal/`.
