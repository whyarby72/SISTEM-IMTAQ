# Parent Portal Architecture v1.0

## 1. Purpose
Provide a secure self-service channel for authorized Guardians to access parent-facing information about their own children without exposing internal IMTAQ transactions, notes, alerts, or data belonging to another Student.

Parent Portal is a **derived consumer/application surface**. It must not become a parallel master, grade database, attendance database, or report authoring system.

## 2. Position in SISTEM IMTAQ

```text
Shared Core
Student / Guardian / Relationship
          │
          ▼
Shared Platform
User / Auth / RBAC / Audit
          │
          ▼
Source Domains
Academic / Tahfizh / ...
validated facts → reviewed/published parent artifact
          │
          ▼
Parent Portal
read authorized published content
          │
          ├── Browser / mobile web
          └── deep link from Shared Communication
```

## 3. MVP boundary
Parent Portal v1 is **read-only for published parent-facing artifacts**.

MVP may provide:
- Guardian sign-in/account activation;
- list of Students the Guardian is currently authorized to access;
- list of published parent-facing Academic artifacts for that Student;
- view exact report version;
- secure PDF/rendered artifact access if policy allows;
- account/session security and access audit.

MVP does **not** provide:
- editing Student master data;
- editing Guardian relationship/contact directly;
- entering attendance/grades;
- viewing internal Academic DQ/alerts;
- viewing internal discipline/kepengasuhan notes;
- sending messages to staff;
- filing permission requests;
- parent-facing AI chat;
- cross-domain Student 360.

Those are independent future capabilities requiring their own contracts/policies.

## 4. Source-of-truth boundaries
- `students`, `guardians`, Student↔Guardian relationship: Shared Core.
- `users`, authentication, roles/permissions: Shared Platform.
- Academic report/Transcript data: Academic owns source facts and publication artifact.
- Communication delivery: Shared Communication.
- Parent Portal owns only portal-specific account-link/access/navigation metadata and read-access observability.

## 5. No data duplication rule
The Portal must not copy grades, attendance facts or sensitive notes into portal-owned tables as a second Source of Truth. It resolves parent-approved content through explicit contracts.

## 6. Canonical access chain

```text
Authenticated user
   ↓
ACTIVE Guardian↔User link
   ↓
Guardian identity
   ↓
Eligible Student↔Guardian relationship
   ↓
Parent-access authorization rule
   ↓
Exact requested artifact belongs to that Student
   ↓
artifact = PUBLISHED
   ↓
artifact approved for parent access
   ↓
requested action allowed (VIEW / DOWNLOAD)
```

Every request re-evaluates authorization server-side. UI child lists are convenience, not security boundaries.

## 7. Multi-child and multi-guardian support
One Guardian may access multiple Students when each relationship is independently authorized. One Student may have multiple Guardians. Access is Student-specific; authorization for Student A never implies authorization for Student B.

Recipient policy for multiple Guardians and historical access after relationship end remain `POLICY_PENDING`.

## 8. Application structure recommendation
Inside the modular monolith:

```text
app/
  Shared/
    ParentPortal/
      Application/
      Domain/
      Infrastructure/
      Http/
```

Exact Laravel folder naming is an implementation choice, but ownership/boundaries are `DESIGN_LOCKED`.

## 9. Public contracts consumed
- `GuardianRecipientContext` / Guardian relationship contract from Shared Core.
- `AuthorizationContract` from Shared Platform.
- `ParentPortalArtifactContract` from source reporting services.
- `CommunicationEligibilityContract` and deep-link integration from Shared Communication when enabled.

## 10. AI future
A future parent-facing AI assistant may only use the same parent-visible data available to the Guardian in the Portal, must be read-only first, and must not access internal notes/alerts or other children. It is not part of Parent Portal v1.

## 11. Availability rule
Parent Portal outage must not affect Academic/Tahfizh/Kesantrian transaction capture or report publication. Portal is a downstream access channel.
