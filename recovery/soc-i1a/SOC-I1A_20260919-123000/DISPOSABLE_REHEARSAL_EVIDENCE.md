# Disposable PostgreSQL Rehearsal Evidence

Disposable database: `imtaq_soc_i1a_r1_20260919_120000`

- created: YES
- source: SOC-I0C post-migration baseline dump
- baseline restore: PASS
- baseline migrations: 41 applied, SOC-I1A pending
- corrected migration: PASS
- constraints: PASS
- negative integrity tests: PASS
- down test: PASS
- reapply test: PASS
- disposable database dropped: YES

The restore required temporary ownership/privilege normalization inside the disposable database only, because `pg_restore --no-owner` otherwise left baseline tables owned by the local restore role. No pilot database object was altered for this workaround.
