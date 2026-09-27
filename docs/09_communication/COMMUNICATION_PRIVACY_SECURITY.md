# Communication Privacy & Security v1.1

**Status:** `DESIGN_LOCKED` principles / implementation policies future.

## Privacy principles
- Minimum necessary content per message.
- Recipient authorization/eligibility before rendering/sending protected content.
- Do not expose internal coaching, disciplinary, sensitive Kepengasuhan or audit notes by default.
- Destination contact data is personal data and must be protected and access-controlled.
- Staff/internal broadcast scope must be no broader than the approved audience.
- A communication role does not automatically grant access to raw domain data used to derive the message.

## High-risk controls
1. Preview recipient count, audience and exclusions before manual bulk send.
2. Prevent cross-student attachment/link substitution in parent delivery.
3. Bind each parent delivery to exact student + artifact version.
4. Use secure/authenticated links for sensitive reports where adopted.
5. Do not log provider secrets or unnecessary message content.
6. Restrict batch initiation, approval, audience management, resend and export independently.
7. Webhook endpoints require provider verification and replay protection.
8. Bulk send jobs must be rate/retry controlled and observable.
9. Action-request inbound responses must be mapped to the authenticated/verified provider recipient context and the exact open request.
10. `NO_RESPONSE` or provider failure must never be interpreted as teacher absence/refusal without a separate authoritative process.
11. Executive read-only roles remain unable to send/modify communication unless a separate explicit permission is approved.

## Parent-facing boundary
`approved_for_parent_report` (or equivalent approved contract) is required for content, but does not itself prove that every communication channel/recipient is eligible. Content approval and recipient/channel eligibility are distinct controls.

## Internal audience boundary
Dynamic audiences such as `ACADEMIC_TEACHERS` or `DRIVERS` must resolve from canonical effective institutional assignments or audited group membership. Provider-side group membership alone is not the Source of Truth.
