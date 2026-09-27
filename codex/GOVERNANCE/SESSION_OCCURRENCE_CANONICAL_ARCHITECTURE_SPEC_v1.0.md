# Session Occurrence Canonical Architecture Specification v1.0

Project: SISTEM IMTAQ  
Task: SOC-CAS  
Date: 2026-09-19  
Mode: Architecture / impact / rollout design only  
Status: SPECIFICATION ONLY — NOT AUTHORIZED FOR IMPLEMENTATION

## 1. Task Scope

This specification defines the smallest additive architecture for an independent institutional session-occurrence fact on top of the existing Academic pilot. It does not modify application source, tests, migrations, schema, database, RBAC, UI, or governance artifacts. It does not reopen the decided `SPLIT_WORKFLOW_AND_OCCURRENCE_STATE` direction.

The design preserves existing attendance, teacher attendance, substitution, cancellation, rescheduling, joint-session, correction/versioning, dashboard metrics, and export workflows.

## 2. Authority / Governance Verification

Verified before analysis:

- Policy v1.1 SHA-256: `ecd1f08fe7d6772ee4f10f7a9e35db4a7c6cb66b2ba57dc5aff5622033caddea`.
- CIBS baseline v1.0 SHA-256: `37a9f4b4866fd70b4b1de4a3fec29503dc9368198a5437171cdb80f95b9f4d85`.
- Current lifecycle: `PILOT_IMPLEMENTED_HARDENING`; `GREENFIELD = NO`.
- Attendance consumer chain remains canonical: `CanonicalAttendanceSemanticService → AttendanceSemanticMetricsService → AcademicRoleDashboardService → AcademicDashboardExportService`.

SOC-MD-06 is decided, but source mutation remains unauthorized. `MD_01_DISPENSASI`, `MD_02_NON_ELIGIBLE_AUTHORITY`, source-authority enforcement, Waka scope canonicalization, historical EXCUSED reconciliation, class-lineage governance, and follow-up domain remain open.

## 3. Existing Session Model

Evidence inspected: `ClassSession`, class-session migrations, `ScheduleChange`, `ClassSessionGroup`, `SessionStudentParticipant`, `SessionTeacherParticipation`, `StudentAttendance`, occurrence-related services/controllers/tests.

### Current `class_sessions` fields

| Field | Current evidence / meaning |
|---|---|
| `id` | UUID primary key. |
| `session_code` | Unique string. |
| `teaching_assignment_id` | Required FK. |
| `schedule_rule_id` | Nullable FK. |
| `class_id` | Required anchor class FK. |
| `subject_id` | Required FK. |
| `location_id` | Nullable FK. |
| `planned_start_at`, `planned_end_at` | Required planned interval; model rejects non-positive duration. |
| `actual_start_at`, `actual_end_at` | Nullable timestamps already present, not currently required for completion. |
| `session_source` | Controlled source: `SCHEDULED`, `EXTRA`, `RESCHEDULED`, `AD_HOC`. |
| `participant_scope` | `FULL_CLASS` or `SELECTED_STUDENTS`. |
| `session_status` | `PLANNED`, `CONFIRMED`, `COMPLETED`, `CANCELLED`, `RESCHEDULED`; current mixed workflow/finalization/status field. |
| `rescheduled_from_session_id` | Nullable self-lineage FK. |
| `notes` | Nullable text. |
| `version_no` | Unsigned integer, default 1; currently used for optimistic changes/finalization. |
| timestamps | `created_at`, `updated_at`. |

Current constraints include unique `session_code`, schedule-rule/start unique index, PostgreSQL active interval exclusion for `PLANNED/CONFIRMED/COMPLETED`, and controlled vocabulary checks. There is no independent occurrence field/entity.

### Related grains

- `ClassSessionGroup`: many class-scope rows per one `ClassSession`, unique per session/class; this is sufficient to identify a shared joint institutional event.
- `SessionStudentParticipant`: one expected participant per session/student, unique; `is_required`, eligibility fields, attendance relation.
- `SessionTeacherParticipation`: one session/teacher row, role `PRIMARY` or `SUBSTITUTE`, obligation, nullable attendance, reason, notes, schedule-change lineage.
- `StudentAttendance`: one attendance row per student participant; status/workflow/version/actor timestamps.
- `ScheduleChange`: source/related session lineage, change type, reason, actor/timestamps/status; used for substitution, cancellation, and rescheduling.

### Direct status write locations observed

`StudentAttendanceFinalizer` changes `PLANNED/CONFIRMED → COMPLETED`; `CancellationService`, `ScheduleRuleArchiveService`, and `ScheduleRuleRevisionService` change active sessions to `CANCELLED`; `RescheduleService` changes source to `RESCHEDULED` and creates a replacement `PLANNED` session. Controllers and metrics read `session_status` directly in several places. This confirms that a destructive rename or in-place reinterpretation is unsafe.

