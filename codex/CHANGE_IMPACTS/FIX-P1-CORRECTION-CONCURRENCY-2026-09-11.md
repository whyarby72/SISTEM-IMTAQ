# Change Impact Record

- Request / problem: Make post-lock correction review and apply operations race-safe and prevent locked direct override bypass.
- Change ID / Task ID: FIX-P1-CORRECTION-CONCURRENCY-2026-09-11 / P1 Phase 5
- Date: 2026-09-11
- Owner module: Academic / Attendance correction workflow
- Change class: `SECURITY_GLOBAL`
- Expected write scope: `PostLockAttendanceCorrectionService`, its correction regression tests, Work Log, and this impact/manifest record
- Authorization: Existing `AcademicAuthorizationService` is used for full Academic correction authority; no permission catalog or policy expansion.
- Workflow: Review locks `CorrectionRequest`; apply locks request first and attendance second; locked direct Waka/Super override is rejected and must use post-lock request/review/apply.
- Schema/data impact: No migration, schema change, historical rewrite, or backfill.
- Concurrency limitation: Local SQLite cannot prove PostgreSQL row-lock contention; real review concurrency remains deferred to staging.
- Decision/status: COMPLETED; P1 Phase 6 not started
