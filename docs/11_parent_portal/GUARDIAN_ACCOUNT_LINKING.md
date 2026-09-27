# Guardian Account Linking v1.0

## 1. Fundamental distinction

```text
Guardian Person
≠ User Account
≠ Student↔Guardian Relationship
≠ Contact Channel
≠ Communication Consent
```

`guardians` is the canonical person. `users` is the authentication identity. Parent Portal needs an audited bridge between the two.

## 2. Proposed portal-owned bridge
Conceptual table:

`guardian_user_account_links`
- `id UUID`
- `guardian_id FK`
- `user_id FK`
- `link_status`: `PENDING | ACTIVE | SUSPENDED | ENDED`
- `identity_verification_status`: `UNVERIFIED | VERIFIED`
- `verification_method_code nullable`
- `valid_from`
- `valid_until nullable`
- `linked_by nullable`
- `linked_at`
- `ended_by nullable`
- `ended_at nullable`
- `reason nullable`
- audit/version metadata

MVP invariant: a portal user account must not be shared by multiple Guardian persons, and a Guardian should not have multiple simultaneously active portal accounts unless architecture is explicitly revised.

## 3. Account activation
Activation requires evidence linking the authenticating person to the canonical Guardian. A verified phone/email may participate in verification but **possession of a contact channel alone is not sufficient authorization to a Student**.

Possible activation methods include invitation + one-time verification, administrative verification, or another approved identity flow. Exact verification method is `POLICY_PENDING`.

## 4. Invitation concept
Optional conceptual table:

`parent_portal_account_invitations`
- `id UUID`
- `guardian_id`
- `contact_channel_id nullable`
- `token_hash` (never plain token)
- `expires_at`
- `status`: `ISSUED | CONSUMED | EXPIRED | REVOKED`
- `issued_by`, `issued_at`
- `consumed_at nullable`

An invitation enables account onboarding; it does not grant Student access unless the relationship/access checks also pass.

## 5. Guardian changes
Changing a Guardian phone/email must not create a new Guardian or silently relink the portal account. Contact correction follows Shared Core governance. Portal account linkage remains auditable.

## 6. Relationship changes
If a Student↔Guardian relationship ends, child access must be re-evaluated immediately. Whether a former authorized Guardian retains access to older historical artifacts is `POLICY_PENDING`; do not assume permanent access.

## 7. Account suspension
Support must be able to suspend Portal access without deleting Guardian master data or Student relationships. Suspended account login/access is denied and audited.

## 8. No direct self-service mutation in MVP
Parent Portal v1 does not let a Guardian directly overwrite verified Guardian identity/contact/relationship fields. Future self-service may submit a change request to the responsible master-data workflow.
