# Canonical Implementation Baseline v1.0

Baseline date: 2026-09-18  
Project: SISTEM IMTAQ Academic Pilot  
Task: CIBS-RB — Canonical Implementation Baseline Synchronization / Rebase

## 1. Purpose

This document is the current implementation pointer for Academic hardening. It prevents a new session from treating the substantially implemented Academic pilot as a greenfield Sprint 0 project. It is an evidence-based baseline, not a production approval and not authority to implement the unfinished session-occurrence rebase.

## 2. Authority Order

1. IMTAQ Core Engine and approved amendments.
2. Explicit management decisions.
3. Accepted canonical decision gates and frozen governance artifacts.
4. Current application source, migrations, and tests as implementation evidence.
5. Recovery/checkpoint and Change Manifest evidence.
6. Task queue and verified progress evidence.
7. Current-status pointers.
8. Legacy or stale status documents.

Historical snapshots do not override newer accepted decisions. `POLICY_PENDING` is not guessed and `SUPERSEDED` is not implemented.

## 3. Baseline Date / Evidence

Evidence checked for this baseline:

- `application/web/app/`, `routes/`, `database/migrations/`, `database/seeders/`, `resources/`, and `tests/`.
- `codex/TASK_QUEUE.md`: 67 Academic implementation rows; 65 `DONE`, 1 `BLOCKED_POLICY`, 1 `NOT_STARTED`.
- `recovery/phase-2r-b2c/PHASE-2R-B2C_20260918-105740/POST_B2C_APPLICATION_SOURCE_MANIFEST.sha256`: current source continuity, 0 mismatches.
- Accepted Phase 2R-A/B1/B1.1/B2A/B2B/B2C and CR-B2B-D evidence.
- `codex/GOVERNANCE/SESSION_OCCURRENCE_MANAGEMENT_POLICY_REGISTER_v1.0.md` and its SOC-MDG manifest.
- Database and PostgreSQL were not accessed for this documentation synchronization.

## 4. Academic Lifecycle State

Academic is `PILOT_IMPLEMENTED_HARDENING`: implementation has started, the Academic MVP and controlled pilot evidence exist, and current work is canonicalization/governance hardening. This is not production-complete. Production cutover is not complete and remains gated by release authority, staging, backup/restore, monitoring, rollback, and runtime verification.

## 5. Current Role Structure

Management-approved operational hierarchy:

`SUPER_ADMIN → WAKA_AKADEMIK → WALI_KELAS`

Teacher website access is `NO`. Teacher attendance evidence is supplied through a manual printed form; there is no teacher direct system input or direct HELD certification. No new operational role is invented by this baseline.

## 6. Implemented Capability Inventory

Classification is based on current source/test paths and the task queue, not documentation claims alone.

