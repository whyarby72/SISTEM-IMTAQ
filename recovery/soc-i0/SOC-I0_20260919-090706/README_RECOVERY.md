# SOC-I0 Recovery Checkpoint

Task: SOC-I0 — Pre-Implementation Runtime Evidence & Recovery Gate  
Created: 2026-09-19 09:07:06 Asia/Jakarta  
Project: SISTEM IMTAQ  
Recovery path: `/Users/afradadmedia/DATA/PTAFRADAD/CHATGPTLOCAL/SISTEM-IMTAQ/recovery/soc-i0/SOC-I0_20260919-090706/`

This checkpoint contains read-only runtime evidence, an application-source snapshot, and a local logical PostgreSQL backup created after runtime identity verification. It is not an implementation authorization and must not be copied to external systems without separate privacy approval.

Database identity is redacted to the minimum necessary: local PostgreSQL at `127.0.0.1:5432`, database identifier `imtaq`, schema `public`, application role `imtaq_app`. No password, application key, or credential is stored here.

Contents include:

- `source/` — rollback-relevant application source snapshot, excluding `.env*`, `vendor`, `node_modules`, runtime logs/cache, and macOS metadata;
- `database/` — custom logical dump and schema-only SQL dump;
- aggregate schema inventory and read-only profiling SQL/results;
- migration state, backup manifest, and SHA-256 recovery manifest;
- pre-implementation source hash manifest.

The database backup may contain sensitive business data and is intentionally local. No restore rehearsal was performed. No application source, test source, migration, seed, import, RBAC, or business database data was changed.
