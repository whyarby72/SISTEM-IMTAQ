# SISTEM IMTAQ P1 pre-staging checkpoint

This bundle is a source/audit handoff only. It is not a deployment package and does not authorize staging execution.

## Included

- Current Laravel application source, routes, resources, migrations, seeders, and tests required for P1 audit.
- Active Academic/RBAC architecture documentation.
- P0/P1 change manifests and impacts.
- Read-only PostgreSQL preflight SQL and staging execution/concurrency runbook.
- This checkpoint README.

## Excluded

`.env`, credentials, secrets, database dumps, SQLite/runtime data, `vendor`, `node_modules`, compiled/cache artifacts, and production configuration.

## Required before staging

Review `FIX-P1-R3-FINAL-PRE-STAGING-GATE-2026-09-11.md`. Do not apply the CHECK migration or run the authority bootstrap until the read-only PostgreSQL audit is clean and any active `ADMIN_AKADEMIK` assignment has an approved manual decision.