## 4. Existing Workflow Status Semantics

Accepted SOC-RDG meaning is preserved:

- `COMPLETED` is an attendance-finalization terminal workflow state.
- `COMPLETED` does not prove actual occurrence.
- `COMPLETED` does not prove officially HELD.
- `COMPLETED` is not equivalent to Core HELD.
- `CANCELLED` is excluded from current attendance opportunity semantics.
- `RESCHEDULED` is a source-session lineage state; replacement is separately created.

The current canonical semantic service filters/aggregates `COMPLETED` for live attendance metrics and keeps historical `PLANNED` as work queue. That is transitional behavior, not evidence that `COMPLETED = HELD`.

## 5. Target Canonical Occurrence Contract

Canonical occurrence vocabulary is exactly `SCHEDULED`, `HELD`, `CANCELLED`, `RESCHEDULED`. It answers: “What institutionally happened to this scheduled session?” It does not answer attendance completeness/outcome, teacher lateness, finalization, lock, or publication.

`SESSION_WORKFLOW_STATE ≠ SESSION_OCCURRENCE_STATE ≠ ATTENDANCE_COMPLETENESS ≠ ATTENDANCE_OUTCOME ≠ PUBLICATION_STATE`.

Only official `HELD` sessions for eligible required participants may eventually form attendance opportunities. Partial and conflict conditions remain observable workflow/exception data, not new occurrence statuses.

## 6. Recommended Occurrence Storage Architecture

### Options

| Option | Assessment |
|---|---|
| A — add fields to `class_sessions` | Lowest table count, but keeps occurrence beside the mixed workflow status, makes correction history harder, and increases contradictory-state risk. Not recommended. |
| B — separate `session_occurrences` only | Clean grain and audit boundary, but a current/effective read must join and correction history needs an additional durable pattern. Viable but incomplete alone. |
| C — hybrid additive | One `session_occurrences` effective row per `ClassSession` plus immutable/versioned occurrence versions/events. Keeps session identity, isolates occurrence semantics, supports correction/history, joint grain, and rollback. Recommended. |
| D — other replacement | No evidence justifies a replacement or destructive redesign. Rejected. |

### Recommendation

`RECOMMENDED_OCCURRENCE_STORAGE_ARCHITECTURE = OPTION_C_HYBRID_ADDITIVE_EFFECTIVE_OCCURRENCE_PLUS_IMMUTABLE_VERSIONS`.

Proposed future shape:

1. `session_occurrences`: one effective occurrence record per `class_session_id` with unique FK, canonical status, workflow/review metadata, current version, and current source/evidence reference.
2. `session_occurrence_versions` (or equivalent append-only occurrence event table): one immutable row per initial record/correction, with old/new status, reason, actor, time, evidence/reference, version and correlation IDs.
3. Optional nullable `current_occurrence_version_id` on `session_occurrences`, not a destructive change to `class_sessions`.

This keeps one occurrence at institutional `ClassSession` grain, avoids duplicate truth for joint classes, and leaves `class_sessions.session_status` as legacy workflow compatibility until a later, evidenced cutover.

Trade-offs: two additive tables and joins; explicit transaction boundaries; a compatibility adapter is needed. Benefits are durable audit, versioned correction, independent publication/lock handling, safe rollback, and no historical status rewrite. Performance is addressed by unique/indexed session FK and period/status indexes. Laravel impact is one model/repository/service boundary and narrow adapters, not a rewrite of existing services.

## 7. Minimum Data Contract

| Field | Purpose / nullability / control |
|---|---|
| `id` | UUID occurrence identity; non-null. |
| `class_session_id` | Unique required FK to one institutional session. |
| `occurrence_status` | Required controlled value: `SCHEDULED`, `HELD`, `CANCELLED`, `RESCHEDULED`. |
| `recorded_by` | Nullable only before certification; required once an effective decision is recorded. FK to user. |
| `recorded_at` | Required with a recorded decision. |
| `note` | Nullable for ordinary decisions; required or recommended for partial/conflict/cancellation according to command rule, not a new status. |
| `evidence_type` / `evidence_reference` | Nullable for routine digital record; required when an evidence/conflict command relies on external/offline evidence. Controlled type; reference must not contain secrets. |
| `review_status` | Separate workflow value such as `NONE`, `PENDING_REVIEW`, `RESOLVED`; never an occurrence status. |
| `version_no` | Required optimistic/version counter, unique per session/version history. |
| `source_reference` / `correlation_id` | Nullable lineage/authority boundary; records where the fact came from without inventing LIVE/LEGACY precedence. |
| `corrected_by`, `corrected_at` | Nullable until correction; required on correction version. |
| `created_at`, `updated_at` | Technical timestamps; not a substitute for audit history. |

Exact teacher arrival/start/end and late minutes are not required. Existing `actual_start_at`/`actual_end_at` remain nullable compatibility fields; they must not be made mandatory for HELD. Historical rows do not require automatic backfill. Immutable version rows preserve old/new values and audit regardless of current pointer.

