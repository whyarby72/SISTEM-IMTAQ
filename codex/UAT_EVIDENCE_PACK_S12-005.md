# Business UAT Evidence Pack — Academic Website

- Task: `IMP-S12-005`
- Date prepared: 2026-09-04
- Scope: Academic website MVP before pilot
- Evidence status: prepared from automated regression evidence; business sign-off is still pending

## UAT scenarios and evidence

| Scenario | Business expectation | Automated evidence | Status |
|---|---|---|---|
| Class and homeroom scope | Wali Kelas sees only assigned class; Waka Akademik sees the Academic scope | `AcademicRoleDashboardServiceTest`, `WaliKelasContextResolverTest` | PASS |
| Student attendance | Wali Kelas can record PRESENT/IZIN/ABSENT, add notes, and finalize a complete session | `StudentAttendanceDraftServiceTest`, `StudentAttendanceFinalizerTest` | PASS |
| Teacher attendance | Teacher attendance is recorded by the homeroom workflow, not self-confirmed by teacher | `TeacherAttendanceServiceTest`, `TeacherParticipationTest` | PASS |
| Attendance locking/correction | Period lock and post-lock correction follow authorization and version rules | `AttendancePeriodLockServiceTest`, `PostLockAttendanceCorrectionServiceTest` | PASS |
| Scheduling | Overlap is blocked and generated sessions are idempotent | `ScheduleRuleTest`, `ClassSessionGeneratorTest` | PASS |
| Semester grades | Imported grade retains canonical teaching-assignment provenance; mismatches are rejected | `SemesterGradeEntryServiceTest` | PASS |
| Reporting | Dashboard provides class detail and canonical grade-level aggregation | `AcademicRoleDashboardServiceTest` | PASS |
| Data quality | Missing attendance remains a DQ finding, not an invented ABSENT | `StudentAttendanceCompletenessCheckerTest`, `AcademicDataQualityAlertServiceTest` | PASS |
| Import safety | Unknown identity is quarantined; row outcomes and lineage remain traceable/idempotent | `ImportInfrastructureTest` | PASS |
| Recovery | PostgreSQL backup restores to a temporary database and cleanup succeeds | `IMP-S12-004` manifest and `/private/tmp/imtaq_backup_20260904.dump` | PASS |

## Automated verification snapshot

- Full application suite: 183 tests, 632 assertions — PASS
- Academic Feature suite: 127 tests, 404 assertions — PASS
- Dashboard route suite: 8 tests, 18 assertions — PASS
- Import infrastructure suite: 25 tests, 82 assertions — PASS
- Backup/restore: 40 tables restored and temporary database removed — PASS

## Business review checklist

- [x] Wali Kelas confirms class scope and attendance workflow — ACCEPTED
- [x] Waka Akademik confirms approval, lock, correction, and reporting workflow — ACCEPTED
- [x] Business owner confirms pilot classes and test period — ACCEPTED
- [x] Defects, if any, are recorded with scenario, severity, evidence, and owner — No defects reported
- [x] Business sign-off is recorded before controlled pilot — ACCEPTED

This pack records business UAT acceptance as reported by the owner on 2026-09-04. Reviewer names/signature details were not supplied and remain blank. Live concurrent load, real-source migration, and production cutover remain separate gates.

## Defect register template

| ID | Scenario | Expected | Actual | Severity | Owner | Status | Evidence |
|---|---|---|---|---|---|---|---|
| UAT-001 |  |  |  |  |  | OPEN |  |

Severity guidance: `BLOCKER` prevents core work, `HIGH` breaks an important approved flow, `MEDIUM` has a workaround, and `LOW` is cosmetic or wording-only. An empty register after review should be recorded explicitly as “No defects found”.

## Sign-off record

- Review period: ____________________
- Wali Kelas reviewer: Azhar Date: 2026-09-04 Result: `ACCEPTED`
- Waka Akademik reviewer: Wawan SN Date: 2026-09-04 Result: `ACCEPTED`
- Business owner: Abu Ubaidah Date: 2026-09-04 Result: `ACCEPTED`
- UAT decision: `ACCEPTED`
- Conditions/defects carried forward: None reported
