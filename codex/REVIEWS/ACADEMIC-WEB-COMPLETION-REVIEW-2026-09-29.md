# Academic Web Completion Review — 2026-09-29

## Executive completion statement

`ACADEMIC_WEB_COMPLETION_REVIEW = COMPLETED / REVIEW_ONLY / PASS`

This is an evidence review of the current repository, not a production-cutover
approval. The Academic Web has a strong operational attendance and scheduling
surface, but it is not complete as an end-to-end Academic product: semester
grades, report-card workflows, and transcript/history have backend services and
tests without corresponding web routes/views in the current route inventory.
The current monthly-report UI is explicitly tied to July 2026 summary/snapshot
lineage and therefore is not evidence of a canonical all-period publication
surface.

`IMPLEMENTATION_AUTHORIZATION = NOT_AUTHORIZED`
`IMP-S12-007 = NOT_STARTED / NOT_ACTIVATED`
`PUBLIC_ACADEMIC_AI = OFF`

## Review baseline and explicit denominator

Baseline: branch `chore/academic-web-completion-review-routing`, HEAD
`cdcf608ac0f5ca4fef7e4d68d7a388447f7a3b28`.

Foundation entry evidence is closed: exact run `36484084952` on
`dfaf585c299aa83fccef7be95d746f943242c192` passed PostgreSQL 18.6 readiness,
UTC session, identity guard, migration-from-zero, schema checks, and foundation
verification (`15 passed, 506 warnings, 2202 assertions, 0 failed`).

The denominator is **10 in-scope review capabilities**, one row per coherent
Academic Web product capability:

1. student attendance;
2. teacher attendance;
3. schedule/session lifecycle;
4. substitution, swap, reschedule, cancellation, and extra sessions;
5. semester subject grades;
6. report-card workflow;
7. transcript and academic history;
8. dashboard, monthly report, and exports;
9. RBAC, audit, correction, lock, and data quality controls;
10. role-specific responsive web usability.

Excluded items are not denominator rows: Parent Portal, WhatsApp,
biometrics, cross-domain Tahfizh/Kesantrian, future assessment engines, future
AI expansion, and production deployment/cutover.

## Capability matrix

