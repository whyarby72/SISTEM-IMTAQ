# Change Impact Record

- Request / problem: Add PostgreSQL CHECK constraints for the Academic integrity vocabulary verified in source and fixtures.
- Change ID / Task ID: FIX-P1-POSTGRES-CONTROLLED-VOCABULARY-2026-09-11 / P1 Phase 6
- Date: 2026-09-11
- Owner module: Academic / Database integrity
- Change class: `SECURITY_GLOBAL`
- Expected write scope: One forward-only PostgreSQL CHECK migration, source/fixture inventory evidence, tests, Work Log, and this impact/manifest record
- Data impact: No UPDATE, backfill, deletion, version rewrite, audit mutation, or seed/import execution.
- Compatibility: Nullable attendance status columns preserve NULL; non-null columns remain non-null as previously defined.
- Local limitation: SQLite migration path is a deliberate no-op for PostgreSQL-specific CHECK syntax; PostgreSQL distinct-value and constraint proof are deferred to staging.
- Decision/status: COMPLETED; P1 Phase 7 not started
