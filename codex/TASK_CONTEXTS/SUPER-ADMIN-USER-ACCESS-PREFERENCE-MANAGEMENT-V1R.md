# SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1R

## Type

`SECURITY_AND_FUNCTIONAL_COMPLETION_REMEDIATION`

## Starting point

- Branch: `feat/super-admin-user-access-preferences`
- Starting HEAD: `79fb525c4369f7d31530a2e5f261499713e37ddd`
- Pilot/staging/production database writes: not authorized.

## Scope

Complete the existing User & Akses implementation without creating a second
identity or authorization system. Preserve User, Staff, UserStaffLink, Role,
Permission, UserRoleAssignment, Feature, UserFeatureOverride, UserPreference,
FeatureAccessResolver, AcademicAuthorizationService, and append-only audit
contracts.

## Required controls

1. Apply effective feature middleware to every seeded route/UI surface:
   `academic.dashboard`, `academic.attendance`,
   `academic.attendance_review`, `academic.reports`, `academic.students`,
   `academic.classes`, `academic.structure`, `academic.schedules`,
   `academic.subjects`, `academic.staff`, `platform.user_access`,
   `platform.system_settings`, and `ai.academic_assistant`. A known DISABLED
   feature denies direct access; ENABLED never bypasses role, permission,
   scope, or resource authorization; INHERIT preserves baseline behavior.
2. Sidebar visibility uses effective feature access.
3. Consume landing, compact-sidebar, table-page-size, and help-text
   preferences without increasing authorization.
4. Enforce disabled accounts on subsequent sessions and invalidate safely.
5. Validate only the supported scope vocabulary and canonical scope keys;
   preserve effective dates and require start before end.
6. Expose role revoke/end-assignment UI; never delete history; block self
   demotion and protect the last active SUPER_ADMIN.
7. Make feature override batches atomic and versioned; audit committed state.
8. Audit actor, target, action, time, before/after, and reason without
   passwords, hashes, tokens, credentials, or secrets.
9. Use temporary-password plus `must_change_password` lifecycle for V1;
   enforce change before normal authenticated navigation.
10. Maintain at least the 26-case focused User & Akses matrix in
    `tests/Feature/Admin/UserAccessManagementTest.php`.

## Verification contract

Required: PHP syntax, Blade cache, route compilation, Pint, structure check,
focused User & Akses tests, disposable PostgreSQL migration-from-zero,
Academic/AI regression, full foundation verification, and exact final CI.
Migration is allowed only on an independently identity-verified disposable
PostgreSQL database. Never use `imtaq` or alter applied migrations.

## Current checkpoint

Local static checks pass. Focused test execution is `BLOCKED_CONFIGURATION`:
the requested disposable targets `imtaq_test_v1r` and `imtaq_ci_test` do not
exist, and the repository guard correctly rejected the local `.env` pilot
identity. No database write was attempted.

## Decision gate

Until disposable PostgreSQL and exact CI pass:

`SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1R = HOLD / FEATURE_GATE_SECURITY_INCOMPLETE`

On complete verification only, the decision may become
`COMPLETED / PASS / READY_FOR_CONTROLLED_PILOT_MIGRATION_REVIEW`.

`ACADEMIC_WALI_FIRST_DAY_CONTROLLED_UAT = HOLD / HUMAN_OBSERVATION_REQUIRED`
and `Public Academic AI = OFF` remain unchanged.
