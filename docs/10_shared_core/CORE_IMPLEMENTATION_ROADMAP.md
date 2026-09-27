# Shared Core Implementation Roadmap v1.0

This roadmap refines existing Sprint 0–1; it does not authorize coding until the user says start.

## Phase C0 — Repository/Foundation
Existing Sprint 0:
- Laravel/PostgreSQL app scaffold.
- module conventions.
- test/quality/CI baseline.

## Phase C1 — Organization / Staff / Platform identity
- organizational units.
- locations.
- Staff canonical identity.
- Staff organizational assignments.
- User↔Staff linkage.

## Phase C2 — Student identity
- students + permanent student code.
- student identifiers.
- identifier correction/version/audit.
- status history + effective-date resolver.
- optional cohort master if source requires it.

## Phase C3 — Authentication / RBAC / Audit
- roles/permissions.
- effective role assignments.
- scope resolver interfaces.
- append-only audit.
- correction-request foundation.

## Phase C4 — Guardian foundation
- guardians.
- student_guardian_relationships.
- guardian_contact_channels.
- recipient-context read contract.

Channel-specific communication consent remains future Shared Communication work.

## Phase C5 — Shared Core contracts & DQ
- StudentIdentityContract.
- StudentEligibilityContract.
- StaffIdentityContract.
- GuardianRecipientContextContract.
- OrganizationContextContract.
- Core DQ queries/alerts integration points.
- cross-module contract tests.

## Definition of Done — Shared Core foundation
- no duplicate module Student/Staff/Guardian masters.
- critical DB constraints automated.
- high-value mutations use explicit commands.
- backend RBAC/field minimization tested.
- audit evidence passes P0 tests.
- effective dating tested.
- migration/idempotency path defined.
- Shared Core contracts documented and consumer-safe.
- Guardian relation/contact foundation supports future Parent Portal/WhatsApp without exposing consent as a Core fact.

## Policy pending that does not block structural implementation
- exact Staff/Organization master approver roles.
- exact Guardian recipient strategy when multiple guardians exist.
- detailed guardian identity proof/verification SOP.
- institutional re-entry policy for former students.
- exact organization/title vocabulary.

Codex must keep these configurable/disabled where policy-dependent.
