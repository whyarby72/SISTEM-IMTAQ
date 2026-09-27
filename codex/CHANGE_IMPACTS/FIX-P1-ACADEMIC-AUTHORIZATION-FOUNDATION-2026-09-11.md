# Change Impact Record

- Request / problem: Establish one effective-date-aware Academic authorization boundary for P1 without migration or schema change.
- Change ID / Task ID: FIX-P1-ACADEMIC-AUTHORIZATION-FOUNDATION-2026-09-11 / P1 Phase 2
- Date: 2026-09-11
- Owner module: Academic / Shared Core Authorization
- Change class: `SECURITY_GLOBAL`
- Affected modules/workstreams: Academic attendance, dashboard, period lock, historical handover, selected Admin Academic entry points, RBAC bootstrap
- Source-of-truth entities/services affected: Existing roles, permissions, role_permissions, user_role_assignments, UserStaffLink, and homeroom assignments only
- Expected file/write scope: Central AcademicAuthorizationService, existing Academic authority callers, existing role/permission seeder, relevant tests, Work Log, and this impact/manifest record
- Protected zones touched: No migration, schema, schedule, historical data, or production configuration
- RBAC/privacy/security impact: Runtime authority now uses effective permissions/roles and resource scope; no new role model or raw controller role checks introduced
- Migration/backward-compatibility impact: None. Existing authorization rows are reused; compatibility fallback remains until target permission rows are configured.
- Required regression scope: Academic authorization, Wali scope, attendance, period lock, dashboard, handover, and P0 regression tests
- Decision/status: COMPLETED; P1 Phase 3 not started
