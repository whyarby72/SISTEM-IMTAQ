# Change Impact Record

- Request / problem: Close the TeacherAttendanceService TOCTOU race between session-state precheck and teacher-attendance mutation.
- Change ID / Task ID: FIX-P1-TEACHER-ATTENDANCE-CONCURRENCY-2026-09-11 / P1 Phase 3
- Date: 2026-09-11
- Owner module: Academic / Teacher Attendance
- Change class: `SECURITY_GLOBAL`
- Affected modules/workstreams: Teacher attendance service and its direct regression tests
- Expected file/write scope: TeacherAttendanceService, TeacherAttendanceServiceTest, Work Log, and this impact/manifest record
- Protected zones touched: No migration, schema, seed execution, schedule, UI, RBAC expansion, substitution, or correction workflow
- Runtime boundary: Every write transaction locks ClassSession first, rechecks resource state, validates participation ownership, evaluates Phase 2 authorization, then locks SessionTeacherParticipation and writes audit/mutation atomically.
- Lock-order audit: PASS for TeacherAttendanceService, StudentAttendanceDraftService, and StudentAttendanceFinalizer: ClassSession → dependent rows.
- Database/concurrency impact: No schema change. SQLite test harness cannot prove PostgreSQL row-lock behavior; real PostgreSQL concurrency proof is deferred to staging.
- Decision/status: COMPLETED; P1 Phase 4 not started
