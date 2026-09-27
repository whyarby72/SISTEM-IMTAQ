# Change Impact Record

- Request / problem: Prevent substitution teacher conflicts, including substitute-vs-substitute overlap, under concurrent requests.
- Change ID / Task ID: FIX-P1-SUBSTITUTION-CONCURRENCY-2026-09-11 / P1 Phase 4
- Date: 2026-09-11
- Owner module: Academic / Scheduling and Attendance substitution
- Change class: `SECURITY_GLOBAL`
- Expected write scope: `SubstitutionService`, direct substitution regression tests, Work Log, and this impact/manifest record
- Authorization: Existing `AcademicAuthorizationService` is used for actor authority when an actor user is supplied; no new RBAC policy or permission was added.
- State safety: Target session is locked and state-rechecked inside the transaction; COMPLETED, CANCELLED, and RESCHEDULED are rejected.
- Conflict coverage: Active overlapping sessions are checked through teaching assignments and EXPECTED SUBSTITUTE participations.
- Serialization: PostgreSQL `pg_advisory_xact_lock(hashtext('academic-substitution:' || teacher_id))` serializes candidate replacement operations; SQLite skips the PostgreSQL-only lock and is not claimed as real concurrency proof.
- Existing exclusion constraint: `class_sessions_active_no_overlap` protects class/time overlap only; it does not protect candidate teacher substitution overlap.
- Migration/schema impact: None.
- Decision/status: COMPLETED; P1 Phase 5 not started