| Capability | Classification | Evidence / boundary |
|---|---|---|
| Authentication / local login | IMPLEMENTED_PRESERVE | Auth controller, login views, and local authentication tests. |
| Super Admin / RBAC / audit | IMPLEMENTED_PRESERVE | Shared Core/RBAC services, admin controllers, authorization and audit tests. |
| Waka Akademik and Wali Kelas scope | IMPLEMENTED_TRANSITIONAL | Authorization/scope resolvers and role dashboard tests; canonical Waka scope remains an open blocker. |
| Student master and identifiers | IMPLEMENTED_PRESERVE | Shared Core student models/services/tests and Sprint 1 queue rows. |
| Grade/class/subject structure | IMPLEMENTED_PRESERVE | Academic masters, migrations, admin views/tests. |
| Effective-dated enrollment and homeroom assignment | IMPLEMENTED_PRESERVE | Enrollment/homeroom models, resolvers, services, and tests. |
| Staff/teacher master | IMPLEMENTED_PRESERVE | Shared Core staff model and Academic teacher assignment paths/tests. |
| Subjects and teaching assignment | IMPLEMENTED_PRESERVE | Subject/TeachingAssignment models and tests. |
| Scheduling and conflict engine | IMPLEMENTED_PRESERVE | Schedule rule services, conflict checker, migrations, and tests. |
| ClassSession generation | IMPLEMENTED_PRESERVE | ClassSessionGenerator and participant snapshot services/tests. |
| Extra/ad-hoc sessions | IMPLEMENTED_PRESERVE | ExtraSessionCreator and tests. |
| Rescheduling | IMPLEMENTED_TRANSITIONAL | RescheduleService preserves source/replacement lineage; session status still mixes concepts. |
| Cancellation | IMPLEMENTED_PRESERVE | CancellationService protects attendance facts, requires reason, and excludes cancelled sessions from attendance opportunity semantics. |
| Teacher participation and substitution | IMPLEMENTED_PRESERVE | SessionTeacherParticipation, SubstitutionService, participation tests and lineage fields. |
| Joint class/session | IMPLEMENTED_TRANSITIONAL | ClassSessionGroup and joint roster/metrics tests exist; session occurrence authority is not canonicalized. |
| Student attendance draft/finalization | IMPLEMENTED_PRESERVE | Draft/finalizer services, workflow statuses and Feature tests. |
| Teacher attendance | IMPLEMENTED_TRANSITIONAL | Wali records `PRESENT`, `ABSENT`, `SICK`, `IZIN`, or `OTHER`; `reason`/`notes` and audited correction exist. |
| Open-period correction | IMPLEMENTED_PRESERVE | StudentAttendanceCorrectionService with reason, version, and audit. |
| Period lock / post-lock correction | IMPLEMENTED_PRESERVE | AttendancePeriodLockService and post-lock request/review/apply tests. |
| Dashboard / semantic metrics | IMPLEMENTED_TRANSITIONAL | Canonical consumer chain is migrated through dashboard; session occurrence remains transitional. |
| Dashboard export | IMPLEMENTED_TRANSITIONAL | AcademicDashboardExportService is a canonical consumer; full attendance canonicalization is not complete. |
| Data quality / alerts | IMPLEMENTED_TRANSITIONAL | Deterministic Academic DQ infrastructure exists; early-warning thresholds remain policy blocked. |
| July historical migration/report infrastructure | IMPLEMENTED_PRESERVE | Import, validation, reconciliation, report and migration evidence exists; historical source grain is not rewritten. |
| Semester grades | IMPLEMENTED_PRESERVE | Grade entry, completeness, correction and finalization services/tests. |
| Report Card / Transcript | IMPLEMENTED_TRANSITIONAL | Draft/version/publication/history paths exist; final authority and publication gates remain policy-sensitive. |
| UAT / pilot infrastructure | IMPLEMENTED_PRESERVE | UAT evidence, pilot evidence, recovery manifests and hardening task rows exist. |
| AI/OpenAI implementation | NOT_IMPLEMENTED | Explicitly deferred and blocked; no provider call or implementation started. |

## 7. Preserve / Do-Not-Rebuild Matrix

For every verified existing area below:

`PRESERVE_EXISTING_IMPLEMENTATION = YES`  
`REBUILD_FROM_ZERO = NO`

This applies to attendance workflow, teacher attendance, substitution, cancellation, rescheduling, joint session, attendance correction, dashboard metrics, and dashboard export. Refinement or canonical rebase must be additive and evidence-led. Transitional classifications do not authorize a greenfield replacement.

## 8. Canonical Attendance Consumer Chain

`CanonicalAttendanceSemanticService → AttendanceSemanticMetricsService → AcademicRoleDashboardService → AcademicDashboardExportService`

Current classification:

- `ATTENDANCE_SEMANTIC_LAYER = CANONICAL`
- `ATTENDANCE_METRICS_SERVICE = CANONICAL_CONSUMER`
- `ACADEMIC_DASHBOARD = CANONICAL_METRICS_CONSUMER`
- `ACADEMIC_EXPORT = CANONICAL_CONSUMER`
- `KNOWN_ATTENDANCE_METRICS_NON_CANONICAL_CONSUMERS = 0`
- `FULL_ATTENDANCE_CANONICALIZATION = NO`

Do not reopen Phase 2R-B1/B2A/B2B/B2C.

## 9. Transitional Session Occurrence State

