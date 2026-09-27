# Parent Portal Architecture v1.0

**Status:** `DESIGN_READY_FUTURE`  
**Implementation:** deferred until Guardian identity/account, RBAC/audit, and at least one stable published parent-facing artifact are available.

Parent Portal is a **shared guardian-facing application surface**, not a student-development domain and not a Source of Truth. It provides authenticated Guardians access only to parent-approved published artifacts and future explicitly approved parent-facing services.

## Authoritative documents
1. `PARENT_PORTAL_ARCHITECTURE.md`
2. `GUARDIAN_ACCOUNT_LINKING.md`
3. `PARENT_PORTAL_AUTHORIZATION.md`
4. `PUBLISHED_CONTENT_ACCESS.md`
5. `SECURE_LINK_AND_SESSION_SECURITY.md`
6. `PARENT_PORTAL_PRIVACY_SECURITY.md`
7. `PARENT_PORTAL_OBSERVABILITY_AUDIT.md`
8. `PARENT_PORTAL_UAT_MATRIX.md`
9. `PARENT_PORTAL_ROADMAP.md`

## Required upstream contracts
- Shared Core Guardian/Student identities: `docs/10_shared_core/`
- Source-domain published artifact/versioning: Academic P6/P7 initially
- Shared Authentication/RBAC/Audit: Shared Platform
- Shared Communication secure-link delivery: `docs/09_communication/`

## Core principle
`Authenticated User → Verified Guardian Link → Authorized Student Relationship → Exact Parent-Approved Published Artifact → View/Download according to policy`

A phone number, WhatsApp possession, name match, or guessed URL never grants access by itself.
