# Deployment readiness — SISTEM IMTAQ foundation

This document defines a repeatable readiness path. It does not authorize staging or production deployment.

## Local verification

From the repository root, run:

```bash
application/web/scripts/verify-foundation.sh
```

The script validates the Composer manifest, clears configuration cache, checks route registration, runs the Laravel test harness, and validates the project structure. It deliberately does not run migrations or deployment commands.

## CI baseline

`.github/workflows/application-foundation.yml` runs the same foundation checks on a known checkout. CI uses locked Composer dependencies and SQLite in-memory tests; PostgreSQL connectivity belongs to an environment-specific integration job when that environment is provisioned.

## Promotion model

The deployment unit is an approved repository revision or release, never a manually selected set of files:

1. Development: run the foundation verification script and relevant regression tests.
2. Staging: deploy the approved revision, install locked dependencies, apply only approved compatible migrations, run smoke checks, and record evidence.
3. Production: require approval after staging evidence, deploy the same known revision, run scoped smoke checks, and record release metadata.

Each release record includes release ID, commit/tag, target environment, migration state, config/feature changes, actor/time, automated result, smoke result, and rollback decision.

## Readiness gates

Before promotion, confirm application boot, required tests, relevant authorization checks, migration compatibility, data backup/recovery requirements, critical DQ/security status, and an available rollback or feature-disable path. No business user is asked to identify individual files to upload.

## Rollback

Use the safest applicable path: disable the feature, redeploy the previous known-good revision when schema-compatible, apply a forward fix, or use controlled database restore for an approved recovery incident. `migrate:rollback` is not a universal disaster-recovery procedure.

## Current foundation status

The local foundation has passed Composer validation, Laravel tests, structure checks and a live local PostgreSQL connectivity check. No staging/production target, deployment provider, backup policy, or production secret is configured by this task.
