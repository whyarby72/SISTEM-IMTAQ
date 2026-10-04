# Academic Web Grade Workflow Surface Design — 2026-10-04

## Closeout

- Project: `SISTEM-IMTAQ`
- Task: `ACADEMIC-WEB-GRADE-WORKFLOW-SURFACE-DESIGN`
- Type: design-only
- Starting repository HEAD: `970f8f801c412dc6092b8c65d152d0f6dc1f634d`
- Design basis: existing source at the starting HEAD; no database was queried or changed.
- Application source changed: `NO`
- Database write: `NONE`
- Migration/seed/import: `NONE`
- Decision: `GRADE_WORKFLOW_READY_FOR_IMPLEMENTATION`
- Next atomic task: `ACADEMIC-WEB-GRADE-WORKFLOW-G1-AUTHORIZATION-READ-SURFACE`

The D1 ratification below resolves the prior policy gaps. The repository still does not contain the grade web surface and the entry-state hardening is an implementation-critical requirement; neither is silently treated as already implemented. No production-ready claim is made.

## 1. Explicit scope and denominator

The Academic Web completion denominator remains the accepted ten-capability denominator from `ACADEMIC-WEB-COMPLETION-REVIEW-2026-09-29.md`. This design evaluates only capability 5, semester grades, and does not recalculate the whole product percentage.

| Capability | Current accepted classification | This task result |
|---|---|---|
| 1. Authentication/session entry | `COMPLETE_EVIDENCED` | unchanged |
| 2. Wali daily attendance workflow | `COMPLETE_EVIDENCED` at application level; pilot/UAT gates remain separate | unchanged |
| 3. Waka attendance monitoring/correction | `COMPLETE_EVIDENCED` at application level | unchanged |
| 4. Attendance report/export | `COMPLETE_EVIDENCED` | unchanged |
| 5. Semester grade workflow | `PARTIAL` | backend contract mapped; web surface absent |
| 6. Report card | `PARTIAL` | existing services only; not widened here |
| 7. Transcript/history | `PARTIAL` | existing services only; not widened here |
| 8. Dashboard | `PARTIAL` | grade metrics are dashboard consumers, not grade entry UI |
| 9. RBAC/audit/data quality/correction/lock | `PARTIAL` | grade service controls mapped; web authorization surface absent |
| 10. Deployment/operational readiness | `GOVERNANCE_BLOCKED` / separate gate | unchanged |

The authoritative product denominator is therefore still `4/10 = 40% COMPLETE_EVIDENCED`; no `~97%` estimate is used. This design changes no completion count.

## 2. Source reconnaissance and capability map

### 2.1 Canonical grade grain

The canonical write fact is one `semester_subject_grades` row per:

`student_id + semester_id + subject_id`

The database unique constraint enforces that grain. The row carries `score` nullable, `grade_source` (`DIRECT_ENTRY` or `IMPORTED`), optional teaching-assignment provenance, optional responsible staff, `workflow_status`, optimistic `version_no`, and entered/updated/finalized actor/time fields. `SemesterSubjectGrade` is the only grade fact model identified in the source.

Evidence:

- `application/web/app/Domains/Academic/Models/SemesterSubjectGrade.php`
- `application/web/database/migrations/2026_09_03_000021_create_semester_subject_grades_table.php`

### 2.2 Existing lifecycle

The implemented lifecycle is:

`DRAFT → CHECKED → LOCKED`

`SemesterGradeEntryService::save()` creates or version-increments a DRAFT, accepts an explicit zero, rejects scores outside 0–100, validates `grade_source`, validates assignment provenance, uses row locking, and emits `SEMESTER_SUBJECT_GRADE_SAVED`.

`SemesterGradeFinalizationService::check()` permits an effective Wali Kelas only when the grade is a complete DRAFT with a non-null score and the student is enrolled in the supplied class for the semester. It increments the version and emits `SEMESTER_SUBJECT_GRADE_CHECKED`.

