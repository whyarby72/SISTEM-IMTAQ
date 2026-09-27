# MIG-A1 Recovery Checkpoint

Migration A was applied to the verified local pilot only after a fresh custom-format backup and schema dump were created and parsed successfully.

- Pre backup: `pre/pilot_pre_migration_a.dump`
- Pre schema: `pre/pilot_pre_migration_a_schema.sql`
- Post schema: `post/POST_MIGRATION_SCHEMA.txt`
- Recovery hashes: `RECOVERY_ARTIFACT_SHA256SUMS.txt`
- Automatic rollback: not executed
- Migration B: not executed
- Restore rehearsal: not performed in this task; the fresh backup is parse-validated and retained for an explicit rollback decision.
