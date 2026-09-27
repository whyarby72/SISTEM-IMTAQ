# Shared Core Conceptual Data Model v1.0

## 1. Core entity map

```text
organizational_units
      │
      ├────────────── staff_organizational_assignments ─── staff
      │                                                      │
      │                                                      └── user/account link (platform)
      │
      └──────── locations

students
   │
   ├── student_identifiers
   ├── student_status_history
   ├── student_guardian_relationships ─── guardians
   │                                         │
   │                                         ├── guardian_contact_channels
   │                                         └── guardian_addresses (optional)
   │
   └── domain-owned assignments/facts
       Academic enrollment / Tahfizh halaqah / Room / etc.
```

## 2. Student
Grain: **one real student**.

Recommended canonical attributes:
- `id` UUID PK.
- `student_code` permanent unique IMTAQ code.
- `full_name`.
- `arabic_name` nullable.
- `nickname` nullable.
- `gender_code` nullable/controlled where institutionally required.
- `birth_place` nullable.
- `birth_date` nullable.
- `entry_year` nullable.
- `cohort_id` nullable.
- `created_at`, `updated_at`.

Do not store current class, halaqah, room or lifecycle status as canonical current-value fields.

## 3. Student identifiers
Grain: **one identifier version/record for one student and identifier type**.

Types initially:
- `NIS`
- `NISN`

State dimensions:
- verification: `UNVERIFIED / VERIFIED`
- record: `ACTIVE / SUPERSEDED / INVALIDATED`

Unknown value means no active identifier row, never a placeholder.

## 4. Student lifecycle
Grain: **one effective status interval for one student**.

Candidate statuses:
- `ACTIVE`
- `GRADUATED`
- `WITHDRAWN`
- `TRANSFERRED_OUT`
- `DISMISSED`
- `DECEASED`

Effective interval convention: `[effective_from, effective_until)`.

## 5. Guardian
Grain: **one real guardian/contact person**, not one relationship.

Recommended attributes:
- `id` UUID PK.
- `guardian_code` stable unique code.
- `full_name`.
- optional identity metadata approved by policy.
- `record_status` (`ACTIVE/INACTIVE/MERGED` candidate; merge workflow remains controlled).
- audit timestamps.

Do **not** store `relationship = FATHER` on Guardian because one person may relate to multiple students differently.

## 6. Student ↔ Guardian relationship
Grain: **one effective relationship between one Student and one Guardian**.

Recommended attributes:
- `student_id`
- `guardian_id`
- `relationship_type` (`FATHER`, `MOTHER`, `GUARDIAN`, `OTHER` initial controlled set)
- `effective_from`, `effective_until`
- `relationship_status`
- `communication_priority` nullable
- `authorized_for_parent_reports` boolean or controlled authorization status
- `source_reference` nullable
- `notes` restricted/nullable

Communication consent remains Communication-owned; report-recipient authorization belongs to relationship/governance context.

## 7. Guardian contact channel
Grain: **one effective contact point for one guardian**.

Recommended:
- `channel_type`: `PHONE`, `WHATSAPP`, `EMAIL` initially.
- `normalized_value`.
- `display_value`.
- `verification_status`: `UNVERIFIED`, `VERIFIED`, `FAILED`, `REVOKED` candidate.
- `is_primary`.
- `active_from`, `active_until`.
- `verified_at`, `verified_by` nullable.
- `source_reference` nullable.

Verification means the contact point is verified as belonging/usable for that guardian. It does **not** mean the guardian has opted in to every communication purpose.

## 8. Staff
Grain: **one real staff member**.

Recommended:
- `id` UUID PK.
- `staff_code` permanent institution code.
- `full_name`.
- contact fields only when operationally required and authorized.
- `active_from`, `active_until` or separate status history.
- no RBAC role string embedded in Staff.

## 9. Staff organizational assignment
Grain: **one staff × organizational unit × effective assignment interval**.

Recommended:
- `staff_id`
- `organizational_unit_id`
- `assignment_type/title_code`
- `is_primary`
- `effective_from/effective_until`
- `source_reference`

This supports departmental/unit context but is not itself a permission grant.

## 10. Organizational unit
Grain: **one institutional organizational node**.

Recommended:
- `unit_code`
- `unit_name`
- `unit_type`
- `parent_unit_id` nullable
- `effective_from/effective_until`
- `record_status`

Prevent hierarchy cycles.

## 11. Location
Grain: one reusable physical/logical location.

Examples: classroom, mosque, dorm building/room, hall, field. Domain-specific occupancy/assignment remains domain-owned.

## 12. Academic year / institutional period
`academic_years` should be treated as a shared institutional temporal reference because multiple domains may align activities/reports to the same year. Table naming is retained to avoid unnecessary migration. Semester remains primarily Academic-owned until cross-domain use requires a formal shared-period contract.

**Impact note:** ownership of `academic_years` is refined from Academic-only to Shared Core/reference; Academic remains a major consumer/operational maintainer. This is a `SHARED_CORE` contract change before implementation, not a physical schema migration yet.
