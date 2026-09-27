# Shared Core Data Quality Rules v1.0

## 1. Philosophy
Shared Core DQ protects identity and referential trust. It must not invent missing facts to make completeness look better.

## 2. Student DQ
- `CORE_DQ_DUPLICATE_STUDENT_CODE`: impossible by DB constraint.
- `CORE_DQ_DUPLICATE_ACTIVE_NISN`: block + review.
- `CORE_DQ_MULTIPLE_ACTIVE_IDENTIFIER_TYPE`: block according to type/scope rule.
- `CORE_DQ_MISSING_CURRENT_STATUS`: active operational Student with no resolvable current lifecycle status.
- `CORE_DQ_OVERLAPPING_STATUS`: block.
- `CORE_DQ_INVALID_BIRTH_DATE`: future/invalid dates blocked according to validation.
- `CORE_DQ_PLACEHOLDER_IDENTIFIER`: reject values such as `-`, `0000`, `BELUM ADA` during import/form normalization.

## 3. Guardian DQ
- `CORE_DQ_ORPHAN_GUARDIAN_RELATIONSHIP`: FK block.
- `CORE_DQ_DUPLICATE_ACTIVE_GUARDIAN_RELATION`: block/warn.
- `CORE_DQ_PARENT_REPORT_NO_AUTHORIZED_GUARDIAN`: operational warning when parent report is expected; does not invalidate Student identity.
- `CORE_DQ_NO_ELIGIBLE_CONTACT`: Communication readiness warning, not Student data failure.
- `CORE_DQ_CONTACT_NORMALIZATION_FAILURE`: quarantine/invalid contact point.

Do not auto-merge guardians based on matching name or phone.

## 4. Staff/organization DQ
- duplicate `staff_code`: block.
- invalid overlapping primary organization assignment: block according to approved rule.
- organization hierarchy cycle: block.
- assignment references inactive/nonexistent unit: block/warn based on effective-date validity.
- active user linked to inactive Staff: security/operational exception requiring review, not automatic deletion.

## 5. RBAC/Audit DQ/security
- expired role assignment still authorizing: P0 defect.
- user role without valid role/permission reference: DB block.
- high-value mutation without audit: P0 defect.
- audit payload containing secret credential: security defect.

## 6. Ownership
Core DQ alerts resolve to the responsible master owner (typically Sekretariat/Core administration) with elevated visibility for high-impact identity/security issues. Exact role codes remain management-configurable.

## 7. Metrics
Recommended Shared Core DQ metrics:
- student_identifier_completeness by type (descriptive, not forced requirement);
- duplicate_candidate_count;
- unresolved_identity_mapping_count;
- guardian_relationship_completeness where required;
- eligible_parent_contact_pct for parent-report-ready population;
- invalid_reference_count;
- high_severity_core_dq_open_count;
- core_correction_rate.

Do not use these metrics to score students or staff performance automatically.