| # | Capability | Backend evidence | UI/route evidence | Test evidence | Status | Gap/risk |
|---:|---|---|---|---|---|---|
| 1 | Student attendance | `StudentAttendanceDraftService`, `StudentAttendanceFinalizer`, `StudentAttendanceCompletenessChecker`, `StudentAttendanceCorrectionService` | `academic.attendance.show`, draft/finalize/correction routes; `academic/attendance/show.blade.php` | `StudentAttendanceDraftServiceTest`, `StudentAttendanceFinalizerTest`, `StudentAttendanceCompletenessCheckerTest`, `StudentAttendanceCorrectionServiceTest`, `StudentAttendanceUiTest` | `COMPLETE_EVIDENCED` | Canonical occurrence regime is separately governed; transitional completed semantics remain documented. |
| 2 | Teacher attendance | `TeacherAttendanceService`, `TeacherParticipationRecorder`, teacher obligation lock services | `POST /academic/attendance/{session}/teacher-attendance` and attendance show UI | `TeacherAttendanceServiceTest`, `TeacherParticipationTest`, `TeacherObligationLockServiceTest`, finalization coverage | `COMPLETE_EVIDENCED` | Evidence is session-oriented; no separate teacher portal is required by current scope. |
| 3 | Schedule/session lifecycle | `ClassSessionGenerator`, `ScheduleRule*`, `SessionOccurrence*`, publication validators | Admin schedule CRUD, official validate/publish, schedule archive routes and views; attendance session view | `ScheduleRuleTest`, `ScheduleRuleRevisionServiceTest`, `ClassSessionGeneratorTest`, `TeacherSchedulePublicationValidatorTest`, occurrence persistence/cutover tests | `COMPLETE_EVIDENCED` | Controlled-pilot cutover and non-eligible authority remain governance-bounded. |
| 4 | Substitution/swap/reschedule/cancellation/extra | `SubstitutionService`, `SwapService`, `RescheduleService`, `CancellationService`, `ExtraSessionCreator` | Attendance session actions plus Waka exception/bulk-cancel surface | `SubstitutionServiceTest`, `SwapServiceTest`, `RescheduleServiceTest`, `CancellationServiceTest`, `ExtraSessionCreatorTest`, UI attendance tests | `COMPLETE_EVIDENCED` | Joint-session scope and lineage require continued operational data-quality monitoring. |
| 5 | Semester subject grades | `SemesterGradeEntryService`, `SemesterGradeFinalizationService`, `SemesterGradeCompletenessService`, correction service | No grade-entry/finalization route or grade Blade view found in `routes/web.php` and `resources/views` inventory | `SemesterGradeEntryServiceTest`, `SemesterGradeFinalizationServiceTest`, `SemesterGradeCompletenessServiceTest`, `SemesterGradeCorrectionRequestServiceTest` | `PARTIAL` | Backend contract is evidenced; authorized web input/review path is not evidenced. |
| 6 | Report-card workflow | `ReportCardDraftService`, `ReportCardReadinessService`, `ReportCardNoteService`, `ReportCardPublicationService` | No report-card draft/review/approve/publish route or Blade view found; monthly attendance report is a different artifact | `ReportCardDraftServiceTest`, `ReportCardReadinessServiceTest`, `ReportCardNoteServiceTest`, `ReportCardPublicationServiceTest` | `PARTIAL` | Backend lifecycle exists; UI and end-to-end role path are not evidenced. |
| 7 | Transcript/history | `AcademicHistoryService`, `AcademicTranscriptDraftService`, `AcademicTranscriptPublicationService` | No transcript/history route or Blade view found in current web inventory | `AcademicHistoryServiceTest`, `AcademicTranscriptDraftServiceTest`, `AcademicTranscriptPublicationServiceTest` | `PARTIAL` | Backend derived history and publication services exist; web consumption and signatory UX are not evidenced. |
| 8 | Dashboard/monthly report/exports | `AcademicRoleDashboardService`, `AcademicDashboardExportService`, `MonthlyAttendanceReportExportService`, semantic metrics services | `/academic/dashboard`, dashboard CSV, July 2026 monthly-report index/detail/CSV/PDF, admin publish routes; corresponding dashboard/report views | `AcademicRoleDashboardServiceTest`, dashboard UI/export tests, monthly report tests and canonical semantic metric tests | `PARTIAL` | Dashboard/export path is evidenced, but monthly report controller reads `MonthlyAttendanceSummary` and optionally `MonthlyStudentAttendanceSnapshot`; canonical live per-session publication coverage is not evidenced for all periods. |
| 9 | RBAC/audit/correction/lock/DQ | `AcademicAuthorizationService`, audit logger, correction services, `AttendancePeriodLockService`, alert/DQ services | Authorization is enforced in controllers for Wali/Waka/Super Admin paths; correction/review UI exists for attendance | `AcademicAuthorizationServiceTest`, `AttendancePeriodLockServiceTest`, post-lock correction, audit/core, DQ/alert tests | `COMPLETE_EVIDENCED` | Publication/signatory policy for grade/report/transcript workflows remains a product/UI gap, not a proven global RBAC failure. |
| 10 | Role-specific responsive web usability | Attendance and dashboard/report Blade surfaces exist for current operational paths; admin academic CRUD views exist | Wali attendance route; Waka dashboard/review/report routes; Super Admin/admin Academic routes | `StudentAttendanceUiTest`, `AttendanceExceptionUiTest`, dashboard/report and authorization tests | `PARTIAL` | Wali/Waka operational attendance is evidenced; grade/report-card/transcript role journeys are not exposed as complete web workflows. |

## Denominator-derived completion

Complete evidenced rows: **4 / 10 = 40%**.

