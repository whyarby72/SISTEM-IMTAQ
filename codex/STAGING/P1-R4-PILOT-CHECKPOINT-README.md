# SISTEM IMTAQ — P1-R4 Pilot Baseline

This checkpoint is an audit and handoff bundle for the current development/pilot source state. It is not staging or production deployment authorization.

## Management status

- **PILOT GOVERNANCE MODE:** ACTIVE
- **SYSTEM MODE:** DEVELOPMENT + REAL-DATA PILOT
- **DEVELOPMENT STRATEGY:** WAKA-FIRST
- **REAL DATA:** PROTECTED
- **WAKA_AKADEMIK:** FULL ACADEMIC AUTHORITY
- **SUPER_ADMIN:** FULL INSTITUTION AUTHORITY
- **ADMIN_AKADEMIK:** RETIRED
- **P0:** CLOSED
- **P1:** CLOSED_SOURCE_LEVEL_FOR_PILOT
- **STAGING:** DEFERRED_BY_MANAGEMENT
- **PRODUCTION:** NOT READY / NOT CURRENT TARGET

## Deferred PostgreSQL and staging items

- actual distinct-value audit
- authority permission bootstrap
- CHECK migration execution
- real PostgreSQL concurrency verification
- `ADMIN_AKADEMIK` real assignment review
- backup/restore and staging sign-off

## Data protection rule

Never run `migrate:fresh`, `db:wipe`, `TRUNCATE`, destructive seed, or silent historical rewrite against a database containing real data. Corrections to historical/validated/locked facts must remain traceable through the approved audit/version workflow.

## Bundle boundaries

Included: current application source, migrations, seeders, tests, active architecture/RBAC documentation, change impacts/manifests, staging SQL/runbook, and project status/context files.

Excluded: `.env`, credentials, secrets, real database dumps, personal/sensitive exports, runtime cache, `vendor`, `node_modules`, SQLite/runtime storage, and compiled cache artifacts.
