# Secure Link & Session Security v1.0

## 1. WhatsApp/email links are navigation aids, not authorization
A message may contain a deep link to an artifact, but possession of that URL does not bypass authentication or Guardian↔Student authorization.

Canonical flow:

```text
WhatsApp notification
  ↓
opaque/signed deep link
  ↓
Parent Portal
  ↓
if unauthenticated: authenticate
  ↓
re-run full Guardian/Student/artifact authorization
  ↓
show exact artifact
```

## 2. URL design
Do not put NISN, phone number, sensitive names, or predictable sequential identifiers in public URLs. Prefer opaque UUIDs or short-lived signed/hashed references.

Exact signed-link implementation is a technical `DESIGN_ASSUMPTION`; security properties are mandatory.

## 3. Optional deep-link record
If persisted tokens are used, store only token hashes. Conceptual fields:
- `id`
- `token_hash`
- `target_artifact_version_id`
- `intended_guardian_id nullable`
- `expires_at`
- `revoked_at nullable`
- `created_at`

A deep-link token may identify navigation target but must not be accepted as sole authorization for sensitive content.

## 4. Session security baseline
Implementation must include:
- HTTPS only in production;
- secure/HttpOnly/SameSite cookies as appropriate;
- CSRF protection;
- login/verification rate limits;
- account lock/suspicious-auth controls;
- secure password reset/account recovery;
- session invalidation after account suspension;
- audit/security logging for repeated denied access.

Exact session timeout and MFA requirement are `POLICY_PENDING`.

## 5. Shared devices
The Portal should support explicit logout and must not cache sensitive report content in unsafe public/shared-browser contexts beyond normal secure web practices. Do not expose full reports in notification previews.

## 6. Document links
If PDF/storage uses pre-signed URLs, generate them only after authorization and keep them short-lived. Never expose permanent public object-storage URLs for parent reports.