Accepted SOC-RDG result: `CLOSED / ACCEPTED`. The repository vocabulary is `PLANNED`, `CONFIRMED`, `COMPLETED`, `CANCELLED`, and `RESCHEDULED`. One status column combines workflow, occurrence, and attendance-finalization concepts. Observed `COMPLETED` is an attendance-finalization terminal state; it does not prove actual occurrence or Core `HELD`. `CANCELLED` is proven excluded from the attendance denominator. The approved direction is `SPLIT_WORKFLOW_AND_OCCURRENCE_STATE`, but canonicalization is not completed and implementation is not authorized. Do not rename `COMPLETED` to `HELD` or implement occurrence state here.

## 10. Session Occurrence Management Policy State

SOC-MDG policy register v1.0 is canonical and unchanged. `SOC_MD_01` through `SOC_MD_05` are `DECIDED`; `SOC_MD_06` is `OPEN`; `SOC_MDG_COMPLETE = NO`. Policy register SHA-256 is `25491de6238d36cd25757cb69ffe41857ba9b09b9a460dea02b38fc8fbef3b65`. Its Change Manifest SHA-256 is `5d96bc994207d0ae15672a46b9b58b61978b0a461937eb4648e054eae37ebd2b`.

## 11. Unfrozen Management Decision Continuation

The following are continuity evidence only and must not be inserted into policy register v1.0:

- `SOC_MD_06A_PARTIAL_SESSION = DECIDED_PENDING_POLICY_REGISTER_UPDATE`: a partial session may remain HELD if KBM occurred; full duration is not required; no minimum-minute threshold or exact clock times are defined; the partial condition remains observable and is not automatically CANCELLED.
- `SOC_MD_06B_TEACHER_LATENESS = DECIDED_PENDING_POLICY_REGISTER_UPDATE`: lateness does not automatically cancel; if KBM occurs the session may remain HELD; operational teacher record may be `HADIR + catatan TERLAMBAT`; exact arrival or minute count is not required.
- `SOC_MD_06B_SUBSTITUTE_TEACHER = OPEN`.
- `SOC_MD_06C_NO_LESSON = OPEN`.
- `SOC_MD_06D_CONFLICTING_EVIDENCE = OPEN`.

## 12. Joint-Class Baseline

Joint sessions remain one institutional event. `ClassSessionGroup`, `SessionStudentParticipant`, effective-dated enrollment, and the joint roster/metrics services partition class-scoped consumers without duplicating the event. This is transitional because occurrence state and broader class-lineage governance remain open. Do not rebuild the joint-session workflow.

## 13. Teacher Attendance / Substitution Baseline

Teacher participation preserves `PRIMARY` and `SUBSTITUTE` roles, obligation type, attendance status, reason, notes, schedule-change lineage, and audited updates. `TeacherAttendanceService` accepts `PRESENT`, `ABSENT`, `SICK`, `IZIN`, and `OTHER`, with nullable status before entry and reason support for correction. The current model can represent present-but-late operationally as `PRESENT + reason/note`; no lateness schema is authorized. Substitution remains `IMPLEMENTED_PRESERVE`; SOC-MD-06 substitute policy is still open.

## 14. Correction / Versioning Baseline

Open-period correction, Waka/Wali scope checks, locked-period correction requests, approval/apply flow, optimistic version checks, reason requirements, old/new value audit, and correction lineage exist. Existing Wali open-period correction must not be silently deleted; if later policy differs, reconcile it explicitly. No correction behavior changes in this baseline.

## 15. Migration / Historical Data Baseline

Migration/import infrastructure, dry-run/reconciliation, class/student mapping, and controlled historical evidence exist. Legacy monthly/daily sources remain historical lineage/sample inputs. No migration, import, seed, historical rewrite, or database access was performed by CIBS-RB. `BUNDLE_SHA256SUMS.txt` is `SNAPSHOT_ONLY_NOT_CURRENT_REPOSITORY_AUTHORITY`; accepted recovery manifests are the continuity evidence.

## 16. Database Runtime Evidence Boundary

