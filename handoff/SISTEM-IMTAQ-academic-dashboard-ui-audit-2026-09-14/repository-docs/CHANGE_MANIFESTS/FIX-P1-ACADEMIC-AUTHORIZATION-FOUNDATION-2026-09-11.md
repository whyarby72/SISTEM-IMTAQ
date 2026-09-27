# Change Manifest

- **Change ID:** FIX-P1-ACADEMIC-AUTHORIZATION-FOUNDATION-2026-09-11
- **Task ID:** P1 Phase 2 — Academic Authorization Foundation
- **Date:** 2026-09-11
- **Status:** DONE
- **Git branch/commit:** Not available; repository has no Git metadata

## Outcome

Added a central, effective-date-aware Academic authorization service using the existing RBAC and scope tables. Waka Akademik receives full Academic authority through `academic.domain.manage`; Super Admin receives institution-wide authority through `platform.institution.manage`; Wali Kelas remains limited to effective homeroom scope.

## Files added

- `application/web/app/Domains/Academic/Services/AcademicAuthorizationService.php`
- `application/web/tests/Feature/Academic/AcademicAuthorizationServiceTest.php`
- `codex/CHANGE_IMPACTS/FIX-P1-ACADEMIC-AUTHORIZATION-FOUNDATION-2026-09-11.md`
- This manifest

## Files modified

- `application/web/app/Domains/Academic/Services/WaliKelasContextResolver.php`
- `application/web/app/Domains/Academic/Services/AttendanceExceptionMonitor.php`
- `application/web/app/Domains/Academic/Services/AttendancePeriodLockService.php`
- `application/web/app/Domains/Academic/Services/HistoricalHomeroomHandoverService.php`
- `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
- `application/web/app/Http/Controllers/Academic/StudentAttendanceController.php`
- `application/web/app/Http/Controllers/Academic/AttendanceExceptionController.php`
- `application/web/app/Http/Controllers/Admin/AcademicClassController.php`
- `application/web/app/Http/Controllers/Admin/AcademicStructureController.php`
- `application/web/app/Http/Controllers/Admin/AdminDashboardController.php`
- `application/web/app/Http/Controllers/Admin/MonthlyAttendanceReportController.php`
- `application/web/app/Http/Controllers/Admin/ScheduleRuleController.php`
- `application/web/app/Http/Controllers/Admin/StaffController.php`
- `application/web/app/Http/Controllers/Admin/StudentController.php`
- `application/web/app/Http/Controllers/Admin/SubjectController.php`
- `application/web/database/seeders/ConsolidateAcademicRolesSeeder.php`
- `codex/WORK_LOG.md`
- `PROJECT_PROGRESS.md` (generated progress evidence)

## Authorization decisions

- Existing tables and effective dates are authoritative; no schema change was introduced.
- Permission creation is idempotent. Waka receives Academic permission and existing Academic action permissions. Super Admin receives institution permission only; it is not attached to Waka.
- Waka and Super Admin full authority still remains subject to resource state, workflow, audit, versioning, and lock guards.
- Wali Kelas authority is effective-dated and class-scoped through the existing staff link and homeroom assignment.
- Historical handover business rules are unchanged. The actor permission/role is evaluated as of the historical session date.

## Explicitly deferred

- Concurrency/locking hardening in TeacherAttendanceService and SubstitutionService
- Post-lock correction lifecycle redesign
- Database CHECK constraints or any migration/schema change
- Workflow actor separation where existing services intentionally retain workflow-specific checks

## Verification

- Targeted authorization/P0 suite: **62 tests / 290 assertions / 0 failures**
- Full `tests/Feature/Academic`: **199 tests / 780 assertions / 0 failures**
- PHP syntax and Pint on touched authorization file: PASS
- View cache: pending final closeout command
- Migration/schema/seed import/historical rewrite: NONE; seeder source changed only for idempotent permission bootstrap
