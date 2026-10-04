# Academic Web Grade Workflow D1 Decisions — 2026-10-04

## Decision record

- Project: `SISTEM-IMTAQ`
- Task: `ACADEMIC-WEB-GRADE-WORKFLOW-DECISION-RATIFICATION-D1`
- Type: `DESIGN_GOVERNANCE_RATIFICATION`
- Basis HEAD: `970f8f801c412dc6092b8c65d152d0f6dc1f634d`
- Application source changed: `NO`
- PILOT database write: `NONE`
- Migration/seed: `NONE`
- Public Academic AI: `OFF`
- Decision: `GRADE_WORKFLOW_READY_FOR_IMPLEMENTATION`
- Next atomic task: `ACADEMIC-WEB-GRADE-WORKFLOW-G1-AUTHORIZATION-READ-SURFACE`

This record ratifies the open decisions from the surface design. It authorizes implementation planning/slices only after audit; it does not implement G1 or any grade UI.

## Ratified ownership and authority

### Process owner

`WAKA_AKADEMIK` owns semester-grade operational completion, final approval/locking, correction approval, and exception monitoring.

### Primary inputter

The assigned subject teacher is the primary grade inputter. Authority is resolved by:

`authenticated User → effective UserStaffLink → Staff_ID → ACTIVE TeachingAssignment → exact Semester + Subject + Class`

No name-based authority is allowed. No new `GURU` role code is required for this decision. A Wali Kelas is not automatically a subject-grade inputter; a Wali who independently satisfies the subject-teaching assignment may enter through that assignment.

### Checker and final approver

- `WALI_KELAS` checks complete DRAFT grades using existing effective Wali/class/student-enrollment semantics in `SemesterGradeFinalizationService::check()`.
- `WAKA_AKADEMIK` approves and locks through `SemesterGradeFinalizationService::approveAndLock()`.
- Super Admin receives no new operational grade authority from D1. Any break-glass path requires a separate decision.

## Correction decisions

### Requester

For a LOCKED grade, a correction request may be submitted by either the effective assigned subject teacher for the exact class/subject/semester or the effective Wali for the enrolled student/class/semester. The request requires expected grade version, explicit reason, proposed allowed fields, and audit evidence. Submission does not mutate the grade.

### Reviewer/approver

The reviewer/approver is `WAKA_AKADEMIK`. The requester must not approve their own request.

### Application

Implementation must add a dedicated atomic `approveAndApplyCorrection()` domain service. One database transaction must:

1. lock the correction request;
2. lock `SemesterSubjectGrade`;
3. require request `PENDING`, grade `LOCKED`, and matching expected version;
4. revalidate proposed fields and score/source constraints;
5. apply the approved change;
6. increment `version_no` while retaining `workflow_status = LOCKED`;
7. record reviewer, reviewed time, applied time, and audit evidence.

Rejection sets `REJECTED`, requires a rejection reason, records reviewer/time, and leaves the grade unchanged. There is no direct edit path for a LOCKED grade.

## Feature and permission decision

Register:

| Field | Ratified value |
|---|---|
| code | `academic.grades` |
| name | `Nilai Semester` |
| module | `Academic` |
| required_permission | `NULL` |
| default_enabled | `true` |
| system_enabled | `true` |
| is_toggleable | `true` |

D1 does not add a new permission code for grade entry. The feature resolver is only one layer. Effective write authorization must still require enabled feature, active account, assignment/role authority, exact resource/class/subject scope, and workflow state. An enabled feature override cannot bypass those controls.

## Action contract

| Action | Assigned subject teacher | Wali Kelas | Waka Akademik | Super Admin |
|---|---|---|---|---|
| `GRADE_VIEW` | relevant assigned scope | own class | management scope | no new authority |
| `GRADE_ENTER_DRAFT` | relevant assigned scope | only if independently assigned teacher | only if separately implemented by scope | no new authority |
| `GRADE_CHECK` | no | complete DRAFT in own class | no shortcut | no |
| `GRADE_APPROVE_LOCK` | no | no | yes | no |
| `GRADE_CORRECTION_REQUEST` | assigned locked grade | own-class locked grade | management scope if separately needed | no new authority |
| `GRADE_CORRECTION_REVIEW` | no | no | yes | no |