## 8. HELD Certification Workflow

Future command flow: offline teacher evidence → Wali authorization and own-session scope check → Wali records occurrence decision and optional note/evidence → append version and update effective occurrence atomically → audit event. Routine HELD is operationally valid without second Waka approval. Waka reviews exceptions/corrections.

The command must check effective Wali assignment for the session date, shared-session scope, resource state, reason/evidence rules, expected version, and audit actor. It must not require student attendance completeness: `HELD + ATTENDANCE_INCOMPLETE` is valid. Attendance remains a separate transaction and semantic chain.

## 9. Partial Session Architecture

Represent partial occurrence as `occurrence_status = HELD` plus a structured exception/note/reason (prefer the existing session `notes`/audit/alert path, or a typed occurrence exception attached to the version). No `PARTIALLY_HELD` status, no minute threshold, and no mandatory actual clock times. The partial condition must be queryable for DQ/review without changing denominator status.

## 10. Teacher Lateness Integration

Current `SessionTeacherParticipation` already has nullable `attendance_status`, `reason`, `notes`, and optional check-in/out. `TeacherAttendanceService` uses `PRESENT`, `ABSENT`, `SICK`, `IZIN`, `OTHER` and audits changes. Preserve `PRESENT + reason/note TERLAMBAT`; do not add occurrence fields or minute tracking. `TEACHER_LATENESS ≠ SESSION_OCCURRENCE`.

## 11. Substitute Teacher Integration

`SUBSTITUTION_IMPLEMENTATION_DELTA = authorization-scope delta only`.

Preserve `SubstitutionService`, `SessionTeacherParticipation`, `ScheduleChange`, reason, lineage, conflict checks, and audit. Later, add an own-authorized-class/session scope check before invoking the existing command for Wali; retain broader Waka authority. The service may need a scoped authorization collaborator or command precondition, but no replacement workflow.

Required later changes: policy/controller authorization and UI visibility for Wali own scope; service-level defense-in-depth; tests for own-class allow, other-class deny, Waka allow, permanent schedule denial, and joint conflict. A single shared ClassSession must have one substitution assignment fact. Existing `ClassSessionGroup` supplies the scope set, but conflict enforcement must evaluate the complete group before writing, so two Walis cannot assign different substitutes.

## 12. Cancellation Integration

`CANCELLED_OCCURRENCE_INVARIANT = an effective CANCELLED occurrence creates zero student attendance opportunities; no effective HELD occurrence may coexist for that ClassSession`.

Future cancellation command must update occurrence and preserve current `CancellationService` attendance-protection and `ScheduleChange` reason/lineage atomically. Wali own-class, Waka cross-class/bulk, and joint shared-session authority follow policy v1.1. If attendance exists, ordinary cancellation remains blocked and routes to conflict-correction workflow; no deletion.

## 13. Reschedule Integration

`RESCHEDULE_OCCURRENCE_INVARIANT = source ClassSession receives RESCHEDULED once its replacement lineage is applied; replacement is a separate ClassSession with a separate occurrence initially SCHEDULED; identities are never merged`.

Extend `RescheduleService` transactionally to create/update the two occurrence facts and retain `rescheduled_from_session_id` and `ScheduleChange`. The original does not remain SCHEDULED/HELD for denominator purposes; the replacement is not implicitly HELD.

## 14. Conflict / Pending Review Workflow

`CONFLICT_WORKFLOW_STORAGE = reuse CorrectionRequest + AuditLog + existing Data Quality/alert ownership; add occurrence correction type/payload in a future additive extension if required`.

Do not create `DISPUTED` or `CONFLICTED` occurrence status. A pending request has workflow status `PENDING_REVIEW`; Wali reports/submits evidence, Waka resolves. Existing correction request versioning and audit are preferred over a new parallel subsystem. Unresolved requests block publication/effective finalization but do not silently select HELD/CANCELLED.

## 15. Occurrence Correction Architecture

`OCCURRENCE_CORRECTION_MODEL = append immutable occurrence version/event, update effective pointer in one transaction; use CorrectionRequest for locked/published review and AuditLog for all changes`.

Every correction records old status, new status, reason, actor, timestamp, evidence/reference, expected/current version, resulting version, correlation ID, and publication/denominator impact. HELD→SCHEDULED is not an ordinary transition; any correction uses this controlled version workflow. Waka is final authority under policy; second approver is not required for validated Academic correction, while publication/lock gates still control effective output.

## 16. Attendance History Preservation

`CANCELLED_AFTER_ATTENDANCE_DATA_STRATEGY = preserve attendance rows as historical versions; mark/supersede their current reporting effectiveness through an auditable occurrence-correction projection; recalculate denominator from corrected occurrence, never DELETE`.

