# Staging Release Gate — 2026-09-07

## Status

`STAGING_PREPARATION_INCOMPLETE — BLOCKED_PENDING_REPOSITORY_AND_ENVIRONMENT`

Local application readiness is verified, but no staging deployment is authorized until the repository and environment targets are supplied by the owner.

## Checklist

### Release identity

- [x] Full regression: 216 tests / 745 assertions passed.
- [ ] Canonical Git repository, branch, and commit/tag.
- [ ] Clean release working tree and reviewable change set.
- [x] `application/web/.env` identified as local-only and excluded from release.
- [ ] Root `.gitignore` or equivalent release exclusion policy.

### Staging environment

- [ ] Staging host/provider.
- [ ] Staging database target and credentials provisioned outside source code.
- [ ] Persistent storage path separated from replaceable release files.
- [ ] Privacy-safe staging data strategy.

### Database and backup

- [x] Local migration state through `2026_09_06_000001_create_monthly_student_attendance_snapshots` is RUN.
- [ ] Staging backup taken before migration.
- [ ] Restore test evidenced with timestamp and operator.
- [ ] RTO/RPO and backup retention approved.
- [ ] Migration compatibility and lock/downtime review completed on staging.

### Application and monitoring

- [ ] Locked dependencies installed from `composer.lock`.
- [ ] Pre-deploy test and cache commands recorded.
- [ ] Error logging/health monitoring configured.
- [ ] Login and RBAC allow/deny smoke tests on staging.
- [ ] Academic dashboard, report detail, CSV/PDF, and snapshot integrity smoke tests on staging.

### Rollback

- [ ] Known-good Git release identified.
- [ ] Application rollback rehearsal completed.
- [ ] Forward-fix plan documented for schema/data changes.
- [ ] Database restore owner and data-loss window documented.

## Current evidence

- Local full regression: PASS, 216/216.
- Local route inventory: 39 Academic/Admin routes.
- Local data: 84 monthly snapshots, 84 lineage rows, 20 session attendance pilot rows, 5 monthly class summaries.
- Local migration: snapshot migration RUN.

## Required owner inputs before staging

1. Canonical Git repository/remote and release branch/tag.
2. Staging host/provider and staging database target.
3. Backup retention, restore RTO/RPO, and deployment approval actor.
4. Staging secrets and monitoring owner, provisioned through the environment—not committed to the repository.

Until these are available, the safe state is local UAT only. No deployment, backup claim, or production promotion is recorded.