`SemesterGradeFinalizationService::approveAndLock()` permits Waka Akademik only for a complete CHECKED grade, increments the version, records finalized actor/time, and emits `SEMESTER_SUBJECT_GRADE_LOCKED`.

There is no source evidence of a separate grade-level `FINALIZED` status; `LOCKED` is the terminal grade state currently implemented. The web design must use that vocabulary and not introduce `FINALIZED` as if it already existed.

### 2.3 Completeness and missing semantics

`SemesterGradeCompletenessService::check()` derives expected students from active `StudentClassEnrollment` rows across active teaching assignments and reports expected, available, missing, and `is_complete`. Availability requires a non-null score. A missing grade is not a zero; the entry UI must preserve an empty value as missing.

`GradeSemanticMetricsService` is a read consumer for dashboard/export metrics. It is not a write path and must not be used as an authorization substitute for the grade grid.

### 2.4 Correction and immutability

`SemesterGradeCorrectionRequestService::request()` creates a `CorrectionRequest` with status `PENDING`, expected version, requested changes, reason, and an audit event. It validates reason, version, score range, and allowed fields. The source contains no grade-specific review/apply service for a pending request. Consequently, the implementation must not invent a Waka approval action or directly update a locked grade until a management decision and a canonical apply service exist.

Locked report-card subject lines, report-card versions, transcript lines, and transcript versions are append-only/immutable in their models. Corrections after publication must be represented by an explicitly governed new version, not an in-place historical edit.

Evidence:

- `application/web/app/Domains/Academic/Services/SemesterGradeCorrectionRequestService.php`
- `application/web/app/Shared/Platform/Audit/Services/CorrectionRequestService.php`
- `application/web/app/Domains/Academic/Models/ReportCardVersion.php`
- `application/web/app/Domains/Academic/Models/ReportCardSubjectLine.php`
- `application/web/app/Domains/Academic/Models/AcademicTranscriptVersion.php`
- `application/web/app/Domains/Academic/Models/AcademicTranscriptLine.php`

## 3. Role and scope contract

| Action | Required actor/source contract | Resource/data scope | Current evidence |
|---|---|---|---|
| View semester/class/subject grade worklist | authenticated Academic actor with a new grade feature and class/assignment scope | selected semester; class and subject rows only | web route/controller absent; policy decision required |
| Save DRAFT | designated grade inputter; canonical service | student enrollment in selected class, semester, subject; assignment provenance must match | service exists, actor authorization at service boundary is incomplete |
| Check | `WALI_KELAS`, effective staff link + active homeroom + student enrollment | class/student/semester supplied to `check()` | implemented in `SemesterGradeFinalizationService` |
| Approve and lock | `WAKA_AKADEMIK` | grade record and its version | implemented in `approveAndLock()` |
| Request correction | authenticated actor with reason and expected version | one grade row; no direct mutation | request service exists; explicit permission/scope not encoded |
| Review/apply correction | management-owned approver/applicator | locked grade and pending request | **not evidenced; management decision required** |
| Read report-card readiness | read-only consumer | student/class/semester | service exists |
| Create report-card draft | existing report-card service contract | locked grades and attendance for student/class/semester | service exists; web surface absent |
| Review/publish report card | Wali review; Waka approve/publish; head-of-school and Wali snapshots | report-card version | service exists; web surface absent |
| Read/create/review/publish transcript | existing transcript service roles | student history/version | service exists; web surface absent |

The backend currently authorizes some lifecycle transitions by role, but `SemesterGradeEntryService::save()` itself does not prove Wali/teacher/class scope. A controller must not treat a feature flag as sufficient. The implementation slice must either add a dedicated authorization service/policy before exposing save, or prove an existing authorization boundary that supplies the same checks. This is a design gate, not an application change in this task.

### 3.1 Management decisions required before implementation