Existing attendance correction/versioning is reused. If a cancelled occurrence has attendance, current reporting must exclude its opportunities while audit/history retains the original attendance and correction linkage. Published artifacts are not silently rewritten; a controlled publication correction/reissue process is required.

## 17. Canonical Denominator Rebase

`TARGET_ATTENDANCE_OPPORTUNITY_SESSION_FILTER = OFFICIALLY_HELD`.

Minimum future change surface is upstream population selection, not formula rewrite:

- `CanonicalAttendanceSemanticService::forClassPeriod()` currently selects `COMPLETED` via `sessionState()`.
- `CanonicalAttendanceSemanticService::sessionState()` needs an occurrence-aware adapter/filter once effective occurrence exists.
- `AttendanceSemanticMetricsService`, `AcademicRoleDashboardService`, and `AcademicDashboardExportService` remain downstream consumers and should not be locally rewritten.
- `SessionSemanticMetricsService` and operational queue/controllers require separate compatibility review because they expose workflow status.

The target population is official HELD × required/relevant participant × ELIGIBLE. Migration must be feature-flagged/dual-read before cutover.

## 18. Missing / Completeness Interaction

Existing canonical invariant already supports the target without formula rewrite:

`ELIGIBLE = RESOLVED + MISSING + RECONCILIATION_REQUIRED`.

After HELD drives the session population, an unresolved student opportunity remains `MISSING`, never `ABSENT`. `HELD` does not require complete attendance. Additional changes are upstream session selection and tests, not downstream formula redesign.

## 19. RBAC Target Matrix

| Action | Current authority | Target authority | Delta |
|---|---|---|---|
| Record routine HELD | Effective Wali finalizes attendance; no occurrence fact | Wali own authorized session | New occurrence command/scope. |
| Correct validated HELD | Wali/open or Waka post-lock paths exist for attendance | Waka occurrence correction | New occurrence correction boundary. |
| Report conflict | Existing UI/DQ/correction pathways | Wali and Waka | Add typed occurrence conflict command. |
| Resolve conflict | No canonical occurrence resolver | Waka | New reviewed command. |
| Assign substitute own class | Current service requires full authority | Wali own class/session or Waka | Policy/controller/service scope delta. |
| Assign substitute cross-class | Full Academic authority | Waka | Preserve; deny Wali. |
| Cancel own-class | Existing cancellation service/scope | Wali own class or Waka | Reconcile authorization at command boundary. |
| Cancel cross-class | Waka/full authority | Waka | Preserve. |
| Cancel joint session | Existing shared-scope safeguards | Waka/shared authority | Enforce one institutional decision. |
| Reschedule | Existing service/authority | Existing authorized workflow, joint-safe | Preserve; occurrence projection extension. |
| Super Admin routine business action | Technical access exists | Not routine owner | Do not expand routine business authority. |

## 20. Joint-Session Architecture

`ONE CLASS SESSION → ONE OCCURRENCE TRUTH`. `ClassSessionGroup` supplies the participating class scope; 2B/3B attribution remains partitioned through `SessionStudentParticipant` and effective enrollment. A unique occurrence per `class_session_id` plus transaction-level group locking prevents 2B HELD / 3B CANCELLED contradictions. Required future tests cover conflicting Wali substitute/cancellation attempts and one occurrence read across both class scopes.

## 21. Source Authority Interface

`OCCURRENCE_SOURCE_AUTHORITY_INTERFACE = evidence provider + source_reference + recorded_by + certified/effective version + correlation ID boundary`.

The interface records where evidence came from and who recorded/certified it, but does not invent LIVE/LEGACY precedence. Broader `SOURCE_AUTHORITY_ENFORCEMENT = PARTIAL` remains open.

## 22. Historical Compatibility Strategy

`LEGACY_OCCURRENCE_BACKFILL_STRATEGY = no automatic backfill; leave canonical occurrence absent/unverified for legacy rows, preserve legacy workflow/snapshot semantics, and reconcile only with evidence-supported, separately approved batches`.

`HISTORICAL_COMPLETED_AUTO_HELD = NO`. No `UNKNOWN` canonical status is invented; absence of a certification/effective occurrence record means not yet reconciled. New transactions can use the additive architecture without forcing historical reconciliation.

## 23. Publication / Lock Integration

Routine occurrence recording is separate from attendance validation, period lock, and publication. A locked/published occurrence correction uses a versioned controlled workflow, Waka authority, reason/evidence, audit, and explicit publication impact. Published output cannot silently change; it requires a controlled correction/reissue path. Existing `AttendancePeriodLockService`, `PostLockAttendanceCorrectionService`, `CorrectionRequest`, version fields, and audit are integration points.

## 24. Audit Trail Architecture

`OCCURRENCE_AUDIT_ARCHITECTURE = immutable occurrence version/event rows + existing AuditLog + ScheduleChange for schedule lineage + CorrectionRequest for reviewed corrections`.