`REAL_POSTGRES_RUNTIME = NOT_VERIFIED`. The bundled SQLite role is `LOCAL_SCAFFOLD_OR_FALLBACK_NOT_PROVEN_AS_PILOT_SOT`. Pilot operational database contents are not verified by this task. CIBS-RB did not open/query SQLite or PostgreSQL.

## 17. Deployment / Cutover State

Controlled local pilot/UAT evidence exists. Production cutover is not complete. Canonical Git/release authority, staging target, backup/restore sign-off for the current release, monitoring, rollback, and pending migration review remain deployment gates. Do not equate pilot with production.

## 18. Remaining Canonical Blockers

- Session occurrence canonicalization: `NOT_COMPLETED`.
- Source authority enforcement: `PARTIAL`.
- Waka scope canonicalization: `NOT_COMPLETED`.
- Real PostgreSQL runtime: `NOT_VERIFIED`.
- Historical excused reconciliation: `NOT_COMPLETED`.
- Class lineage governance: `NOT_COMPLETED`.
- Follow-up domain: `NOT_IMPLEMENTED`.
- `MD_01_DISPENSASI = OPEN`; `MD_02_NON_ELIGIBLE_AUTHORITY = OPEN`; `SOC_MD_06 = OPEN`.
- Full attendance canonicalization: `NO`.

## 19. AI Gate

`AI_IMPLEMENTATION_STARTED = NO`, `OPENAI_API_CALLED = NO`, `TYPED_AI_TOOLS = NOT_STARTED`, `OPENAI_API_INTEGRATION = BLOCKED`, `LLM_ORCHESTRATION = BLOCKED`, and `AI_UI = BLOCKED`. Academic implementation maturity does not activate AI.

## 20. Current Next Gate

The current canonical/governance gate is `SOC_MD_06` — management decision gate; source mutation is `NOT_AUTHORIZED`. Deployment/cutover remains a separate track and must not become the automatic next implementation task while the owner focus is session-occurrence governance.

## 21. Documents Superseded as Current-State Pointers

The following are no longer authoritative current-state pointers after this sync, but are preserved as historical/supporting evidence:

| Document | Classification |
|---|---|
| `00_CODEX_HANDOFF_START_HERE.md` | CURRENT_CANONICAL_POINTER after sync |
| `README.md` | CURRENT_CANONICAL_POINTER after sync |
| `START_HERE.md` | CURRENT_CANONICAL_POINTER after sync |
| `PROJECT_STATUS.md` | CURRENT_SUPPORTING_STATUS after sync |
| `PROJECT_PROGRESS.md` | CURRENT_SUPPORTING_STATUS; percentages retained as legacy tracking unless verified |
| `PROJECT_MANIFEST.json` | CURRENT_CANONICAL_POINTER after sync |
| `NEXT_ACTION.md` | CURRENT_CANONICAL_POINTER after sync |
| `codex/CURRENT_TASK_CONTEXT.md` | CURRENT_CANONICAL_POINTER after sync |
| `codex/TASK_QUEUE.md` | CURRENT_SUPPORTING_STATUS; completed rows immutable |
| `codex/WORK_LOG.md` | CURRENT_SUPPORTING STATUS / append-only history |
| `modules/academic/STATUS.md` | CURRENT_SUPPORTING_STATUS after sync |
| `BUNDLE_SHA256SUMS.txt` | SNAPSHOT_ONLY_NOT_CURRENT_REPOSITORY_AUTHORITY |
| `archive/` | HISTORICAL_IMMUTABLE |
| `recovery/` | HISTORICAL_IMMUTABLE / recovery evidence |
| existing `codex/CHANGE_MANIFESTS/` | HISTORICAL_IMMUTABLE |
| SOC policy register v1.0 | CURRENT_CANONICAL governance evidence; immutable in this task |
| bundle-local `README_AUDIT_BUNDLE.md` | SNAPSHOT_ONLY |

## 22. Baseline Machine State

