# MIG-B1 Fresh Pilot Backup Manifest

- Target: local pilot PostgreSQL `imtaq` at `127.0.0.1:5432`
- Runtime identity: `imtaq_app`; PostgreSQL 18.6; timezone `Asia/Jakarta`
- Created: 2026-09-19 10:56:27 Asia/Jakarta
- Scope: current pilot database after MIG-A1 and before MIG-B1
- Logical dump: `pilot_pre_migration_b.dump`
- Schema dump: `pilot_pre_migration_b_schema.sql`
- Parse validation: `pg_restore --list` exit 0, 507 entries
- Logical dump SHA-256: `e90366814d7abaeb82dbdc17db8b914102b7ce1782f11b876583913584ff6975`
- Schema dump SHA-256: `95c6fb4c7b8b1cd457e726f712624ec4bc0ff2730f06b43e6bc89b686e88ac6b`

Credentials were supplied to the dump process from Laravel runtime configuration and were not recorded in this evidence.