Minimum audit fields: created/recorded by, recorded at, old occurrence, new occurrence, reason, evidence/reference, correction actor/time, version before/after, source/correlation reference, and affected ClassSession. Do not rely only on `updated_at`; avoid parallel logs when the existing audit infrastructure can carry the event.

## 25. Database Constraint Design

Specification only:

- **Database enforced:** unique `session_occurrences.class_session_id`; allowed occurrence value check; FKs to ClassSession/users; unique `(session_occurrence_id, version_no)`; non-null version/recorded fields for effective decisions; immutable version identity; indexed status/period queries.
- **Application enforced:** Wali date/scope authorization; required reason/evidence for partial/conflict/correction/cancellation; no conflicting joint assignment; expected-version checks; no ordinary cancellation with attendance; publication/lock workflow.
- **Management policy enforced:** teacher offline evidence; Waka conflict/correction authority; no routine Super Admin ownership; unresolved conflict not publishable.

Do not encode every policy as a database CHECK. No migration is written in SOC-CAS.

## 26. API / Command Surface

Prefer explicit actions, not generic `update session_status`:

- `RecordSessionOccurrence` — Wali own scope, Waka oversight.
- `RecordSessionCancellation` — authorized own/cross/shared scope.
- `ApplySessionReschedule` — existing RescheduleService extended atomically.
- `AssignSessionSubstitute` — existing substitution command with scoped authorization.
- `ReportSessionOccurrenceConflict` — Wali/Waka evidence submission.
- `ResolveSessionOccurrenceConflict` — Waka only.
- `CorrectSessionOccurrence` — Waka, versioned and audited.
- `ReadSessionOccurrence` — scoped effective projection.

Existing controllers/actions can be extended with explicit endpoints/commands; no generic status endpoint should be introduced.

## 27. UI / UX Delta

Minimal future delta only:

- Wali: record KBM/HELD, view occurrence, add simple/partial note, record conflict, assign substitute within own session scope.
- Waka: cross-class occurrence overview, pending conflict queue, resolution, correction, substitution/cancellation oversight.
- Teacher: no website occurrence control; printed/offline evidence remains.
- No minute-level teacher time input and no dashboard redesign.

## 28. Data Quality / Alert Design

Future actionable alerts: HELD + attendance incomplete; past scheduled session without occurrence decision; conflict pending review; HELD with unresolved teacher participation; CANCELLED with effective attendance; rescheduled source/replacement inconsistency; occurrence correction awaiting denominator/publication recalculation. Each alert needs owner, severity, status, due/follow-up. Do not implement alerts here.

## 29. Policy-to-Code Traceability Matrix

| Policy | Current implementation | Target | Gap / components | Migration? | RBAC? | Test / risk |
|---|---|---|---|---|---|---|
| SOC-MD-01 | Wali attendance authority, teacher offline model | Wali occurrence recorder | Add occurrence command; ClassSession/authorization | Additive | Yes | Scope/one-truth tests; medium |
| SOC-MD-02 | No teacher direct attendance/HELD | Preserve | No source redesign | No | No | Negative access tests; low |
| SOC-MD-03 | Completeness separate in canonical metrics; workflow uses COMPLETED | HELD may coexist with incomplete | Upstream occurrence population | Additive | No | Missing≠ABSENT; high |
| SOC-MD-04 | No Waka second approval for routine attendance | Operational HELD without second approval | New occurrence command | Additive | No | state/readiness; medium |
| SOC-MD-05 | Audited attendance correction/version exists | Waka occurrence correction | Occurrence version/correction adapter | Additive | Yes | history/publication; high |
| SOC-MD-06A | Notes/actual timestamps nullable | HELD + partial exception | Typed occurrence exception/note | Additive | No | partial/no threshold; medium |
| SOC-MD-06B lateness | PRESENT + reason/notes supported | Preserve, no occurrence schema | UI/copy only if needed | No | No | late does not cancel; low |
| SOC-MD-06B substitute | Substitution service requires full authority | Wali own scope + Waka | authorization delta, joint lock | No/additive | Yes | conflicting Wali; high |
| SOC-MD-06C | Cancellation protects attendance and uses ScheduleChange | canonical CANCELLED projection | atomic occurrence sync | Additive | Yes | zero opportunity; high |
| SOC-MD-06D | Audit/correction infrastructure exists | Waka conflict workflow | typed CorrectionRequest path | Additive | Yes | no auto outcome; high |

## 30. Preserve / Change Matrix

