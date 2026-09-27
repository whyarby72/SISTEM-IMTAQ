# Guardian, Contact & Consent Contract v1.0

**Status:** Guardian identity/relationship/contact foundation is `DESIGN_READY` in Shared Core; communication consent/preferences and delivery policy are `DESIGN_READY_FUTURE` / `POLICY_PENDING`.

**Authority:** canonical Guardian/relationship/contact schema and lifecycle are defined in `docs/10_shared_core/GUARDIAN_MASTER.md` and `CORE_POSTGRESQL_CONTRACT.md`. This file owns communication-specific consent/preference eligibility only.

## Why this exists
Personalized parent communication is unsafe unless the system has a canonical relationship:

`Student_ID → Authorized Guardian → Verified Contact Channel → Communication Consent/Preference`

Names or phone numbers typed ad hoc into a broadcast screen are not a reliable recipient registry.

## Shared Core entities consumed by Communication

### `guardians`
Canonical guardian/person identity. Do not duplicate guardian records per domain.

Suggested concepts:
- `guardian_id`
- name and relevant identity/contact metadata according to privacy policy
- active/inactive lifecycle
- audit metadata

### `student_guardian_relationships`
Grain: one Guardian × one Student × one effective relationship period.

Suggested concepts:
- `student_id`
- `guardian_id`
- relationship type
- effective from/until
- `authorized_for_parent_reports`
- `is_primary_contact` or future delivery-priority policy
- verification/approval metadata

Do not hard-code that only father or only mother may receive reports.

### `guardian_contact_channels`
Grain: one Guardian × one contact channel identity/version.

Suggested concepts:
- channel type (`WHATSAPP`, future `EMAIL`, etc.)
- normalized destination
- verification status
- effective from/until
- source/reference
- audit metadata

A changed phone number must not rewrite historical delivery evidence.

## Communication-owned entities

### `communication_consents`
Capture consent/opt-in/opt-out evidence when required by institution/provider/channel policy.

Suggested concepts:
- guardian
- channel
- communication purpose/category
- status
- captured_at
- source/evidence
- revoked_at

### `communication_preferences`
Future configurable preferences, e.g. preferred channel or recipient routing. Preferences never override legal/institutional authorization or privacy restrictions.

## Recipient eligibility contract
A recipient is eligible only when all required conditions are true, e.g.:
1. student and guardian relationship is effective;
2. guardian is authorized for the report/content class;
3. target channel is valid/active/verified according to policy;
4. required consent is present and not revoked;
5. source artifact is eligible for parent delivery;
6. user initiating the batch has permission to initiate delivery for the scope.

The exact consent/legal/provider rules are implementation-time policy inputs and must not be guessed by Codex.

## One guardian, multiple students
Each child remains a separate delivery fact by default. A future consolidated family digest requires its own approved design.