Partial rows: **6 / 10**. Governance-blocked, technical-blocked, deferred, and
not-evidenced rows are not silently counted as complete. No `~97%` estimate is
used.

Critical end-to-end workflows require transaction + validation + authorization
+ audit/versioning + user-facing path. On that stricter measure:

- critical workflows complete: student attendance, teacher attendance,
  schedule/session lifecycle, and session changes = **4 / 10 = 40%**;
- grades, report cards, transcript/history, and canonical reporting remain
  incomplete because a web path or canonical report consumer is missing.

## Critical workflow matrix

| Workflow | Transaction | Validation/lifecycle | Authorization | Audit/versioning | User-facing path | Result |
|---|---|---|---|---|---|---|
| Student attendance | Draft and final attendance services | Completeness, finalize, lock/correction | Wali scope; Waka/Super Admin review | Audit and correction/version services | Attendance show, review, correction routes/views | `COMPLETE_EVIDENCED` |
| Teacher attendance | Teacher participation recorder/service | Status and substitute prerequisites | Scoped Wali plus full Academic authority | Audit through attendance workflow | Attendance session action | `COMPLETE_EVIDENCED` |
| Schedule/session changes | Rule/session generator and change services | Conflict, lineage, status checks | Full Academic authority; scoped session controls | Schedule change/occurrence history | Admin schedules plus attendance session actions | `COMPLETE_EVIDENCED` |
| Semester grade | Entry/finalization services | Completeness and lock/correction tests | Backend service tests only | Audit logger/version fields | No web route/view found | `PARTIAL` |
| Report card | Draft/readiness/note/publication services | Readiness and publish checks | Backend service boundary | Version/publication/signatory models | No report-card route/view found | `PARTIAL` |
| Transcript/history | History/transcript draft/publication services | Review/approve/publish services | Backend service boundary | Version/signatory/revision services | No transcript/history route/view found | `PARTIAL` |
| Monthly report | Summary/export controller and services | July mapping/publish checks | Wali viewer; Waka/Super Admin reviewer | Publish audit exists | July-only report UI/export | `PARTIAL` |

## Role/UI matrix

| Role | Evidenced usable path | Backend authority evidence | Completion finding |
|---|---|---|---|
| Wali Kelas | View/save/finalize scoped student and teacher attendance; view scoped July report | `WaliKelasContextResolver`, scoped controller checks, authorization tests | `COMPLETE_EVIDENCED` for attendance; `PARTIAL` for grades/reports/transcript |
| Waka Akademik | Dashboard, exception queue, review/export, schedule/admin operations, report review/publish | `hasAcademicFullAuthority`, controller gates, reviewer tests | `COMPLETE_EVIDENCED` for operational attendance/schedule; `PARTIAL` overall due academic publication surfaces |
| Super Admin | Admin Academic CRUD and system/provider settings | Full Academic authority plus Super Admin provider authorization | `PARTIAL` for Academic product because grade/report/transcript web workflows are absent; AI remains outside this review and OFF |

## Backend-vs-UI gap matrix

| Backend capability | Backend state | UI state | Classification |
|---|---|---|---|
| Semester grades | Services/models/tests present | No route/view found | `PARTIAL` — backend complete, UI missing |
| Report card | Draft/readiness/publication services/tests present | No route/view found | `PARTIAL` — backend complete, UI missing |
| Transcript/history | Services/tests present | No route/view found | `PARTIAL` — backend complete, UI missing |
| Live per-session attendance | Services/controllers/tests present | Route/view/export present | `COMPLETE_EVIDENCED` |
| Monthly report | Controller/export/view present | UI present | `PARTIAL` — lineage is July legacy summary/snapshot, not proven canonical all-period publication |

## End-to-end integrity, RBAC, audit, DQ, and lock findings

- Attendance uses session participants and student attendance services; missing
  attendance is not converted to ABSENT by the completeness tests.
- Student attendance finalization is transactional and audited; post-lock
  correction has a distinct request/review/apply path.