| Component | Preserve as-is | Extend / adapt | Replace | Reason |
|---|---|---|---|---|
| ClassSession | identity, schedule, lineage, legacy status | occurrence relation/adapter | No | existing source of session identity |
| `session_status` | legacy workflow semantics | compatibility mapping | No | mixed existing consumers |
| CanonicalAttendanceSemanticService | formulas/invariant | upstream HELD population adapter | No | canonical consumer |
| AttendanceSemanticMetricsService | output contract | consume occurrence-aware population | No | canonical consumer |
| AcademicRoleDashboardService | composition/UI data | no local formula | No | downstream canonical consumer |
| AcademicDashboardExportService | export contract | no local formula | No | downstream canonical consumer |
| StudentAttendanceFinalizer | attendance workflow | occurrence hook later | No | working finalizer |
| TeacherAttendanceService | vocabulary/audit | no lateness schema | No | policy compatible |
| SubstitutionService | lineage/conflict | scoped authorization/joint lock | No | preserve domain mechanics |
| CancellationService | reason/protection | atomic occurrence projection | No | preserve safety |
| RescheduleService | source/replacement lineage | occurrence pair update | No | preserve identity |
| ScheduleChange | reason/lineage | occurrence correlation | No | existing transaction lineage |
| SessionTeacherParticipation | roles/attendance | no occurrence duplication | No | teacher fact separate |
| SessionStudentParticipant | participant/eligibility | no grain change | No | attendance opportunity input |
| ClassSessionGroup | joint scope | group-lock invariant | No | one occurrence grain |
| Period lock | lock semantics | occurrence correction hook | No | preserve lock |
| Correction services | audit/version | occurrence correction type | No | reuse infrastructure |
| AuditLog | audit events | occurrence event payload | No | avoid duplicate log |
| Controllers/policies | existing routes | explicit occurrence commands | No | narrow authorization |
| UI | existing screens | small occurrence/conflict controls | No | no redesign |
| Tests | existing suite | SOC-CAS acceptance matrix | No | additive regression |

## 31. Target Domain Model

```text
Academic ClassSession (1)
 ├─ legacy Workflow State (1; session_status, transitional)
 ├─ Canonical SessionOccurrence (0..1 effective; unique per ClassSession)
 │   └─ OccurrenceVersion/Event (1..n immutable)
 ├─ TeacherParticipation (1..n; PRIMARY/SUBSTITUTE)
 ├─ StudentParticipation (0..n; required/eligibility)
 │   └─ AttendanceOutcome (0..1; separate workflow/version)
 ├─ ClassSessionGroup (1..n class scopes for joint session)
 ├─ ScheduleChange (0..n; cancellation/reschedule/substitution lineage)
 ├─ ConflictReview/CorrectionRequest (0..n; workflow only)
 └─ Publication / PeriodLock (separate reporting state)
```

One `ClassSession` is the institutional event. Class attribution is a projection of group/enrollment, not a second occurrence identity.

## 32. Target State Machine

Normal commands:

```text
SCHEDULED --record occurrence after KBM--> HELD
SCHEDULED --no lesson / approved cancellation--> CANCELLED
SCHEDULED --move lesson--> RESCHEDULED (source)
RESCHEDULED source --lineage--> replacement SCHEDULED
```

Partial session is `HELD + exception`; teacher lateness is teacher attendance note; conflict is `PENDING_REVIEW` workflow. `HELD → SCHEDULED` is not ordinary. Any correction uses versioned correction, not a normal transition. Correction can resolve a previously effective decision to HELD/CANCELLED/RESCHEDULED only with Waka authority, reason, evidence, audit, and expected version.

## 33. Workflow-to-Occurrence Compatibility Matrix

| Current workflow status | Canonical occurrence | Validity / notes |
|---|---|---|
| `PLANNED` | `SCHEDULED` | Valid for new generated sessions; legacy rows require review/absence of certification. |
| `CONFIRMED` | `SCHEDULED` | Valid; confirmation is workflow, not occurrence. |
| `COMPLETED` | `HELD` | Not automatically valid; only after evidence-supported occurrence record. |
| `COMPLETED` | `CANCELLED` | Only controlled occurrence correction if later proven no lesson. |
| `RESCHEDULED` | `RESCHEDULED` | Source lineage; replacement has separate `SCHEDULED`. |
| `CANCELLED` | `CANCELLED` | Valid invariant; zero opportunities. |
| any | `PENDING_REVIEW` | Invalid as occurrence; review workflow only. |

## 34. Future Test Architecture

No tests were changed or run. Future acceptance cases: `SOC-CAS-01` new session SCHEDULED; `02` Wali HELD own scope; `03` other class deny; `04` HELD incomplete attendance; `05` missing not ABSENT; `06` partial HELD note; `07` late teacher HELD; `08` substitute HELD; `09` Wali own substitution; `10` joint conflicting substitution blocked; `11` no lesson CANCELLED; `12` cancelled zero opportunities; `13` reschedule lineage; `14` unresolved conflict cannot publish; `15` Waka resolves HELD; `16` Waka resolves CANCELLED; `17` attendance history preserved; `18` denominator recalculates; `19` joint one occurrence; `20` class-partitioned attendance; `21` legacy COMPLETED not auto-HELD; `22` dashboard/export chain unchanged; `23` post-lock versioned correction; `24` cancelled/rescheduled no double count.

