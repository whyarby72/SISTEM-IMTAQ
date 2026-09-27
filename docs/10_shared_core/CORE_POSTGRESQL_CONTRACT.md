# Shared Core PostgreSQL Contract v1.0

This document supplements the Academic PostgreSQL specification. It defines shared tables needed before multiple domains expand.

## Technical conventions
- PostgreSQL.
- UUID PK (`gen_random_uuid()`).
- `timestamptz` for actual timestamps.
- effective intervals use `[from, until)`.
- `ON DELETE RESTRICT` for business relationships unless explicitly justified.
- historical business facts are not destructively deleted.
- controlled vocabularies use VARCHAR + CHECK/configuration instead of hard-to-evolve PG ENUM by default.
- unknown is NULL/no row.

## 1. `organizational_units`
Suggested fields:
- `id uuid pk`
- `unit_code varchar unique not null`
- `unit_name varchar not null`
- `unit_type varchar not null`
- `parent_unit_id uuid null references organizational_units(id)`
- `effective_from date null`
- `effective_until date null`
- `record_status varchar not null`
- timestamps/audit metadata

Rules:
- no self-parent.
- no hierarchy cycle (service validation + integrity test).
- closed unit remains referenceable by historical assignments.

## 2. `locations`
- `id`
- `location_code unique`
- `location_name`
- `location_type`
- `organizational_unit_id nullable`
- `address_text nullable`
- `effective_from/effective_until nullable`
- `record_status`

## 3. `cohorts` (recommended shared master)
- `id`
- `cohort_code unique`
- `label`
- `entry_year`
- `organizational_unit_id nullable`
- `record_status`

This is optional for the first runnable migration if current source data does not need a cohort object, but the schema contract reserves it as a canonical shared concept rather than duplicating cohort labels per domain.

## 4. `students`
- `id uuid pk`
- `student_code varchar unique not null`
- `full_name varchar not null`
- `arabic_name varchar null`
- `nickname varchar null`
- `gender_code varchar null`
- `birth_place varchar null`
- `birth_date date null`
- `entry_year smallint null`
- `cohort_id uuid null references cohorts(id)`
- `created_by uuid null`
- `created_at timestamptz not null`
- `updated_by uuid null`
- `updated_at timestamptz not null`
- `version_no integer not null default 1`

No current class/status/halaqah/room columns.

## 5. `student_identifiers`
As already specified by Academic P1, with additional recommended columns:
- `identifier_scope` nullable for institutional NIS uniqueness scope if needed.
- `valid_from`, `valid_until`.
- `version_no` if mutable metadata is corrected.

Constraints:
- one current ACTIVE NISN per student.
- active NISN value unique globally.
- one current ACTIVE identifier per student/type/scope.
- placeholder strings prohibited by application/import validation.

## 6. `student_status_history`
- `id`
- `student_id`
- `status`
- `effective_from`
- `effective_until nullable`
- `decision_reference nullable`
- `reason nullable`
- actor/timestamps

Temporal overlap prevention should use exclusion constraint where practical.

## 7. `guardians`
- `id uuid pk`
- `guardian_code varchar unique not null`
- `full_name varchar not null`
- `record_status varchar not null default 'ACTIVE'`
- `created_by/created_at/updated_by/updated_at`
- `version_no`

Do not store relationship-to-student or primary phone directly here as canonical relationship/contact facts.

## 8. `student_guardian_relationships`
- `id`
- `student_id fk`
- `guardian_id fk`
- `relationship_type`
- `effective_from date null`
- `effective_until date null`
- `relationship_status`
- `communication_priority smallint null`
- `authorized_for_parent_reports boolean not null default false`
- `source_reference nullable`
- `notes nullable restricted`
- audit timestamps/version

Recommended uniqueness prevents duplicate active same relationship rows for same student/guardian.

## 9. `guardian_contact_channels`
- `id`
- `guardian_id fk`
- `channel_type`
- `normalized_value`
- `display_value nullable`
- `verification_status`
- `is_primary boolean`
- `active_from/active_until`
- `verified_by nullable`
- `verified_at nullable`
- `source_reference nullable`
- timestamps/version

Recommended:
- at most one active primary channel per guardian/type.
- normalized phone/WhatsApp uses E.164 when implemented.
- consent is **not** stored here; Communication owns consent/preference records.

## 10. `guardian_addresses` (optional MVP/shared future)
Separate effective-dated address records if operationally required. Do not block Academic MVP if address migration is not yet needed.

## 11. `staff`
- `id`
- `staff_code unique not null`
- `full_name not null`
- `record_status`
- `active_from/active_until nullable`
- timestamps/version

A staff row is not a user account and does not contain domain permission roles.

## 12. `staff_organizational_assignments`
- `id`
- `staff_id`
- `organizational_unit_id`
- `assignment_type`
- `is_primary`
- `effective_from`
- `effective_until`
- `source_reference nullable`
- timestamps/version

Prevent invalid overlapping primary assignments according to approved institutional rule; exact title vocabulary remains configuration/policy.

## 13. Shared Platform RBAC tables
Recommended minimum:
- `users`
- `roles`
- `permissions`
- `role_permissions`
- `user_role_assignments`
- account-to-staff link (`staff_user_accounts` or equivalent explicit relational mapping)

`user_role_assignments` should be effective-dated and include role context required for scope resolution. Domain dynamic scopes (e.g. `HOMEROOM_CLASS`) are resolved from business assignment tables rather than copied permanently into RBAC rows.

Future parent portal should add an explicit Guardian↔User account link without changing Guardian identity.

## 14. `audit_logs`
Append-only minimum:
- `id`
- `occurred_at`
- `actor_user_id nullable`
- `actor_type`
- `action`
- `entity_type`
- `entity_id`
- `version_before nullable`
- `version_after nullable`
- `old_values jsonb nullable`
- `new_values jsonb nullable`
- `reason nullable`
- `source_channel` (`WEB/API/AI_ASSISTED/IMPORT/SYSTEM` candidate)
- `correlation_id/request_id nullable`
- `correction_request_id nullable`
- technical request metadata only where privacy/security policy allows

Never write passwords, API keys, tokens or sensitive secrets into audit payloads.

## 15. `correction_requests`
Shared Platform workflow for post-lock/high-risk corrections. Entity-specific service applies the approved correction; generic correction infrastructure must not bypass domain validation.

## 16. Shared ownership of `academic_years`
Table remains `academic_years` but is treated as a shared institutional reference. Academic-specific `semesters`, `classes`, subjects and schedules remain Academic-owned.
