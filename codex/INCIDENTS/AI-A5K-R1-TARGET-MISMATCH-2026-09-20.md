# AI-A5K-R1 — Pilot Migration Target-Mismatch Incident

Date: 2026-09-20 (Asia/Jakarta)
Project: SISTEM IMTAQ
Disposition: `RATIFIED_EXISTING_PILOT_MIGRATION`

## Summary

The AI-A5K migration was intended for an isolated disposable PostgreSQL database, but the first Laravel migration invocation applied it to the pilot database `imtaq`. The execution used shell environment overrides, while the production application's cached Laravel configuration still resolved the PostgreSQL connection to the pilot.

This is classified as a `DEPLOYMENT_TARGET_CONTROL_INCIDENT`. The incident was detected by comparing the Laravel-resolved configuration and the PostgreSQL identity after the unexpected migration. No automatic pilot rollback was performed.

## Target evidence

| Evidence | Actual |
|---|---|
| Pilot PostgreSQL identity | `imtaq`, `127.0.0.1`, port `5432`, PostgreSQL `18.6` |
| Laravel-resolved pilot connection | `127.0.0.1:5432/imtaq`, user `imtaq_app` |
| Intended disposable target | `imtaq_a5k_disposable`, loopback port `55433` |
| First migration result | Migration `2026_09_20_000001_create_ai_provider_configuration_tables` applied to pilot, migration row `43`, batch `34` |
| Pilot AI-provider rows | credentials `0`, configurations `0`, active pointers `0` |
| Public AI feature gate | `false` / OFF |

## Root cause

Laravel loaded the cached database configuration from `application/web/bootstrap/cache/config.php`. A shell-level `DB_DATABASE`/port override therefore did not change the connection used by `php artisan migrate`. The command's intended target was not verified through the actual Laravel-resolved connection before the write.

## Blast-radius verification

The migration source was audited line by line. Its `up()` operation only creates three AI-provider tables, indexes, foreign keys, and defaults. Its `down()` operation only drops those three AI-provider tables. It contains no DML and no references to students, attendance, class sessions, session occurrences, grades, or other Academic transaction tables.

Independent verification restored the accepted pre-A5K dump to a disposable comparison database and compared all 72 non-AI base tables against the pilot. Row counts matched. Data-only dumps also matched after removing only non-semantic `pg_dump` tokens and the expected `migrations_id_seq` difference (`42` versus `43`). Therefore `PILOT_ACADEMIC_BUSINESS_DATA_CHANGED_BY_A5K_MIGRATION=NO`.

## Containment and recovery evidence

- Pilot rollback was intentionally not attempted: automatic rollback could create additional schema churn and was not authorized.
- Fresh post-incident pilot backup: `/tmp/ai-a5k-ir1-postincident-20260920/imtaq-post-incident.dump`.
- Backup SHA-256: `3f968d5a4f0dcde938438275429c1f6ca0a61b2e2673c5e001a5585d5915e476`.
- `pg_restore --list` SHA-256: `51af1f3a00162e75036e9ce4813e4fb712a36bb29ce2e3994377564865bfbdb1`.
- Accepted pre-A5K baseline SHA-256: `4e88818468cc329d8d112dd28ebd97a6d875d9308fccf4bb7341df636b681461`.
- No secrets, credentials, dumps, or `.env` files were added to the repository.

## Isolated reproduction

A separate local PostgreSQL 18.6 cluster used PGDATA `/tmp/ai-a5k-ir1-pgdata.lQImtu`, loopback port `55433`, role `ai_a5k_r1`, and database `imtaq_a5k_disposable`.

1. Direct identity guard: PASS.
2. Laravel-resolved identity guard from a runtime copy without production config cache: PASS.
3. Baseline restore: 42 migrations, no AI-provider tables: PASS.
4. AI-A5K migration: PASS; 3 tables, 2 foreign keys, 1 unique constraint, 6 indexes.
5. FK, unique-provider, and delete-RESTRICT negative checks: PASS; all synthetic rows rolled back/removed.
6. Pilot versus disposable AI-provider schema comparison: PASS after removing only non-deterministic dump tokens.
7. Rollback/reapply on disposable: PASS; 0 AI-provider tables after rollback and 3 after reapply.

## Preventive control

Added `migrate:guarded`, which requires an explicit expected database, host, and port and verifies both Laravel's resolved connection configuration and `current_database()/inet_server_port()` before invoking `migrate`. A cached-config mismatch now fails before opening the migration write path. Regression tests cover both a cached Laravel target mismatch and a PostgreSQL identity mismatch.

Production config cache was not modified to run the verification. The pilot remains at 43 applied migrations with zero pending AI-provider configuration rows and AI feature OFF.

## Final disposition

The existing pilot migration is ratified because source audit, data-impact comparison, fresh backup, isolated PostgreSQL reproduction, schema equivalence, negative checks, rollback/reapply, and application regressions all passed. This ratification does not activate credentials, provider runtime, public AI, AI-A5V, or AI-A6.
