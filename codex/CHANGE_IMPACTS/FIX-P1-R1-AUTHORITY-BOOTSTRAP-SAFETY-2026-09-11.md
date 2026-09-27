# Change impact — P1-R1 authority permission bootstrap safety

- Change ID: FIX-P1-R1-AUTHORITY-BOOTSTRAP-SAFETY-2026-09-11
- Scope: authorization bootstrap seeder, active RBAC documentation, and active test fixtures
- Runtime transaction services: NONE
- Migration/schema: NONE
- Real seed execution: NONE
- Historical RBAC cleanup: NONE

The active `ConsolidateAcademicRolesSeeder` previously deleted pilot user-role assignments, deleted all `ADMIN_AKADEMIK` assignments, detached its permissions, and deleted the role. Those statements were active bootstrap behavior, not an immutable migration. They have been removed from the bootstrap. Legacy cleanup is explicitly `LEGACY_RBAC_CLEANUP_DEFERRED` and requires a separate approved governance task. Active RBAC documentation now marks the role retired; historical manifests/work logs remain unchanged.