1. **Input owner:** choose whether grade DRAFT input is Wali Kelas only, teaching staff by teaching assignment, or a controlled combination. The current services support provenance and responsible staff but do not define a web input role.
2. **Correction authority:** define who reviews and who applies a pending `SEMESTER_SUBJECT_GRADE` correction, whether the same actor is forbidden from approving their own request, and whether a correction creates a new grade version or a new grade fact/versioned correction record. Until this is decided, correction request submission may be surfaced only as read/request state, not as an apply action.
3. **Feature/permission:** add/approve a canonical feature code and permission mapping for the grade surface. A candidate is `academic.grades`, but it is not present in `UserAccessFeatureSeeder` and must not be treated as authoritative until approved.
4. **Class scope:** confirm whether Waka may view all classes and Wali only effective homeroom classes, and how a teacher’s assignment scope intersects a class with multiple assignments.

## 4. Proposed information architecture

This is a proposed surface derived from existing contracts; route names are implementation targets, not existing routes.

`Academic → Nilai Semester`

1. Semester selector (only active/authorized semesters).
2. Class selector constrained by actor scope.
3. Subject/teaching-assignment selector constrained by selected semester/class.
4. Grade worklist/grid.
5. Completeness and lifecycle summary.
6. Review/lock queue for Waka.
7. Correction history/request state.

Candidate routes, to be confirmed in implementation design:

| Method | Candidate route | Purpose |
|---|---|---|
| GET | `/academic/grades` | semester/class/subject worklist |
| GET | `/academic/grades/{grade}` | grade detail/history |
| POST | `/academic/grades/batch-draft` | batch DRAFT saves through canonical service |
| POST | `/academic/grades/{grade}/check` | Wali check |
| POST | `/academic/grades/{grade}/lock` | Waka approve/lock |
| POST | `/academic/grades/{grade}/corrections` | correction request only |
| GET | `/academic/grades/corrections` | pending/history queue; mutation absent until authority exists |

These routes must use `auth`, `active.account`, a dedicated approved feature gate, CSRF, request validation, actor scope checks, optimistic version fields, and resource-state checks. No route may call `Model::create()` for grade writes or bypass `SemesterGradeEntryService`.

## 5. Grade grid contract

The primary UI is a batch-oriented class/subject grid, but each submitted cell maps to the canonical individual service call or a new transaction wrapper that delegates to it. The UI must not create a second batch persistence model.

Required columns and states:

- canonical `student_id`/student code as the stable identity; display name is presentation only;
- current score, blank when missing;
- grade source/provenance indicator when present;
- responsible staff/assignment indicator when present;
- workflow state: DRAFT, CHECKED, LOCKED;
- version token for stale-write rejection;
- validation errors per student/subject cell;
- audit/save result summary.

Required behavior:

- blank is missing, never auto-converted to `0`;
- `0` is an explicit valid score;
- score range is 0–100 with server-side validation authoritative;
- Save Draft is explicit and idempotent per canonical grain;
- stale version is reported as a conflict and reloaded, not silently overwritten;
- CHECKED/LOCKED cells are read-only in the entry grid;
- no auto-check or auto-lock after save;
- completeness uses `SemesterGradeCompletenessService` and clearly separates missing from zero;
- responsive table/grid must remain keyboard navigable, label each input by canonical student and subject, expose errors and saved state to assistive technology, and provide a non-table mobile fallback or horizontal-scroll affordance.

## 6. Controller/service implementation boundary

### 6.1 Read controller responsibilities

The future controller should resolve and validate semester, class, subject/assignment, and actor scope, then call a dedicated read/query service. It should not embed enrollment or Wali authorization queries in Blade. The query service should return canonical IDs, subject/assignment metadata, grade rows, completeness, and allowed transitions.

### 6.2 Write controller responsibilities

