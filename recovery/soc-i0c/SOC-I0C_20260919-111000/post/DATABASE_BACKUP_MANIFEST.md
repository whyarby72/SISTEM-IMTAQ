# SOC-I0C Post-Migration Backup Manifest

- Target: local pilot PostgreSQL `imtaq` at `127.0.0.1:5432`
- PostgreSQL: 18.6
- State: fully migrated, 41 applied / 0 pending
- Logical backup: `POST_MIGRATION_BASELINE.dump`
- Schema backup: `POST_MIGRATION_BASELINE_schema.sql`
- `pg_restore --list`: exit 0, 522 entries
- Logical backup SHA-256: `ebe3b43ac5d1dbf8687626aca7e57852cae28c3199f7a6abbf9543e67e48cbea`
- Schema backup SHA-256: `51229358bbf5b610cb88b1178f568ac4af5e8320177d971ececa69920711e749`

Credentials were not recorded in this artifact.