- Teacher attendance has substitute/finalization prerequisites and scoped
  authorization.
- Schedule changes use conflict/lineage services and dedicated tests for
  substitution, swap, reschedule, and cancellation.
- Waka/Super Admin reviewer gates are explicit in controllers; Wali scope is
  resolved through `WaliKelasContextResolver`.
- DQ alerts, attendance locks, audit/correction infrastructure, and semantic
  metrics are evidenced by services and tests.
- The current monthly report controller reads legacy
  `MonthlyAttendanceSummary` and checks `monthly_student_attendance_snapshots`;
  this is a lineage/product-completion limitation, not evidence to rewrite
  those tables in this review.
- 506 foundation warnings are non-blocking entry evidence; this review does
  not open broad warning cleanup without a material Academic finding.

## Governance blockers vs technical gaps

### Governance / policy boundaries

- `SOC-MD-06` remains the canonical management decision gate.
- `IMP-S12-007 Production cutover readiness review` remains `NOT_STARTED` and
  is not activated by this review.
- Session occurrence non-eligible authority, source precedence, lineage
  population, and historical backfill remain separately governed.
- Publication/signatory policy for grades, report cards, and transcripts is not
  evidenced as a complete authorized web product contract.

### Technical/product gaps

- No current web routes/views expose semester-grade entry/finalization.
- No current web routes/views expose report-card drafting, notes, review,
  approval, or publication.
- No current web routes/views expose transcript/history review or publication.
- Monthly reporting is fixed to July 2026 and is backed by legacy summary/
  snapshot models in the current controller.

## Deferred / out of scope

Parent Portal, WhatsApp/shared communications, biometrics, cross-domain
features, future assessment engine, future AI expansion, and production
deployment/cutover are `DEFERRED_OUT_OF_SCOPE` for this denominator.

## Exact evidence map

- Routes: `application/web/routes/web.php`.
- Attendance controllers/views: `application/web/app/Http/Controllers/Academic/StudentAttendanceController.php`, `AttendanceExceptionController.php`, `application/web/resources/views/academic/attendance/`.
- Dashboard/report: `AcademicDashboardController.php`, `Admin/MonthlyAttendanceReportController.php`, `AcademicDashboardExportService.php`, `resources/views/academic/dashboard.blade.php`, `resources/views/admin/academic/monthly-reports/`.
- Grade/report/transcript backend: `SemesterGrade*Service.php`, `ReportCard*Service.php`, `AcademicTranscript*Service.php`, `AcademicHistoryService.php` and corresponding Academic tests.
- RBAC/audit/DQ/lock: `AcademicAuthorizationService.php`, `AttendancePeriodLockService.php`, correction services, `AcademicDataQualityAlertService.php`, Shared Core audit/RBAC tests.
- Foundation: `codex/CHANGE_MANIFESTS/FOUNDATION-TZ-B1-2026-09-29.md`, exact Actions run `36484084952`, `PROJECT_STATE.json`, `EVIDENCE_INDEX.json`.

## Recommended next atomic task

**ONE recommendation:** `ACADEMIC-WEB-GRADE-WORKFLOW-SURFACE-DESIGN`

Design-only task to map the existing semester-grade backend contract to an
authorized Wali/Waka/Super Admin web workflow, including route/view ownership,
validation/finalization/correction states, audit expectations, and UAT cases.
It must not implement source, migrate, write database data, publish reports,
or activate production cutover. This is the smallest missing web surface that
blocks the next coherent Academic user workflow and has clear backend evidence.

## Closeout

`ACADEMIC_WEB_COMPLETION_REVIEW = COMPLETED / REVIEW_ONLY / PASS`
`IMPLEMENTATION_AUTHORIZATION = NOT_AUTHORIZED`
`DATABASE_WRITE = NONE`
`SOURCE_MUTATION = NONE`
`PRODUCTION_READINESS = NOT_ASSESSED / SEPARATE IMP-S12-007 GATE`

