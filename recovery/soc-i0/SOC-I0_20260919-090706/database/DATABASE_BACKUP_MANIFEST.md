# SOC-I0 Database Backup Manifest

Backup timestamp: 2026-09-19 09:07:06 Asia/Jakarta  
Redacted DB identity: local PostgreSQL `127.0.0.1:5432`, database `imtaq`, schema `public`, application role `imtaq_app`  
DB identity fingerprint SHA-256: `bc1f8e600fa3f7bc7cd14eca799025043527983b4be6d098c13708ef755912b2`

PostgreSQL version: `PostgreSQL 18.6 (Homebrew) on aarch64-apple-darwin25.6.0`  
Database size at handshake: `26343103` bytes  
Migration state: repository 41 files; database 39 applied; `PARTIAL`, two named unapplied repository migrations documented in `../MIGRATION_STATE.txt`.

## Full logical archive

Filename: `pilot_pre_soc_implementation.dump`  
Size: `1021669` bytes  
SHA-256: `3eae1ece49099cbba3db2f502fa16a4f3bc7fccadf7fdc830453a061ab969849`  
Format: PostgreSQL custom archive, no owner, no ACL  
Archive parse validation: `YES` (`pg_restore --list` succeeded)

## Schema-only dump

Filename: `pilot_pre_soc_implementation_schema.sql`  
Size: `135440` bytes  
SHA-256: `e0dfd34827ad8fbaf2f3f1e6df32253d85ca1b5a6a65d59413ca98a3309bf358`

Restore test: `NO` — explicitly outside this checkpoint.  
Backup contents: sensitive business data may be present; keep local and do not attach or print.  
Credentials exposed: `NO`.