Additional critical cases: idempotent occurrence command, stale version rejection, duplicate joint Wali assignment, published correction/reissue, evidence-reference audit, and source-authority boundary.

## 35. Migration / Rollout Sequence

1. `SOC-I0` recovery checkpoint, source manifest, real runtime/database identity verification, schema/migration inventory; stop if evidence incomplete.
2. `SOC-I1` additive occurrence tables/indexes and models only; leave unused; rollback by disabling feature, not down-migration.
3. `SOC-I2` read-only dual-read/compatibility adapter with divergence reports; no denominator cutover.
4. `SOC-I3` new-session occurrence write commands for SCHEDULED/HELD/CANCELLED/RESCHEDULED with Wali/Waka scope; feature-flagged.
5. `SOC-I4` substitution scoped authorization and joint-session conflict enforcement.
6. `SOC-I5` occurrence-aware DQ/conflict/correction/lock/publication integration.
7. `SOC-I6` denominator read migration to officially HELD after evidence and regression gate; downstream formulas unchanged.
8. `SOC-I7` separately approved evidence-supported historical reconciliation; never bulk COMPLETED→HELD.
9. `SOC-I8` retire transitional reads only after production evidence and rollback window.

Each phase is atomic, checkpointed, and separately authorized. No phase is authorized by SOC-CAS.

## 36. Rollback Strategy

Additive tables can remain unused. Before denominator cutover, disable occurrence writes/read adapter and revert reads to the current transitional path without deleting occurrence records. New occurrence records remain preserved for later re-enable. After HELD is used by published reports, rollback becomes non-trivial: freeze publication, preserve both projections, issue a controlled reporting decision, and do not silently restore COMPLETED semantics. Destructive down-migration is never the only rollback. The effective rollback boundary is `SOC-I6` publication/denominator cutover.

## 37. Pre-Implementation Evidence Requirements

Before source/database work: application source manifest; canonical Git/release identity; database identity and PostgreSQL runtime verification; encrypted database backup; schema dump; migration state; backup restore test; row counts by current session status; sessions with attendance; joint session/group counts; rescheduled lineage counts; cancelled-with-attendance anomaly count; locked/published period counts; source/test baseline; current policy/baseline hashes; authorization matrix; feature flag and rollback owner; monitoring/alert plan; acceptance test plan; pilot/UAT sign-off; publication impact assessment.

## 38. Atomic Implementation Phase Plan

| Phase | Objective / scope | DB | Tests | Rollback checkpoint / stop |
|---|---|---|---|---|
| SOC-I0 | Evidence/recovery/runtime gate | None | Read-only checks | Stop if PostgreSQL identity/backup absent. |
| SOC-I1 | Add occurrence storage/models | Additive only | model/constraint tests | Leave unused; stop on schema mismatch. |
| SOC-I2 | Compatibility read adapter | None | dual-read/divergence tests | Disable adapter; no denominator change. |
| SOC-I3 | New occurrence commands | No further schema unless gated | scope/state/audit tests | Disable feature flag; preserve records. |
| SOC-I4 | Wali substitute scope/joint lock | Maybe additive authorization metadata | RBAC/joint tests | Revert Wali visibility; Waka path remains. |
| SOC-I5 | conflict/correction/lock/publication | Additive correction fields if needed | correction/publication/DQ tests | Freeze correction path; no destructive rollback. |
| SOC-I6 | HELD denominator cutover | None expected | full Academic/export regression | Pre-publication rollback; post-publication requires controlled decision. |
| SOC-I7 | evidence-supported legacy reconciliation | controlled batch only | reconciliation/accounting tests | Batch-level rollback/versioned correction. |
| SOC-I8 | retire transitional dependency | later cleanup migration | consumer/source audit | Only after production evidence. |

## 39. Architectural Decisions / ADR Summary

- `SOC-CAS-ADR-01`: Option C hybrid effective occurrence plus immutable versions; chosen for auditability and additive rollback.
- `SOC-CAS-ADR-02`: preserve `session_status` as transitional workflow; no COMPLETED→HELD rename.
- `SOC-CAS-ADR-03`: denominator cutover upstream to officially HELD; preserve downstream canonical formulas.
- `SOC-CAS-ADR-04`: reuse CorrectionRequest/AuditLog/DQ for conflict workflow; no new parallel subsystem by default.
- `SOC-CAS-ADR-05`: occurrence corrections append versions and update effective pointer; no silent overwrite/delete.
- `SOC-CAS-ADR-06`: substitution implementation preserved; add Wali own-session scope and joint conflict enforcement.
- `SOC-CAS-ADR-07`: joint occurrence remains one ClassSession grain; participant attribution remains partitioned.

## 40. Remaining Blockers