The future controller should validate only transport shape, pass the actor and expected versions into a dedicated authorization/application service, and translate domain exceptions into field-level/session-safe responses. The application service should execute a transaction and delegate grade writes to `SemesterGradeEntryService`, preserving optimistic version and audit behavior.

`SemesterGradeFinalizationService` remains the transition authority for Wali check and Waka lock. The controller must not update `workflow_status`, `version_no`, `finalized_by`, or timestamps directly.

### 6.3 Correction boundary

The first implementation slice may expose `SemesterGradeCorrectionRequestService::request()` as a request-only flow after scope/permission approval. It must not expose “approve”, “apply”, or “edit locked grade” because no canonical apply service is present. A later slice needs a separate service with transaction locking, expected-version validation, self-approval prohibition, append-only audit, and immutable historical/version behavior.

## 7. Validation, finalization, audit, and downstream lineage

### 7.1 Validation

Transport validation: UUID existence, semester/class/subject relationship, numeric score nullable and 0–100, allowed source enum, expected version integer, and assignment provenance. Domain validation remains in services and PostgreSQL checks. Scope validation must prove effective enrollment/assignment before save.

### 7.2 Finalization

The implementation must present `CHECKED` as Wali review and `LOCKED` as Waka approval/finalization. Lock is per grade row in the current backend; a future bulk lock must be an explicit transaction over a deterministic set with per-row version and all-or-nothing/partial failure policy decided before implementation. No monthly or semester “publish” action may be simulated by merely changing grade status.

### 7.3 Audit

Reuse existing audit actions:

- `SEMESTER_SUBJECT_GRADE_SAVED`
- `SEMESTER_SUBJECT_GRADE_CHECKED`
- `SEMESTER_SUBJECT_GRADE_LOCKED`
- `SEMESTER_SUBJECT_GRADE_CORRECTION_REQUESTED`

Audit payloads must retain actor, canonical entity type/id, version before/after, state transition, and safe old/new values. Do not add student-name-only identity or free-form client metadata as authority.

### 7.4 Downstream

`ReportCardReadinessService` requires enrollment, complete locked grades by active teaching assignment, and locked attendance periods. `ReportCardDraftService` snapshots locked subject grades and attendance into append-only report-card versions. `ReportCardPublicationService` transitions DRAFT → REVIEWED → APPROVED → PUBLISHED and records signatories; its Wali/Waka boundaries are existing backend evidence.

`AcademicHistoryService` reads only LOCKED non-null grades. `AcademicTranscriptDraftService` snapshots that history, while `AcademicTranscriptPublicationService` reviews/publishes and allows a Super Admin revision path. The grade UI must link to these downstream readiness states without implying publication when only a grade is saved or checked.

## 8. Security, DQ, locks, and failure states

- Backend authorization is authoritative; hiding controls in Blade is insufficient.
- Scope is based on canonical User/Staff links, effective roles, homeroom assignment, teaching assignment, and student enrollment—not names.
- Every write carries expected version; stale version is a conflict.
- Database unique grain and score/source checks remain enabled.
- No deletion of grade facts or snapshot history.
- Missing grades remain distinguishable from score zero.
- Lock state must disable mutation except a governed correction path.
- Error responses must not reveal unrelated students, credentials, or internal SQL.
- Empty, partial, invalid, stale, unauthorized, locked, and correction-pending states need distinct UI copy and HTTP/session behavior.
- Joint classes must use the existing effective enrollment/assignment semantics; the UI cannot duplicate or merge students by display name.

## 9. MVP and later slices

### MVP implementation slices

1. Resolve management decisions; approve feature/permission and input-owner matrix.
2. Add grade query/controller authorization boundary and read-only worklist.
3. Add single-cell Save Draft through `SemesterGradeEntryService` with optimistic version and audit.
4. Add batch grid orchestration that delegates per-row canonical saves; no second grade grain.
5. Add Wali CHECKED action through `SemesterGradeFinalizationService`.
6. Add Waka LOCKED action through `SemesterGradeFinalizationService`.
7. Add completeness/readiness panels and downstream links.
8. Add correction-request-only surface, if approved.

