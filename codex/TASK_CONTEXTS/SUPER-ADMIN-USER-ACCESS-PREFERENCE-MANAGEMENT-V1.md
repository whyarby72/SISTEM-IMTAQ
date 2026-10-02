# SUPER-ADMIN-USER-ACCESS-PREFERENCE-MANAGEMENT-V1

## Scope

Implement the dedicated Super Admin `User & Akses` module on a new feature
branch. Extend the existing User, Staff, RBAC, scope, and append-only audit
contracts; do not create duplicate identity or authorization systems.

## Required behavior

- Manage account identity, ACTIVE/DISABLED lifecycle, safe Staff linkage, role
  assignment and scope, per-user feature overrides, and whitelisted UI
  preferences.
- Enforce access server-side through the existing effective role/permission
  model. An ENABLED feature override never bypasses permission or scope.
- Preserve audit history; role revocation ends an assignment rather than
  deleting historical facts. A disabled account cannot authenticate.
- Protect self-disable/self-demotion and the last active SUPER_ADMIN.
- Never render or audit passwords, hashes, tokens, API keys, or secrets.

## Write boundary

Application source, new migrations, registry seeder, tests, views, and
governance metadata only. No PILOT/staging/production database migration,
business-data write, AI/provider mutation, deployment, or Academic UAT action.

## Verification

Use disposable PostgreSQL 18.6 through the repository identity guard for
migration replay and tests. Required checks: PHP syntax, view compilation,
focused User & Akses tests, foundation workflow, and existing Academic/AI
regression as available on the exact implementation commit.

## Decision

On exact-current CI PASS: `SUPER_ADMIN_USER_ACCESS_PREFERENCE_MANAGEMENT_V1`
is `IMPLEMENTED / READY_FOR_CONTROLLED_PILOT_MIGRATION_REVIEW`. This is not
authorization to migrate the PILOT database.