```text
BASELINE_VERSION = v1.0
BASELINE_DATE = 2026-09-18
ACADEMIC_LIFECYCLE_STATE = PILOT_IMPLEMENTED_HARDENING
GREENFIELD = NO
IMPLEMENTATION_STARTED = YES
CONTROLLED_PILOT_COMPLETED = YES
PRODUCTION_CUTOVER_COMPLETE = NO
ACADEMIC_TASK_COUNT = 67
ACADEMIC_TASK_DONE_COUNT = 65
ACADEMIC_TASK_BLOCKED_POLICY_COUNT = 1
ACADEMIC_TASK_NOT_STARTED_COUNT = 1
ACADEMIC_TASK_OTHER_COUNT = 0
POST_B2C_APPLICATION_SOURCE_CONTINUITY = PASS
APPLICATION_SOURCE_HASH_MISMATCH_COUNT = 0
ATTENDANCE_SEMANTIC_LAYER = CANONICAL
ATTENDANCE_METRICS_SERVICE = CANONICAL_CONSUMER
ACADEMIC_DASHBOARD = CANONICAL_METRICS_CONSUMER
ACADEMIC_EXPORT = CANONICAL_CONSUMER
KNOWN_ATTENDANCE_METRICS_NON_CANONICAL_CONSUMERS = 0
SOC_RDG = CLOSED_ACCEPTED
SOC_MDG_POLICY_REGISTER_VERSION = v1.0
SOC_MD_01 = DECIDED
SOC_MD_02 = DECIDED
SOC_MD_03 = DECIDED
SOC_MD_04 = DECIDED
SOC_MD_05 = DECIDED
SOC_MD_06 = OPEN
SOC_MDG_COMPLETE = NO
SOC_MD_06A_PARTIAL_SESSION = DECIDED_PENDING_POLICY_REGISTER_UPDATE
SOC_MD_06B_TEACHER_LATENESS = DECIDED_PENDING_POLICY_REGISTER_UPDATE
SOC_MD_06B_SUBSTITUTE_TEACHER = OPEN
SOC_MD_06C_NO_LESSON = OPEN
SOC_MD_06D_CONFLICTING_EVIDENCE = OPEN
SESSION_OCCURRENCE_REBASE_DIRECTION = SPLIT_WORKFLOW_AND_OCCURRENCE_STATE
SESSION_OCCURRENCE_CANONICALIZATION = NOT_COMPLETED
SESSION_OCCURRENCE_IMPLEMENTATION_AUTHORIZED = NO
SOURCE_AUTHORITY_ENFORCEMENT = PARTIAL
WAKA_SCOPE_CANONICALIZATION = NOT_COMPLETED
REAL_POSTGRES_RUNTIME = NOT_VERIFIED
HISTORICAL_EXCUSED_RECONCILIATION = NOT_COMPLETED
CLASS_LINEAGE_GOVERNANCE = NOT_COMPLETED
FOLLOWUP_DOMAIN = NOT_IMPLEMENTED
MD_01_DISPENSASI = OPEN
MD_02_NON_ELIGIBLE_AUTHORITY = OPEN
FULL_ATTENDANCE_CANONICALIZATION = NO
BUNDLED_SQLITE_ROLE = LOCAL_SCAFFOLD_OR_FALLBACK_NOT_PROVEN_AS_PILOT_SOT
BUNDLE_SHA256SUMS_STATUS = SNAPSHOT_ONLY_NOT_CURRENT_REPOSITORY_AUTHORITY
REBUILD_ATTENDANCE_WORKFLOW = NO
REBUILD_TEACHER_ATTENDANCE = NO
REBUILD_SUBSTITUTION = NO
REBUILD_CANCELLATION = NO
REBUILD_RESCHEDULING = NO
REBUILD_JOINT_SESSION = NO
REBUILD_ATTENDANCE_CORRECTION = NO
REBUILD_DASHBOARD_METRICS = NO
REBUILD_DASHBOARD_EXPORT = NO
AI_IMPLEMENTATION_STARTED = NO
OPENAI_API_CALLED = NO
NEXT_MANAGEMENT_GATE = SOC_MD_06
NEXT_SOURCE_MUTATION = NOT_AUTHORIZED
FULL_SYSTEM_IMPLEMENTATION_COMPLETE = NO
```