### Later / explicitly deferred

- correction approval/apply service and its policy;
- bulk lock semantics;
- report-card/transcript web pages if not already exposed by a future task;
- grade import workflow beyond the existing `IMPORTED` field;
- rubric/assessment-component grades, weighted calculations, letter-grade conversion, and teacher self-service unless separately specified;
- public/parent grade publication;
- new database schema unless a reviewed implementation design proves it necessary.

## 10. Test and regression matrix for implementation

### Focused domain tests

- save new DRAFT, update with expected version, explicit zero, null/missing, score bounds, source enum, provenance mismatch;
- unique grain and duplicate concurrent save behavior;
- completeness expected/available/missing and transfer deduplication;
- Wali check allowed/denied by role, staff link, class, enrollment, state, and stale version;
- Waka lock allowed/denied by role, state, score, and stale version;
- correction request reason/version/allowed fields/audit;
- locked/report-card/transcript append-only protections;
- report-card readiness and transcript history consume only LOCKED grades.

### Web/RBAC tests

- unauthenticated and inactive-account denial;
- feature/permission denial for every candidate route;
- Wali class scope and Waka cross-class scope according to the approved matrix;
- CSRF, validation, stale conflict, partial batch result, and safe error rendering;
- no direct model write in controller; audit event present for every state-changing request;
- keyboard labels, error association, saved state, and responsive grid behavior.

### Regression gates

Run focused grade tests, Academic suite, Shared Core/RBAC/audit tests, report-card/transcript consumers, full foundation PostgreSQL suite, view cache, PHP lint, Pint, and exact CI on the implementation commit. This design task runs no tests that mutate a database and claims no implementation pass.

## 11. Exact future file scope

The following is a proposed implementation scope, not a change made here:

- `application/web/routes/web.php` — approved grade routes only;
- new `application/web/app/Http/Controllers/Academic/*Grade*Controller.php` or repository-approved equivalent;
- new grade read/scope/application service(s) under `application/web/app/Domains/Academic/Services/`;
- optional Policy/FormRequest classes under existing conventions;
- new `application/web/resources/views/academic/grades/` views and minimal shared CSS/JS only if required;
- `application/web/database/seeders/UserAccessFeatureSeeder.php` only after feature/permission decision;
- focused feature tests under `application/web/tests/Feature/Academic/`;
- a Change Manifest and task context for the implementation slice.

No migration is currently required by the mapped contract. Any schema need must stop for a separate design/approval.

## 12. Final decision

`GRADE_WORKFLOW_READY_FOR_IMPLEMENTATION`

Reason: D1 ratifies process ownership, subject-teacher input authority, correction authority/application semantics, the `academic.grades` feature registry, the role/action contract, and the canonical lifecycle. The web surface remains unimplemented by design, and the normal-save state hardening is explicitly implementation-critical.

## DECISION RATIFICATION D1 — 2026-10-04

### Ratified authority

- Process owner: `WAKA_AKADEMIK` owns semester-grade operational completion, final approval/locking, correction approval, and exception monitoring.
- Primary inputter: the assigned subject teacher, resolved through authenticated User → effective UserStaffLink → Staff → ACTIVE TeachingAssignment → exact semester/subject/class. A Wali is not automatically a subject-grade inputter; a Wali who is also the assigned subject teacher may qualify independently through that teaching assignment.
- Checker: effective `WALI_KELAS`, using the existing class and student enrollment checks in `SemesterGradeFinalizationService::check()`.
- Final approver/locker: `WAKA_AKADEMIK`, using `SemesterGradeFinalizationService::approveAndLock()`. Super Admin is not granted operational grade approval by this ratification.
- Correction requester: effective assigned subject teacher for the relevant class/subject/semester, or effective Wali for the enrolled student/class/semester. The request requires expected version, reason, proposed fields, and audit evidence and does not mutate the grade.
- Correction reviewer: `WAKA_AKADEMIK`; the requester cannot approve their own request.

