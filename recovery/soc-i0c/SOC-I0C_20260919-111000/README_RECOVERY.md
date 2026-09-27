# SOC-I0C Recovery Checkpoint

This directory is the durable post-MIG-B1 baseline before Session Occurrence implementation.

Included artifacts:

- `post/POST_MIGRATION_BASELINE.dump`
- `post/POST_MIGRATION_BASELINE_schema.sql`
- `post/DATABASE_BACKUP_MANIFEST.md`
- `post/APPLICATION_SOURCE_SNAPSHOT/`
- `post/APPLICATION_SOURCE_MANIFEST.sha256`
- `CURRENT_BUSINESS_AGGREGATE_SNAPSHOT.txt`
- `MIGRATION_STATE.txt`
- `MIGRATION_A_CONSTRAINTS.txt`
- `MIGRATION_B_FOUNDATION_STATE.txt`
- `SNAP_G1_GUARD_STATE.txt`
- `CANONICAL_PRE_SOC_IMPLEMENTATION_BASELINE.md`
- `RECOVERY_SHA256SUMS.txt`

The PostgreSQL backup is parse-validated with `pg_restore --list`. No restore was executed here. No business data was written or rewritten.
