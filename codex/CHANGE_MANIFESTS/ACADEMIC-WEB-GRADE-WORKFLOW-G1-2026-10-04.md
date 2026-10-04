# Change Manifest — Academic Web Grade Workflow G1

Date: 2026-10-04
Task: `ACADEMIC-WEB-GRADE-WORKFLOW-G1-AUTHORIZATION-READ-SURFACE`
Branch: `feat/super-admin-user-access-preferences`
Baseline: `64f9123dec48ff0a36d6a0b648c4e81164f34909`

## Scope

Implemented the read-only semester grade surface, strict `academic.grades` feature boundary, exact subject-teacher/Wali/Waka authorization service, scoped sidebar navigation, and focused authorization/read-only regression tests.

## Files changed

- `application/web/app/Domains/Academic/Services/SemesterGradeAuthorizationService.php`
- `application/web/app/Domains/Academic/Services/SemesterGradeWorkspaceService.php`
- `application/web/app/Http/Controllers/Academic/SemesterGradeController.php`
- `application/web/app/Http/Middleware/EnsureGradeFeatureAccess.php`
- `application/web/bootstrap/app.php`
- `application/web/database/seeders/UserAccessFeatureSeeder.php`
- `application/web/resources/views/academic/grades/index.blade.php`
- `application/web/resources/views/academic/partials/sidebar.blade.php`
- `application/web/routes/web.php`
- `application/web/tests/Feature/Academic/SemesterGradeWorkspaceUiTest.php`
- `application/web/tests/Feature/Admin/UserAccessManagementTest.php`

## Safety boundary

No migration, schema change, runtime configuration change, grade write endpoint, pilot/staging/production database write, AI/provider change, or public Academic AI activation. G2 DRAFT-only state hardening remains deferred.

## Validation evidence

- PHP lint: PASS for all changed PHP files.
- Composer validate: PASS.
- Route list, view cache, and project structure: route list/view cache PASS; project structure command requires repository-root invocation.
- Focused PHPUnit: BLOCKED before test execution because the local `.env` resolves to protected pilot database `imtaq`; the test guard correctly refused it. No database write occurred. Disposable PostgreSQL `imtaq_test_*` was not available locally.
- Exact disposable PostgreSQL CI verification: pending after push.

## Rollback

Revert the single implementation commit; no persistent business data rollback is required because no database write was authorized or performed.

## Decision

`GRADE_G1_IMPLEMENTED_PASS` only after exact CI and focused tests pass. Until then: `HOLD / CI_VERIFICATION_PENDING`.
