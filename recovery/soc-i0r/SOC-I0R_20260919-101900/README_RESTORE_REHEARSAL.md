# SOC-I0R Disposable PostgreSQL Restore Rehearsal

Task: SOC-I0R. This evidence proves that the SOC-I0 backup can be restored into an isolated disposable database without using the pilot database as a restore target.

Source backup:
`recovery/soc-i0/SOC-I0_20260919-090706/database/pilot_pre_soc_implementation.dump`

Backup SHA-256: `3eae1ece49099cbba3db2f502fa16a4f3bc7fccadf7fdc830453a061ab969849`

Schema dump SHA-256: `e0dfd34827ad8fbaf2f3f1e6df32253d85ca1b5a6a65d59413ca98a3309bf358`

Disposable database: `imtaq_soc_i0r_20260919_101900`; created with the local administrative role and owned by `imtaq_app`, restored with `pg_restore --no-owner --no-privileges --exit-on-error`, queried, and dropped with `DROP DATABASE ... WITH (FORCE)` after verification.

Restore writes were isolated and expected. No migration, seed, import, application write, or pilot database mutation occurred. Application connection smoke test was not run; raw PostgreSQL restore verification was sufficient.

Pilot pre/post identity remained `imtaq/public/127.0.0.1:5432/PostgreSQL 18.6/Asia/Jakarta`, fingerprint `bc1f8e600fa3f7bc7cd14eca799025043527983b4be6d098c13708ef755912b2`. Pilot migration state remained 39 applied and 2 pending.