`SESSION_OCCURRENCE_CANONICALIZATION = NOT_COMPLETED`; `SOURCE_AUTHORITY_ENFORCEMENT = PARTIAL`; `WAKA_SCOPE_CANONICALIZATION = NOT_COMPLETED`; `REAL_POSTGRES_RUNTIME = NOT_VERIFIED`; `HISTORICAL_EXCUSED_RECONCILIATION = NOT_COMPLETED`; `CLASS_LINEAGE_GOVERNANCE = NOT_COMPLETED`; `FOLLOWUP_DOMAIN = NOT_IMPLEMENTED`; `MD_01_DISPENSASI = OPEN`; `MD_02_NON_ELIGIBLE_AUTHORITY = OPEN`; `FULL_ATTENDANCE_CANONICALIZATION = NO`.

## 41. Implementation Gate

`POSTGRES_VERIFICATION_REQUIRED_BEFORE_MIGRATION = YES`. This specification does not authorize source mutation, database migration, historical rewrite, or implementation. The first implementation phase remains blocked pending owner authorization and SOC-I0 evidence.

## 42. Final Status

SOC-CAS architecture specification is complete for audit. It is additive, preserves the pilot, separates occurrence from workflow/completeness/publication, avoids new canonical statuses, and provides staged implementation/rollback boundaries. Stop after this artifact; do not begin SOC-I1.

## Machine-readable state

```text
SOC_CAS_VERSION = v1.0
SOC_CAS_COMPLETED = YES
POLICY_V1_1_HASH_VALID = YES
CIBS_BASELINE_HASH_VALID = YES
APPLICATION_SOURCE_CHANGED = NO
APPLICATION_TEST_SOURCE_CHANGED = NO
DATABASE_ACCESSED = NO
DATABASE_WRITE = NONE
MIGRATION_EXECUTED = NO
POSTGRESQL_ACCESSED = NO
TESTS_RERUN = NO
CURRENT_SESSION_STATUS_PRESERVED = YES
COMPLETED_AUTO_MAPPED_TO_HELD = NO
RECOMMENDED_OCCURRENCE_STORAGE_ARCHITECTURE = OPTION_C_HYBRID_ADDITIVE_EFFECTIVE_OCCURRENCE_PLUS_IMMUTABLE_VERSIONS
NEW_OCCURRENCE_FACT_REQUIRED = YES
CANONICAL_OCCURRENCE_VOCABULARY = SCHEDULED, HELD, CANCELLED, RESCHEDULED
PARTIAL_SESSION_NEW_CANONICAL_STATUS_REQUIRED = NO
CONFLICT_NEW_CANONICAL_STATUS_REQUIRED = NO
HELD_REQUIRES_COMPLETE_STUDENT_ATTENDANCE = NO
TEACHER_LATENESS_REQUIRES_OCCURRENCE_SCHEMA_CHANGE = NO
SUBSTITUTION_REBUILD_REQUIRED = NO
SUBSTITUTION_RBAC_CHANGE_REQUIRED = YES
CANCELLATION_REBUILD_REQUIRED = NO
RESCHEDULING_REBUILD_REQUIRED = NO
JOINT_SESSION_REBUILD_REQUIRED = NO
OCCURRENCE_CORRECTION_MODEL = APPEND_IMMUTABLE_VERSION_AND_UPDATE_EFFECTIVE_POINTER
ATTENDANCE_HISTORY_DESTRUCTIVE_DELETE_REQUIRED = NO
TARGET_ATTENDANCE_OPPORTUNITY_SESSION_FILTER = OFFICIALLY_HELD
DOWNSTREAM_ATTENDANCE_FORMULA_REWRITE_REQUIRED = NO
LEGACY_COMPLETED_AUTO_HELD = NO
LEGACY_OCCURRENCE_BACKFILL_STRATEGY = NO_AUTOMATIC_BACKFILL_EVIDENCE_SUPPORTED_REVIEW_ONLY
POSTGRES_VERIFICATION_REQUIRED_BEFORE_MIGRATION = YES
SOURCE_AUTHORITY_ENFORCEMENT = PARTIAL
MD_01_DISPENSASI = OPEN
MD_02_NON_ELIGIBLE_AUTHORITY = OPEN
SESSION_OCCURRENCE_CANONICALIZATION = NOT_COMPLETED
FULL_ATTENDANCE_CANONICALIZATION = NO
SESSION_OCCURRENCE_IMPLEMENTATION_AUTHORIZED = NO
SOURCE_MUTATION_AUTHORIZED = NO
DATABASE_MIGRATION_AUTHORIZED = NO
AI_IMPLEMENTATION_STARTED = NO
OPENAI_API_CALLED = NO
RECOMMENDED_FIRST_IMPLEMENTATION_PHASE = SOC-I0 / BLOCKED_PENDING_EXPLICIT_AUTHORIZATION
NEXT_ATOMIC_TASK = RETURN_TO_CHATGPT_FOR_SOC_CAS_ARCHITECTURE_AUDIT
STOP = YES
```
