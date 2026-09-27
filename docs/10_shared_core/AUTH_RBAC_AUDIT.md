# Shared Authentication, RBAC & Audit Integration v1.0

**Boundary:** operationally part of Shared Platform, specified here because every Shared Core/domain command depends on it.

## 1. Authentication
- one shared authentication system for SISTEM IMTAQ.
- `users` identifies application accounts, not Students/Staff/Guardians themselves.
- inactive/disabled accounts cannot authenticate.
- credentials/secrets are never stored in business audit payloads or repository documentation.

## 2. Identity links
MVP staff users should use an explicit relational link between `users` and `staff` rather than copying Staff data into users.

Future Parent Portal links a User to Guardian via a separate explicit relation without changing Guardian identity.

## 3. RBAC model

`USER → EFFECTIVE ROLE ASSIGNMENT → PERMISSION → SCOPE RESOLVER → RESOURCE STATE`

No `users.role` string.

Roles/permissions are catalogs; user-role assignments are effective-dated.

## 4. Scope
Shared scope vocabulary includes:
- `SELF`
- `ASSIGNED_SESSION`
- `ASSIGNED_CLASS`
- `HOMEROOM_CLASS`
- `DEPARTMENT`
- `UNIT`
- `INSTITUTION`

Future domains may extend through reviewed contracts.

Dynamic scope derives from effective business assignments. Example: Wali Kelas access comes from `class_homeroom_assignments`, not a permanent class ID copied into RBAC.

## 5. Core permission examples
Suggested names:
- `core.student.view`
- `core.student.create`
- `core.student.update_identity`
- `core.student_identifier.view`
- `core.student_identifier.add`
- `core.student_identifier.correct`
- `core.student_status.change`
- `core.guardian.view`
- `core.guardian.manage`
- `core.guardian_relationship.manage`
- `core.guardian_contact.manage`
- `core.staff.view`
- `core.staff.manage`
- `core.organization.manage`
- `platform.rbac.manage`
- `platform.audit.view`

Field-level authorization still applies; a user who can view Student name does not automatically view NISN/guardian contacts.

## 6. Super Admin authority and workflow
`SUPER_ADMIN` has full institution-wide authority across enabled domains, including Shared Core business and platform operations. This authority is not an automatic approval result: identifier correction, student dismissal, report publication, guardian authorization and other high-value actions still require the applicable permission, scope, resource state, workflow, audit and versioning controls.

## 7. Audit
High-value events include:
- student creation/identity correction;
- NIS/NISN add/correction/supersession;
- lifecycle change;
- guardian creation/link/end relationship;
- guardian contact correction/verification;
- staff/organization changes;
- role grant/revoke;
- post-lock correction;
- import/migration of Core master data.

Audit is append-only evidence and not a mutable business fact store.

## 8. AI-assisted actions
If future AI proposes a Core action, the actual domain command still records the authenticated human actor and `source_channel=AI_ASSISTED`. AI cannot approve its own high-risk action.

## 9. Communication
Shared Communication receives only guardian/contact/authorization data that the caller is allowed to resolve. It does not gain blanket access to all Student/Guardian data.

## 10. Organizational hierarchy and executive read-only
Current system model distinguishes:
- `SUPER_ADMIN` — institution-wide business and platform authority, subject to resource workflow;
- `ADMIN` — cross-domain operational administration according to explicit permissions;
- domain Waka: `WAKA_AKADEMIK`, `WAKA_TAHFIZH`, `WAKA_KESANTRIAN`;
- domain members under the relevant Waka;
- executive read-only: `KEPALA_UNIT`, `IDAROH`, `YAYASAN`.

Organizational seniority does not imply write-permission inheritance. `WAKA_AKADEMIK` is full authority within Academic; executive roles remain read-only by default and consume authorized report/dashboard views rather than directly editing domain facts.

## 11. AI access
AI access is a separate Shared Platform permission family. Initial rollout grants AI Assistant/READ only to `SUPER_ADMIN`; all other roles are deny-by-default. AI capability is intersected with ordinary business permission, data scope and resource state. See `docs/08_ai/AI_RBAC_ACCESS_POLICY.md`.
