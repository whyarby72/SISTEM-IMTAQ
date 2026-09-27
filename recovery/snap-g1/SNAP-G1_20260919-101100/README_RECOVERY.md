# SNAP-G1 Recovery Checkpoint

This checkpoint preserves the pre-change copies of only the six source/test files intentionally changed by SNAP-G1 and their pre-change SHA-256 manifest.

SNAP-G1 is a lazy, idempotent participant-snapshot guard. It does not eager-snapshot session generation, alter cancellation, add a migration, change schema, repair historical rows, or implement HELD.

Pre-change continuity: B2C application source manifest remained 380 checked, 0 missing, 0 mismatched before the change. SOC-I0.2 governance evidence was present and valid.

Post-change tests:
- focused snapshot/attendance tests: 57 tests, 355 assertions, PASS
- Academic suite: 294 tests, 1,257 assertions, PASS
- full application suite: 407 tests, 1,725 assertions, PASS

Pilot PostgreSQL was not used by tests. No migration, restore, import, seed, or database write was executed.

The three historical cancelled sessions remain untouched. Restore/recovery of application source files can use the copies under `source/` after verifying `PRE_CHANGE_SOURCE_MANIFEST.sha256`.
