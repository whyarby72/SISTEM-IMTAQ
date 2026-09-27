# Change Manifest

- Change ID: FIX-P1-R1-AUTHORITY-BOOTSTRAP-SAFETY-2026-09-11
- Status: DONE — local verification complete; real assignment inventory not inspectable
- Git metadata: unavailable in repository workspace

## Audit evidence

- Pilot cleanup was executed at the former `UserRoleAssignment::...->delete()` block for `azhar.pilot@example.test` and `wawan.sn.pilot@example.test`.
- `ADMIN_AKADEMIK` cleanup was executed at the former assignment delete, permission detach, and role delete block.
- Both blocks were part of the active `ConsolidateAcademicRolesSeeder::run()` method, not a migration-only path.
- No test depended on that cleanup; existing role/assignment preservation is now covered explicitly.
- Active runtime search has no `ADMIN_AKADEMIK` authorization reference. The active RBAC matrix marks the role `RETIRED`; historical/archive manifests and work log entries were preserved.
- No real database was inspected, so `ADMIN_AKADEMIK ACTIVE ASSIGNMENTS FOUND` is `NOT_INSPECTABLE`; no automatic promotion or cleanup was performed.

## Behavior

- `academic.domain.manage`: created once with `firstOrCreate`; assigned additively to WAKA.
- `platform.institution.manage`: created once with `firstOrCreate`; assigned additively to SUPER_ADMIN when the role exists.
- Existing roles, permissions, role assignments, effective dates, pilot assignments, and `ADMIN_AKADEMIK` are preserved.
- No detach, delete, rename, effective-date mutation, cleanup, migration, or real seed execution.
- Legacy cleanup: `LEGACY_RBAC_CLEANUP_DEFERRED`.

## Files

- Modified: `application/web/database/seeders/ConsolidateAcademicRolesSeeder.php`; `application/web/tests/Feature/Academic/AcademicAuthorizationServiceTest.php`; active Admin/Academic test fixtures; `docs/02_architecture/RBAC_MATRIX.md`; Phase 7 impact wording; `codex/WORK_LOG.md`.
- Added: `application/web/tests/Feature/Shared/ConsolidateAcademicRolesSeederTest.php`; this impact and manifest.

## Verification

- Targeted RBAC/authorization: **14 tests / 58 assertions / 0 failures**.
- Academic regression: **210 tests / 812 assertions / 0 failures**.
- Shared/Auth regression: **63 tests / 273 assertions / 0 failures**.
- Admin regression: **44 tests / 164 assertions / 0 failures**.
- View cache: **PASS**.
- Pint: **PASS**.
- Real seed execution: **NONE**; all seeder assertions used isolated RefreshDatabase.

## Closeout

No staging or real database seed was performed. P1-R2 is not started.
