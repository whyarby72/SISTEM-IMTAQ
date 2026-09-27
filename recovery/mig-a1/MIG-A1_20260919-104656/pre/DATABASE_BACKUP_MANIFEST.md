# MIG-A1 Fresh Pilot Backup Manifest

- Target: local pilot PostgreSQL `imtaq` at `127.0.0.1:5432`
- Runtime identity: `imtaq_app`; PostgreSQL 18.6; timezone `Asia/Jakarta`
- Created: 2026-09-19 10:46:56 Asia/Jakarta
- Scope: current pilot database, custom logical dump and schema-only dump
- Data writes: none during backup
- Logical dump: `pilot_pre_migration_a.dump`
- Schema dump: `pilot_pre_migration_a_schema.sql`
- Parse validation: `pg_restore --list` exit 0, 507 entries
- Logical dump SHA-256: `0053443f225ddad7cb09a9f75c7c268723c253430d771ccaba363b07eabfcba0`
- Schema dump SHA-256: `cd5f5f6c976837087092386769cc0820e30bb83bfa1db9ab8ee25fef9a0f1872`

Credentials were supplied to the dump process from the Laravel runtime configuration and were not recorded in this evidence.
