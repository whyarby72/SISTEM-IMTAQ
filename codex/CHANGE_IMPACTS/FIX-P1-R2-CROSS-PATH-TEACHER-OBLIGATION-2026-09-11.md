# Change impact — P1-R2 cross-path teacher obligation serialization

- Change ID: FIX-P1-R2-CROSS-PATH-TEACHER-OBLIGATION-2026-09-11
- Scope: teacher-obligation serialization across substitution, swap, reschedule, extra/session generation, and participation materialization
- Migration/schema: NONE
- Real seeder/migration/staging execution: NONE
- Authorization/business policy: unchanged
- Historical data: unchanged

The shared primitive uses PostgreSQL transaction-scoped advisory locks. SQLite deliberately skips the database lock and is used only for deterministic source and behavior tests; real contention remains a staging dependency.