## Grain, missing semantics, and lifecycle

- Canonical grain: `Student_ID × Semester_ID × Subject_ID`, enforced by the existing unique constraint on `semester_subject_grades`.
- Student names are display values, never identity or authorization keys.
- `MISSING != ZERO`; `score = 0` is valid and missing remains `NULL`.
- Lifecycle: `DRAFT → CHECKED → LOCKED`.
- Normal save is allowed only for a new grade or an existing `DRAFT`.
- `CHECKED` and `LOCKED` normal saves fail closed; no silent return to DRAFT.
- Post-lock change uses correction request/application and retains locked final-state semantics.

## Source verification disposition

| Ratified item | Source disposition | D1 action |
|---|---|---|
| Canonical grade model/grain | exists in `SemesterSubjectGrade` and migration unique key | preserve |
| DRAFT save, score/source/provenance validation, audit | exists in `SemesterGradeEntryService` | reuse, add state guard in G2 |
| Completeness | exists in `SemesterGradeCompletenessService` | reuse |
| Wali check | exists in `SemesterGradeFinalizationService::check()` | reuse |
| Waka lock | exists in `SemesterGradeFinalizationService::approveAndLock()` | reuse |
| Correction request | exists in `SemesterGradeCorrectionRequestService` and `CorrectionRequestService` | reuse for request-only path |
| Correction approve/apply | not present | implement dedicated service in G5 |
| `academic.grades` feature | absent from `UserAccessFeatureSeeder` | add in G1; no permission code |
| Subject-teacher scope authorization | not a complete grade web boundary | implement in G1/G2 |
| Normal-save DRAFT-only guard | absent; checked/locked updates are currently possible | `IMPLEMENTATION_CRITICAL` in G2 |
| AI grade authority | absent and forbidden by policy | keep AI out of workflow |

`AcademicAuthorizationService` currently provides broad academic authority and Wali session checks, but it is not a substitute for exact subject-teacher grade scope. The grade implementation must add/use a dedicated scope boundary rather than route through broad `academic.domain.manage` authority.

## Implementation slices

| Slice | Required result | Recommended model |
|---|---|---|
| G1 | authorization contract, `academic.grades` registry, read surface/worklist | GPT-5.6 Luna — Medium |
| G2 | DRAFT batch entry and canonical save orchestration; explicit DRAFT-only hardening | GPT-5.6 Sol — Medium |
| G3 | completeness display and Wali CHECKED workflow | GPT-5.6 Sol — Medium |
| G4 | Waka approve/LOCK workflow | GPT-5.6 Sol — Medium |
| G5 | correction request plus Waka approve/reject/apply service | GPT-5.6 Sol — High |
| G6 | end-to-end regression and completion review | GPT-5.6 Luna — Medium |

No route, controller, Blade view, service, seeder, migration, or test was changed by D1. No PILOT/staging/production data was accessed or written.

## Closeout

- Starting HEAD: `970f8f801c412dc6092b8c65d152d0f6dc1f634d`
- Final HEAD: unchanged source basis; governance edits are local and pending commit/push after validation
- Input owner: assigned subject teacher via exact TeachingAssignment scope
- Process owner: `WAKA_AKADEMIK`
- Checker: `WALI_KELAS`
- Final approval/lock: `WAKA_AKADEMIK`
- Correction requester: assigned subject teacher or effective Wali
- Correction reviewer/application: Waka via dedicated atomic service
- Feature: `academic.grades`
- Required permission: `NULL`
- Lifecycle: `DRAFT → CHECKED → LOCKED`
- Entry-state hardening: `IMPLEMENTATION_CRITICAL / REQUIRED IN G2`
- Selected decision: `GRADE_WORKFLOW_READY_FOR_IMPLEMENTATION`
- Application source changed: `NO`
- PILOT database write: `NONE`
- Blockers: no unresolved governance blocker; implementation-critical source hardening remains
- Commit/push: pending closeout validation and repository authorization
- SAFE_TO_CLOSE: `YES` after validation