### Ratified correction application

The MVP requires a dedicated atomic `approveAndApplyCorrection()` domain service. In one transaction it must lock the request and grade, require `PENDING` and `LOCKED`, revalidate expected version and fields, apply the approved changes, increment the grade version, retain `workflow_status = LOCKED`, mark reviewer/reviewed/applied timestamps, and append audit evidence. Rejection must set `REJECTED`, require a rejection reason, record reviewer/time, and leave the grade unchanged. No direct locked-grade edit path is allowed.

### Feature and permission

Ratified feature registry:

```text
code: academic.grades
name: Nilai Semester
module: Academic
required_permission: NULL
default_enabled: true
system_enabled: true
is_toggleable: true
```

No new permission code is required for grade entry in D1. `FeatureAccessResolver` is only one layer: effective write authorization remains feature enabled + active account + assignment/role authority + exact resource/class/subject scope + workflow state. An enabled feature override must not bypass these controls.

### Ratified action matrix

| Action | Assigned subject teacher | Wali Kelas | Waka Akademik | Super Admin |
|---|---|---|---|---|
| `GRADE_VIEW` | assigned scope | own class | management scope | no new authority ratified |
| `GRADE_ENTER_DRAFT` | assigned scope | only if independently assigned teacher | management scope only if separately implemented | no new authority |
| `GRADE_CHECK` | no | complete DRAFT in own class | no shortcut | no |
| `GRADE_APPROVE_LOCK` | no | no | yes | no |
| `GRADE_CORRECTION_REQUEST` | assigned locked grade | own-class locked grade | management scope if needed | no new authority |
| `GRADE_CORRECTION_REVIEW` | no | no | yes | no |

### Ratified lifecycle and hardening

The canonical lifecycle is `DRAFT → CHECKED → LOCKED`. Normal save is allowed only for a new row or an existing `DRAFT`. Existing `CHECKED` or `LOCKED` rows must fail closed on normal save; they must never be returned to DRAFT. Post-lock changes use the correction workflow and preserve the locked final-state semantics.

`SemesterGradeEntryService` currently permits an existing grade update without an explicit DRAFT-state rejection. This is an `IMPLEMENTATION_CRITICAL` hardening gap for G2. D1 does not patch it.

### AI boundary

AI is not required. AI must not generate or suggest scores, finalize grades, approve corrections, or become a source of truth. Public Academic AI remains `OFF`.

### Final implementation slices

| Slice | Scope | Recommended model |
|---|---|---|
| G1 | authorization contract, `academic.grades` registry, read surface/worklist | GPT-5.6 Luna — Medium |
| G2 | DRAFT batch entry, canonical save orchestration, `DRAFT-only` hardening | GPT-5.6 Sol — Medium |
| G3 | completeness and Wali CHECKED workflow | GPT-5.6 Sol — Medium |
| G4 | Waka approve/LOCK workflow | GPT-5.6 Sol — Medium |
| G5 | correction request plus Waka approve/reject/apply service | GPT-5.6 Sol — High |
| G6 | end-to-end regression and Academic completion review | GPT-5.6 Luna — Medium |

No migration, seed, PILOT write, or application source change is authorized by D1. G1 is the next atomic implementation task, but must be started only after ChatGPT/project-owner audit of this ratification.

## 13. Safety closeout

- Source files modified: `NONE`
- Database write: `NONE`
- Pilot/staging/production access: `NONE`
- Migration/seed/import: `NONE`
- AI/provider/Public Academic AI: unchanged; Public Academic AI remains `OFF`
- `IMP-S12-007`: unchanged, `NOT_STARTED`
- `SOC-MD-06`: unchanged
- SAFE_TO_CLOSE: `YES`
