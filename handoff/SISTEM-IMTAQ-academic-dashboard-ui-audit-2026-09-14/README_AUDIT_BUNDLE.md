# SISTEM IMTAQ — Academic Dashboard UI Audit Bundle

Purpose: handoff of the current source needed to redesign only `GET /academic/dashboard` in ChatGPT.

## Scope

The primary page is `application/web/resources/views/academic/dashboard.blade.php`. Related controller, dashboard/export services, academic models, routes, shared academic shell/sidebar styles, tests, migrations, and active architecture/change documentation are included for context.

Technical source of truth: the included current application source. Existing business behavior, KPI semantics, authorization, routes, database schema, and real data are not changed by this bundle.

## Suggested audit entry points

1. `application/web/resources/views/academic/dashboard.blade.php`
2. `application/web/app/Http/Controllers/Academic/AcademicDashboardController.php`
3. `application/web/app/Domains/Academic/Services/AcademicRoleDashboardService.php`
4. `application/web/app/Domains/Academic/Services/AcademicDashboardExportService.php`
5. `application/web/resources/views/academic/partials/sidebar.blade.php`
6. `application/web/resources/views/academic/partials/sidebar-styles.blade.php`
7. `application/web/tests/Feature/Academic/AcademicRoleDashboardServiceTest.php`
8. `application/web/routes/web.php`

## Handoff rules

- Upgrade presentation/UI only for the Academic Dashboard page.
- Preserve routes, form/query parameters, KPI and trend semantics, RBAC, export contract, and backend behavior unless explicitly approved.
- Do not include or request secrets, `.env`, credentials, real database dumps, personal exports, runtime cache, or `vendor`.

## Bundle contents

- Current application source excluding `vendor`, runtime storage/cache, `.env`, credentials, and build artifacts.
- Current migrations, seeders, tests, and public assets.
- Selected architecture/RBAC and change-management documentation.
- Current task context and relevant change manifests.

Generated: 2026-09-14 (Asia/Jakarta).

