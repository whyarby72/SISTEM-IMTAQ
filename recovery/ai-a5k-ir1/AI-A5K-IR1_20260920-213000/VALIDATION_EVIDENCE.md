# AI-A5K-IR1 Validation Evidence

- Pilot identity: `imtaq / 127.0.0.1:5432 / PostgreSQL 18.6`.
- Pilot migration count: `43`; AI-provider rows: `0 / 0 / 0`; feature gate: `false`.
- Post-incident backup SHA-256: `3f968d5a4f0dcde938438275429c1f6ca0a61b2e2673c5e001a5585d5915e476`.
- Backup restore-list SHA-256: `51af1f3a00162e75036e9ce4813e4fb712a36bb29ce2e3994377564865bfbdb1`.
- Non-AI row-count comparison: PASS, 72 tables.
- Non-AI data-only comparison after expected dump normalization: PASS.
- Disposable target guards: direct PASS; Laravel-resolved PASS.
- Disposable migration: PASS.
- PostgreSQL FK/unique/RESTRICT checks: PASS.
- Disposable rollback/reapply: PASS.
- AI tests: 35/120 PASS; Academic: 366/1463 PASS; full: 479/1931 PASS.
- PHP lint, Pint, view cache: PASS.
