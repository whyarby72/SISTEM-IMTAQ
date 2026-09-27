# MIG-B1 Recovery Checkpoint

Migration B was applied in infrastructure-only mode to the verified local pilot after a fresh current-state backup.

- Pre backup: `pre/pilot_pre_migration_b.dump`
- Pre schema: `pre/pilot_pre_migration_b_schema.sql`
- Post schema: `post/POST_MIGRATION_SCHEMA.txt`
- Recovery hashes: `RECOVERY_SHA256SUMS.txt`
- New foundation tables are empty.
- Existing participant eligibility fields are nullable and remain NULL for all 26,261 existing rows.
- Automatic rollback: not executed.
- No source authority, NON_ELIGIBLE classification, lineage population, or historical rewrite occurred.
